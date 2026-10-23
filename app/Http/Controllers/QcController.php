<?php

namespace App\Http\Controllers;

use App\Models\QcInspection;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\QcPolicy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class QcController extends Controller
{
    protected $policy;

    public function __construct(QcPolicy $policy)
    {
        $this->policy = $policy;
    }

    public function index()
    {
        $this->authorize('view', QcInspection::class);

        $inspections = QcInspection::with(['purchaseOrder', 'inspectedBy'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->orderBy('inspected_at', 'desc')
            ->paginate(20);

        return view('qc.index', compact('inspections'));
    }

    public function create()
    {
        $this->authorize('create', QcInspection::class);

        $pendingReceptions = PurchaseReceipt::where('status', 'fully_received')
            ->whereNotNull('total_amount')
            ->whereDoesntHave('qcInspection')
            ->get();

        return view('qc.create', compact('pendingReceptions'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', QcInspection::class);

        $validated = $request->validate([
            'receipt_id' => 'required|exists:purchase_receipts,id',
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'display_number' => 'required|string|max:50|unique:qc_inspections',
            'observations' => 'nullable|string',
            'accepted_quantity' => 'required|decimal:0,2',
            'rejected_quantity' => 'required|decimal:0,2',
            'status' => 'required|in:draft,received,pending_inspection,partially_accepted,accepted,rejected,posted',
        ]);

        // Ensure accepted + rejected <= total from receipt
        $receipt = $request->receipt_id;
        $receiptData = $receipt->load('purchaseOrder');

        $totalQty = $receiptData->purchaseOrder
            ->purchaseRequestItems()
            ->sum('quantity') ?? 0;

        if ($validated['accepted_quantity'] + $validated['rejected_quantity'] > $totalQty) {
            return back()
                ->withInput()
                ->withErrors(['rejected_quantity' => 'Jumlah accepted + rejected melebihi kuantitas PO.']);
        }

        $validated['inspected_by'] = auth()->id();
        $validated['inspected_at'] = now();

        $inspection = QcInspection::create($validated);

        // Auto-transition status based on conditions
        if ($inspection->rejected_quantity > 0 && $inspection->accepted_quantity > 0) {
            $inspection->status = 'partially_accepted';
        } elseif ($inspection->rejected_quantity > 0) {
            $inspection->status = 'rejected';
        } elseif ($inspection->accepted_quantity > 0) {
            $inspection->status = 'accepted';
        }

        $inspection->save();

        return redirect()
            ->route('qc.show', $inspection->id)
            ->with('success', 'Inspeksi kualitas berhasil dicatat. Nomor: ' . $inspection->display_number);
    }
public function show(QcInspection $inspection)
    {
        $this->authorize('view', $inspection);

        $canAccept = $this->policy->accept(auth()->user(), $inspection);
        $canReject = $this->policy->reject(auth()->user(), $inspection);

        return view('qc.show', compact('inspection', 'canAccept', 'canReject'));
    }

    public function edit(QcInspection $inspection)
    {
        $this->authorize('update', $inspection);

        $pendingReceptions = PurchaseReceipt::where('status', 'fully_received')
            ->whereNotNull('total_amount')
            ->whereDoesntHave('qcInspection', fn($q) => $q->where('id', '<>', $inspection->id))
            ->get();

        return view('qc.edit', compact('inspection', 'pendingReceptions'));
    }

    public function update(Request $request, QcInspection $inspection)
    {
        $this->authorize('update', $inspection);

        $validated = $request->validate([
            'receipt_id' => 'sometimes|required|exists:purchase_receipts,id',
            'purchase_order_id' => 'sometimes|required|exists:purchase_orders,id',
            'display_number' => 'sometimes|required|string|max:50|unique:qc_inspections,display_number,' . $inspection->id,
            'observations' => 'sometimes|nullable|string',
            'accepted_quantity' => 'sometimes|required|decimal:0,2',
            'rejected_quantity' => 'sometimes|required|decimal:0,2',
            'status' => 'sometimes|required|in:draft,received,pending_inspection,partially_accepted,accepted,rejected,posted',
        ]);

        // Ensure accepted + rejected <= total from receipt/Purchase Order
        $totalQty = $inspection->purchaseOrder
            ->purchaseRequestItems()
            ->sum('quantity') ?? 0;

        if ($validated['accepted_quantity'] + $validated['rejected_quantity'] > $totalQty) {
            return back()
                ->withInput()
                ->withErrors(['rejected_quantity' => 'Jumlah accepted + rejected melebihi kuantitas PO.']);
        }

        $inspection->update($validated);

        // Auto-transition status based on conditions
        if ($inspection->rejected_quantity > 0 && $inspection->accepted_quantity > 0) {
            $inspection->status = 'partially_accepted';
        } elseif ($inspection->rejected_quantity > 0) {
            $inspection->status = 'rejected';
        } elseif ($inspection->accepted_quantity > 0) {
            $inspection->status = 'accepted';
        }

        $inspection->save();

        return redirect()
            ->route('qc.show', $inspection->id)
            ->with('success', 'Data inspeksi kualitas berhasil diperbarui');
    }

    public function accept(QcInspection $inspection)
    {
        $this->authorize('accept', $inspection);

        return $this->processStatus($inspection, 'accepted');
    }

    public function reject(QcInspection $inspection)
    {
        $this->authorize('reject', $inspection);

        return $this->processStatus($inspection, 'rejected');
    }

    public function post(QcInspection $inspection)
    {
        $this->authorize('post', $inspection);

        return $this->processStatus($inspection, 'posted');
    }

    protected function processStatus(QcInspection $inspection, string $newStatus)
    {
        // Business rule: cannot post rejected without resolution
        if ($newStatus === 'posted' && $inspection->status === 'rejected') {
            // Check if there's a resolution (retur)
            $hasRetur = $inspection->receipt->returs()->where('status', 'approved')->exists();
            if (!$hasRetur) {
                return back()
                    ->with('error', 'Tidak dapat memposting status rejected tanpa retur barang yang disetujui.');
            }
        }

        $inspection->status = $newStatus;
        $inspection->save();

        // If posted, trigger stock posting
        if ($newStatus === 'posted') {
            // Post stock - this will trigger the stock ledger
        }

        return redirect()
            ->route('qc.show', $inspection->id)
            ->with('success', 'Status inspeksi berhasil diupdate menjadi ' . $inspection->getStatusLabelAttribute());
    }
}