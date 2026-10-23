<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelPurchaseRequestRequest;
use App\Http\Requests\ReviewPurchaseRequestRequest;
use App\Http\Requests\StorePurchaseRequestRequest;
use App\Http\Requests\UpdatePurchaseRequestRequest;
use App\Models\Department;
use App\Models\Item;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Unit;
use App\Services\PurchaseRequestWorkflow;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function __construct(
        protected PurchaseRequestWorkflow $workflow,
    ) {
    }

    /**
     * Daftar RPO (filter status, tipe, prioritas, pencarian).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $search = $request->input('q');
        $status = $request->input('status');
        $requestType = $request->input('request_type');
        $priority = $request->input('priority');

        $purchaseRequests = PurchaseRequest::with(['requestedBy', 'department', 'project'])
            ->withCount('items')
            ->when($search, fn ($query, $search) => $query->search($search))
            ->when(in_array($status, PurchaseRequest::statuses(), true),
                fn ($query) => $query->status($status))
            ->when(in_array($requestType, PurchaseRequest::requestTypes(), true),
                fn ($query) => $query->requestType($requestType))
            ->when(in_array($priority, PurchaseRequest::priorities(), true),
                fn ($query) => $query->where('priority', $priority))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('rpo.index', [
            'purchaseRequests' => $purchaseRequests,
            'search' => $search,
            'status' => $status,
            'requestType' => $requestType,
            'priority' => $priority,
            'statuses' => PurchaseRequest::statuses(),
            'requestTypes' => PurchaseRequest::requestTypes(),
            'priorities' => PurchaseRequest::priorities(),
        ]);
    }

    /**
     * Form pembuatan RPO baru.
     */
    public function create(): View
    {
        $this->authorize('create', PurchaseRequest::class);

        return view('rpo.create', $this->formOptions());
    }

    /**
     * Simpan RPO baru (beserta item) dalam satu transaksi.
     */
    public function store(StorePurchaseRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $purchaseRequest = DB::transaction(function () use ($validated, $request) {
            $purchaseRequest = PurchaseRequest::create([
                'requested_by' => $request->user()->id,
                'request_type' => $validated['request_type'],
                'reason' => $validated['reason'],
                'priority' => $validated['priority'],
                'projected_date' => $validated['projected_date'] ?? null,
                'department_id' => $validated['department_id'] ?? $request->user()->department_id,
                'project_id' => $validated['project_id'] ?? null,
                'status' => PurchaseRequest::STATUS_DRAFT,
                'comment' => $validated['comment'] ?? null,
            ]);

            $this->syncItems($purchaseRequest, $validated['items']);

            return $purchaseRequest;
        });

        AuditLogger::log('create', PurchaseRequest::class, $purchaseRequest->id, [
            'new' => [
                'display_number' => $purchaseRequest->display_number,
                'status' => $purchaseRequest->status,
                'item_count' => count($validated['items']),
            ],
        ], [
            'reference_type' => PurchaseRequest::class,
            'reference_id' => $purchaseRequest->id,
        ]);

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', "RPO {$purchaseRequest->display_number} berhasil dibuat.");
    }

    /**
     * Detail RPO beserta item dan riwayat status.
     */
    public function show(PurchaseRequest $purchaseRequest): View
    {
        $this->authorize('view', $purchaseRequest);

        $purchaseRequest->load(['items.item', 'items.unit', 'requestedBy', 'reviewedBy', 'approvedBy', 'department', 'project']);

        return view('rpo.show', [
            'purchaseRequest' => $purchaseRequest,
            'statusHistories' => $purchaseRequest->statusHistories()->with('user')->get(),
            'itemStatuses' => PurchaseRequestItem::statuses(),
        ]);
    }

    /**
     * Form edit RPO (hanya draft / perlu revisi).
     */
    public function edit(PurchaseRequest $purchaseRequest): View
    {
        $this->authorize('update', $purchaseRequest);

        $purchaseRequest->load('items');

        return view('rpo.edit', array_merge($this->formOptions(), [
            'purchaseRequest' => $purchaseRequest,
        ]));
    }

    /**
     * Perbarui RPO (header + item) dalam satu transaksi.
     */
    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $validated = $request->validated();

        $oldData = [
            'request_type' => $purchaseRequest->request_type,
            'reason' => $purchaseRequest->reason,
            'priority' => $purchaseRequest->priority,
            'projected_date' => $purchaseRequest->projected_date?->toDateString(),
            'project_id' => $purchaseRequest->project_id,
            'item_count' => $purchaseRequest->items()->count(),
        ];

        DB::transaction(function () use ($purchaseRequest, $validated) {
            $purchaseRequest->update([
                'request_type' => $validated['request_type'],
                'reason' => $validated['reason'],
                'priority' => $validated['priority'],
                'projected_date' => $validated['projected_date'] ?? null,
                'department_id' => $validated['department_id'] ?? $purchaseRequest->department_id,
                'project_id' => $validated['project_id'] ?? null,
                'comment' => $validated['comment'] ?? $purchaseRequest->comment,
            ]);

            $this->syncItems($purchaseRequest, $validated['items']);
        });

        AuditLogger::log('update', PurchaseRequest::class, $purchaseRequest->id, [
            'old' => $oldData,
            'new' => [
                'request_type' => $validated['request_type'],
                'reason' => $validated['reason'],
                'priority' => $validated['priority'],
                'projected_date' => $validated['projected_date'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'item_count' => count($validated['items']),
            ],
        ], [
            'reference_type' => PurchaseRequest::class,
            'reference_id' => $purchaseRequest->id,
        ]);

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', "RPO {$purchaseRequest->display_number} berhasil diperbarui.");
    }

    /**
     * Submit RPO (draft / perlu revisi → diajukan).
     */
    public function submit(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->authorize('submit', $purchaseRequest);

        if (! $purchaseRequest->isEditable()) {
            return back()->with('error', 'RPO tidak dapat diajukan pada status saat ini.');
        }

        if ($purchaseRequest->items()->count() === 0) {
            return back()->with('error', 'RPO harus memiliki minimal satu item sebelum diajukan.');
        }

        $this->workflow->submit($purchaseRequest, $request->user(), $request->input('comment'));

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', "RPO {$purchaseRequest->display_number} berhasil diajukan untuk verifikasi.");
    }

    /**
     * Aksi verifikasi: approve / reject / request revision / start review / comment.
     */
    public function review(ReviewPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $action = $request->input('action');
        $comment = $request->input('comment');
        $actor = $request->user();

        // Aksi komentar tidak mengubah status dan boleh oleh pemohon.
        if ($action === 'comment') {
            $this->authorize('comment', $purchaseRequest);

            if (empty($comment)) {
                return back()->withErrors(['comment' => 'Komentar tidak boleh kosong.'])->withInput();
            }

            $this->workflow->addComment($purchaseRequest, $actor, $comment);

            return redirect()->route('rpo.show', $purchaseRequest)
                ->with('success', 'Komentar berhasil disimpan.');
        }

        $this->authorize('verify', $purchaseRequest);

        if ($action === 'approve') {
            $this->workflow->approve(
                $purchaseRequest,
                $actor,
                $comment,
                $request->input('item_statuses', []),
                $request->input('item_reasons', [])
            );

            return redirect()->route('rpo.show', $purchaseRequest)
                ->with('success', "RPO {$purchaseRequest->display_number} berhasil disetujui.");
        }

        if ($action === 'reject') {
            $this->workflow->reject($purchaseRequest, $actor, $request->input('rejection_reason'), $comment);

            return redirect()->route('rpo.show', $purchaseRequest)
                ->with('success', "RPO {$purchaseRequest->display_number} berhasil ditolak.");
        }

        if ($action === 'request_revision') {
            $this->workflow->requestRevision($purchaseRequest, $actor, $request->input('rejection_reason'), $comment);

            return redirect()->route('rpo.show', $purchaseRequest)
                ->with('success', "RPO {$purchaseRequest->display_number} dikembalikan untuk revisi.");
        }

        return back()->with('error', 'Aksi verifikasi tidak dikenal.');
    }

    /**
     * Mulai review (submitted → under_review).
     */
    public function startReview(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->authorize('verify', $purchaseRequest);

        $this->workflow->startReview($purchaseRequest, $request->user(), $request->input('comment'));

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', "RPO {$purchaseRequest->display_number} sedang direview.");
    }

    /**
     * Batalkan RPO (alasan wajib).
     */
    public function cancel(CancelPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->workflow->cancel(
            $purchaseRequest,
            $request->user(),
            $request->validated('reason'),
            $request->input('comment')
        );

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', "RPO {$purchaseRequest->display_number} berhasil dibatalkan.");
    }

    /**
     *  (create/update/delete) berdasarkan input form.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function syncItems(PurchaseRequest $purchaseRequest, array $items): void
    {
        $keepIds = [];

        foreach ($items as $row) {
            $data = [
                'purchase_request_id' => $purchaseRequest->id,
                'item_id' => $row['item_id'],
                'quantity' => $row['quantity'],
                'unit_id' => $row['unit_id'],
                'unit_price' => $row['unit_price'] ?? null,
                'minimum_term' => $row['minimum_term'] ?? null,
                'specification' => $row['specification'] ?? null,
            ];

            if (! empty($row['id'])) {
                $item = $purchaseRequest->items()->whereKey($row['id'])->first();

                if (! $item) {
                    continue;
                }

                $item->update($data);
                $keepIds[] = $item->id;

                continue;
            }

            $created = PurchaseRequestItem::create($data);
            $keepIds[] = $created->id;
        }

        // Hapus item yang tidak lagi ada di form (hanya saat RPO masih bisa diedit).
        $purchaseRequest->items()
            ->when($keepIds !== [], fn ($query) => $query->whereNotIn('id', $keepIds))
            ->get()
            ->each(fn (PurchaseRequestItem $item) => $item->delete());
    }

    /**
     * Opsi data master untuk form RPO.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'items' => Item::with('defaultUnit')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
            'requestTypes' => PurchaseRequest::requestTypes(),
            'priorities' => PurchaseRequest::priorities(),
        ];
    }
}

/**
     * Start sourcing (publish RPO to sourcing phase).
     */
    public function sourcing(PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('rpo.sourcing'), 403);

        $purchaseRequest->status = PurchaseRequest::STATUS_SOURCING;
        $purchaseRequest->save();

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', 'RPO berhasil dipublikasikan ke tahap sourcing.');
    }

    /**
     * Store sourcing data (quotations).
     */
    public function sourcingStore(StoreSourcingRequest $request, PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('rpo.sourcing'), 403);

        $validated = $request->validated();

        foreach ($validated['quotations'] as $quotation) {
            $purchaseRequest->quotations()->create([
                'supplier_id' => $quotation['supplier_id'],
                'price' => $quotation['price'],
                'lead_time' => $quotation['lead_time'],
                'status' => 'pending_review',
            ]);
        }

        return redirect()->route('rpo.quotations', $purchaseRequest)
            ->with('success', 'Kuotaion berhasil disimpan.');
    }

    /**
     * Display quotations form.
     */
    public function quotations(PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('rpo.quotations'), 403);

        return view('rpo.quotations', compact('purchaseRequest'));
    }

    /**
     * Store quotations.
     */
    public function quotationsStore(StoreQuotationRequest $request, PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('rpo.quotations'), 403);

        $validated = $request->validated();

        foreach ($validated['quotations'] as $quotation) {
            $purchaseRequest->quotations()->create([
                'supplier_id' => $quotation['supplier_id'],
                'price' => $quotation['price'],
                'lead_time' => $quotation['lead_time'],
                'status' => 'pending_review',
            ]);
        }

        return redirect()->route('rpo.quotations', $purchaseRequest)
            ->with('success', 'Kuotaion berhasil disimpan.');
    }

    /**
     * Approve sourcing (set status ke pending_approval).
     */
    public function approveSourcing(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('director'), 403);

        $purchaseRequest->status = PurchaseRequest::STATUS_PENDING_APPROVAL;
        $purchaseRequest->save();

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', 'Sourcing berhasil diapprove.');
    }

    /**
     * Reject sourcing (kembali ke revision requested).
     */
    public function rejectSourcing(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_if(!auth()->user()->can('director'), 403);

        $purchaseRequest->status = PurchaseRequest::STATUS_REVISION_REQUESTED;
        $purchaseRequest->sourcing_notes = $request->input('reason') ?? 'Sourcing ditolak';
        $purchaseRequest->save();

        return redirect()->route('rpo.show', $purchaseRequest)
            ->with('success', 'Sourcing ditolak, kembali ke revision requested.');
    }
}