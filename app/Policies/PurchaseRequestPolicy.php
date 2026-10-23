/**
 * Melihat daftar/detail RPO.
 * Semua user internal dengan izin rpo.view boleh melihat RPO
 * (pemohon, purchasing, direksi, dsb.).
 */
<?php
<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    /**
     * Melihat daftar/detail RPO.
     *
     * Semua user internal dengan izin rpo.view boleh melihat RPO
     * (pemohon, purchasing, direksi, dsb.).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('rpo.view');
    }

    /**
     * Melihat detail RPO.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermission('rpo.view');
    }

    /**
     * Membuat RPO baru (Gudang / Produksi).
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('rpo.create');
    }

    /**
     * Mengubah RPO.
     *
     * Hanya pemohon yang dapat mengubah RPO miliknya sendiri, dan hanya
     * ketika RPO masih dalam status draft atau revision_requested.
     * Purchasing/admin Edit berizin juga boleh untuk keperluan koreksi.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if (! $user->hasPermission('rpo.edit')) {
            return false;
        }

        if (! $purchaseRequest->isEditable()) {
            return false;
        }

        if ((int) $purchaseRequest->requested_by === (int) $user->id) {
            return true;
        }

        // Purchasing boleh mengoreksi RPO yang perlu revisi.
        return $user->hasPermission('rpo.verify');
    }

    /**
     * Menghapus (soft delete) RPO draft milik sendiri.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if (! $user->hasPermission('rpo.delete')) {
            return false;
        }

        if (! $purchaseRequest->isDraft()) {
            return false;
        }

        return (int) $purchaseRequest->requested_by === (int) $user->id;
    }

    /**
     * Menjalankan aksi verifikasi (approve/reject/request revision).
     *
     * Hanya Purchasing. Pengaju TIDAK boleh memverifikasi pengajuannya sendiri.
     */
    public function verify(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if (! $user->hasPermission('rpo.verify')) {
            return false;
        }

        // Pengaju tidak boleh memverifikasi pengajuannya sendiri.
        if ((int) $purchaseRequest->requested_by === (int) $user->id) {
            return false;
        }

        return in_array($purchaseRequest->status, [
            PurchaseRequest::STATUS_SUBMITTED,
            PurchaseRequest::STATUS_UNDER_REVIEW,
        ], true);
    }

    /**
     * Mengomentari RPO (tanpa mengubah status).
     */
    public function comment(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if (! $user->hasPermission('rpo.view')) {
            return false;
        }

        return ! in_array($purchaseRequest->status, [
            PurchaseRequest::STATUS_CLOSED,
            PurchaseRequest::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Membatalkan RPO.
     *
     * Pemohon boleh membatalkan selagi RPO belum diproses (draft/submitted),
     * Purchasing boleh membatalkan RPO yang ditolak, dan super_admin berizin penuh.
     */
    public function cancel(User $user, PurchaseRequest $purchaseRequest): bool
    {
        $isOwner = (int) $purchaseRequest->requested_by === (int) $user->id;

        if ($isOwner) {
            if (! $user->hasPermission('rpo.create')) {
                return false;
            }

            return in_array($purchaseRequest->status, [
                PurchaseRequest::STATUS_DRAFT,
                PurchaseRequest::STATUS_SUBMITTED,
            ], true);
        }

        if ($user->hasPermission('rpo.verify')) {
            return in_array($purchaseRequest->status, [
                PurchaseRequest::STATUS_SUBMITTED,
                PurchaseRequest::STATUS_UNDER_REVIEW,
                PurchaseRequest::STATUS_REVISION_REQUESTED,
                PurchaseRequest::STATUS_REJECTED,
            ], true);
        }

        return false;
    }

    /**
     * Submit RPO (draft atau revisi → submitted).
     */
    public function submit(User $user, PurchaseRequest $purchaseRequest): bool
    {
        if ((int) $purchaseRequest->requested_by !== (int) $user->id) {
            return false;
        }

        return in_array($purchaseRequest->status, [
            PurchaseRequest::STATUS_DRAFT,
            PurchaseRequest::STATUS_REVISION_REQUESTED,
        ], true);
    }
}

/**
 * Mulai sourcing (menerbitkan RPO ke tahap sourcing).
 */
public function sourcing(User $user, PurchaseRequest $purchaseRequest): bool
{
    // Hanya purchasing dan director yang boleh mulai sourcing.
    return $user->hasPermission('purchasing') || $user->hasPermission('director');
}

/**
 * Approve sourcing (set status ke pending_approval).
 */
public function approveSourcing(User $user, PurchaseRequest $purchaseRequest): bool
{
    // Hanya director yang boleh approve sourcing.
    return $user->hasPermission('director');
}

/**
 * Reject sourcing (kembali ke revision requested).
 */
public function rejectSourcing(User $user, PurchaseRequest $purchaseRequest): bool
{
    // Hanya director yang boleh reject sourcing.
    return $user->hasPermission('director');
}