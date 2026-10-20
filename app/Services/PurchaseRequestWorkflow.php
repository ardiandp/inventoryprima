<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestStatusHistory;
use App\Models\User;
use App\Notifications\PurchaseRequestStatusChanged;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Alur kerja RPO (Purchase Request Order) — draft → submitted → under_review →
 * approved / revision_requested / rejected / cancelled.
 *
 * Semua perubahan status:
 *  - dijalankan dalam database transaction,
 *  - menyimpan aktor, waktu, status asal/tujuan, dan alasan,
 *  - mengirim notifikasi ke pemohon,
 *  - tercatat pada audit log.
 */
class PurchaseRequestWorkflow
{
    /**
     * Submit RPO: draft / revision_requested → submitted.
     */
    public function submit(PurchaseRequest $purchaseRequest, User $actor, ?string $comment = null): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_SUBMITTED,
                'comment' => $comment ?? $purchaseRequest->comment,
                'rejection_reason' => null,
            ]);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_SUBMITTED, $comment);

            AuditLogger::log('submit', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_SUBMITTED],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyPurchasing($purchaseRequest, $from, PurchaseRequest::STATUS_SUBMITTED, $comment);
            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_SUBMITTED, null, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Mulai review: submitted → under_review.
     */
    public function startReview(PurchaseRequest $purchaseRequest, User $actor, ?string $comment = null): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_UNDER_REVIEW,
                'reviewed_by' => $actor->id,
            ]);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_UNDER_REVIEW, $comment);

            AuditLogger::log('review', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_UNDER_REVIEW],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_UNDER_REVIEW, null, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Setujui RPO: submitted/under_review → approved.
     */
    public function approve(
        PurchaseRequest $purchaseRequest,
        User $actor,
        ?string $comment = null,
        array $itemStatuses = [],
        array $itemReasons = [],
    ): PurchaseRequest {
        return DB::transaction(function () use ($purchaseRequest, $actor, $comment, $itemStatuses, $itemReasons) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_APPROVED,
                'reviewed_by' => $actor->id,
                'approved_by' => $actor->id,
                'comment' => $comment ?? $purchaseRequest->comment,
                'rejection_reason' => null,
            ]);

            $this->applyItemDecisions($purchaseRequest, $itemStatuses, $itemReasons);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_APPROVED, $comment);

            AuditLogger::log('approve', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_APPROVED],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_APPROVED, null, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Minta revisi: submitted/under_review → revision_requested.
     *
     * Alasan revisi WAJIB (PRD bagian 4 & acceptance PHASE 4).
     */
    public function requestRevision(PurchaseRequest $purchaseRequest, User $actor, string $reason, ?string $comment = null): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $reason, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_REVISION_REQUESTED,
                'reviewed_by' => $actor->id,
                'revision_reason' => $reason,
                'revision_count' => (int) $purchaseRequest->revision_count + 1,
                'comment' => $comment ?? $purchaseRequest->comment,
            ]);

            // Item yang masih pending ditandai perlu revisi.
            $purchaseRequest->items()
                ->where('status', PurchaseRequestItem::STATUS_PENDING)
                ->update([
                    'status' => PurchaseRequestItem::STATUS_REVISION_REQUESTED,
                    'rejection_reason' => $reason,
                ]);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_REVISION_REQUESTED, $reason, $comment);

            AuditLogger::log('request_revision', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_REVISION_REQUESTED, 'revision_reason' => $reason],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_REVISION_REQUESTED, $reason, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Tolak RPO: submitted/under_review → rejected.
     *
     * Alasan penolakan WAJIB.
     */
    public function reject(PurchaseRequest $purchaseRequest, User $actor, string $reason, ?string $comment = null): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $reason, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_REJECTED,
                'reviewed_by' => $actor->id,
                'rejection_reason' => $reason,
                'comment' => $comment ?? $purchaseRequest->comment,
            ]);

            $purchaseRequest->items()
                ->where('status', PurchaseRequestItem::STATUS_PENDING)
                ->update([
                    'status' => PurchaseRequestItem::STATUS_REJECTED,
                    'rejection_reason' => $reason,
                ]);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_REJECTED, $reason, $comment);

            AuditLogger::log('reject', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_REJECTED, 'rejection_reason' => $reason],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_REJECTED, $reason, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Batalkan RPO. Alasan pembatalan WAJIB.
     */
    public function cancel(PurchaseRequest $purchaseRequest, User $actor, string $reason, ?string $comment = null): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $reason, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update([
                'status' => PurchaseRequest::STATUS_CANCELLED,
                'comment' => $comment ?? $purchaseRequest->comment,
            ]);

            $this->recordHistory($purchaseRequest, $actor, $from, PurchaseRequest::STATUS_CANCELLED, $reason, $comment);

            AuditLogger::log('cancel', PurchaseRequest::class, $purchaseRequest->id, [
                'old' => ['status' => $from],
                'new' => ['status' => PurchaseRequest::STATUS_CANCELLED, 'reason' => $reason],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            $this->notifyRequester($purchaseRequest, $from, PurchaseRequest::STATUS_CANCELLED, $reason, $comment);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Simpan komentar tanpa mengubah status.
     */
    public function addComment(PurchaseRequest $purchaseRequest, User $actor, string $comment): PurchaseRequest
    {
        return DB::transaction(function () use ($purchaseRequest, $actor, $comment) {
            $from = $purchaseRequest->status;

            $purchaseRequest->update(['comment' => $comment]);

            $this->recordHistory($purchaseRequest, $actor, $from, $from, $comment);

            AuditLogger::log('comment', PurchaseRequest::class, $purchaseRequest->id, [
                'new' => ['comment' => $comment],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);

            return $purchaseRequest->refresh();
        });
    }

    /**
     * Terapkan keputusan per-item dari pemverifikasi.
     *
     * @param  array<int|string, string>  $itemStatuses  id item => status
     * @param  array<int|string, string>  $itemReasons  id item => alasan
     */
    protected function applyItemDecisions(PurchaseRequest $purchaseRequest, array $itemStatuses, array $itemReasons): void
    {
        foreach ($itemStatuses as $itemId => $status) {
            if (! $status) {
                continue;
            }

            $item = $purchaseRequest->items()->where('id', $itemId)->first();
            if (! $item) {
                continue;
            }

            $item->update([
                'status' => $status,
                'rejection_reason' => $status === PurchaseRequestItem::STATUS_REJECTED
                    ? ($itemReasons[$itemId] ?? 'Ditolak oleh pemverifikasi.')
                    : null,
                'comment' => $itemReasons[$itemId] ?? $item->comment,
            ]);

            AuditLogger::log('review_item', PurchaseRequestItem::class, $item->id, [
                'old' => ['status' => PurchaseRequestItem::STATUS_PENDING],
                'new' => ['status' => $status],
            ], [
                'reference_type' => PurchaseRequest::class,
                'reference_id' => $purchaseRequest->id,
            ]);
        }
    }

    /**
     * Simpan riwayat status.
     */
    protected function recordHistory(
        PurchaseRequest $purchaseRequest,
        User $actor,
        string $fromStatus,
        string $toStatus,
        ?string $reason = null,
        ?string $comment = null,
    ): PurchaseRequestStatusHistory {
        return PurchaseRequestStatusHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'comment' => $comment,
        ]);
    }

    /**
     * Notifikasi ke pemohon RPO.
     */
    protected function notifyRequester(
        PurchaseRequest $purchaseRequest,
        string $fromStatus,
        string $toStatus,
        ?string $reason = null,
        ?string $comment = null,
    ): void {
        $requester = $purchaseRequest->requestedBy;
        if (! $requester) {
            return;
        }

        $requester->notify(new PurchaseRequestStatusChanged(
            $purchaseRequest,
            $fromStatus,
            $toStatus,
            $reason,
            $comment
        ));
    }

    /**
     * Notifikasi ke user Purchasing yang memiliki izin rpo.verify.
     */
    protected function notifyPurchasing(
        PurchaseRequest $purchaseRequest,
        string $fromStatus,
        string $toStatus,
        ?string $comment = null,
    ): void {
        $users = User::whereHas('roles.permissions', fn ($q) => $q->where('name', 'rpo.verify'))
            ->whereHas('roles', fn ($q) => $q->where('is_active', true))
            ->get();

        foreach ($users as $user) {
            // Jangan menginformasikan pengaju sendiri.
            if ((int) $user->id === (int) $purchaseRequest->requested_by) {
                continue;
            }

            $user->notify(new PurchaseRequestStatusChanged(
                $purchaseRequest,
                $fromStatus,
                $toStatus,
                null,
                $comment
            ));
        }
    }
}
