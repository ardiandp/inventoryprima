/**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_if(!auth()->user()->can('po.view'), 403);

        $status = request('status') ?? null;
        $query = PurchaseOrder::query();

        if ($status) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->with(['rpo', 'supplier', 'items'])->latest()->paginate(20);

        return view('po.index', compact('purchaseOrders', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(!auth()->user()->can('po.create'), 403);

        // Only allow creating PO from approved RPOs
        $approvedRpos = PurchaseRequest::where('status', PurchaseRequest::STATUS_APPROVED)
            ->whereNotNull('rpo_id')
            ->get();

        return view('po.create', compact('approvedRpos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('po.create'), 403);

        $validated = $request->validate([
            'rpo_id' => 'required|exists:purchase_requests,id',
            'supplier_id' => 'required|exists:users,id',
            'po_number' => 'required|string|max:50|unique:purchase_orders',
            'status' => 'required|in:draft,sent,supplier_confirmed,partially_received,fully_received,closed,cancelled',
            'total_amount' => 'required|decimal:0,2',
            'delivery_address' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Verify RPO is approved
        $rpo = PurchaseRequest::findOrFail($validated['rpo_id']);
        if ($rpo->status !== PurchaseRequest::STATUS_APPROVED) {
            throw new AuthorizationException('Hanya RPO yang sudah diapprove yang dapat dibuat menjadi PO.');
        }

        $purchaseOrder = PurchaseOrder::create([
            'rpo_id' => $validated['rpo_id'],
            'supplier_id' => $validated['supplier_id'],
            'po_number' => $validated['po_number'],
            'status' => $validated['status'],
            'total_amount' => $validated['total_amount'],
            'delivery_address' => $validated['delivery_address'],
            'delivery_date' => $validated['delivery_date'],
            'notes' => $validated['notes'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Create PO items from RPO items
        foreach ($rpo->items as $rpoItem) {
            $purchaseOrder->items()->create([
                'item_code' => $rpoItem->item_code,
                'description' => $rpoItem->description,
                'quantity' => $rpoItem->quantity,
                'unit' => $rpoItem->unit,
                'unit_price' => $rpoItem->price ?? 0,
                'total_price' => ($rpoItem->price ?? 0) * $rpoItem->quantity,
                'status' => PurchaseOrderItem::STATUS_PENDING,
            ]);
        }

        return redirect()->route('po.index')
            ->with('success', 'Purchase Order berhasil dibuat dari RPO #' . $rpo->id . ' - ' . $rpo->po_number . '.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('po.view', $purchaseOrder), 403);

        return view('po.show', compact('purchaseOrder'));
    }
<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
/**
     * Supplier confirms PO.
     */
    public function confirmSupplier(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('confirmSupplier', $purchaseOrder), 403);

        $validated = $request->validate([
            'confirmed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->status = PurchaseOrder::STATUS_SUPPLIER_CONFIRMED;
        $purchaseOrder->save();

        // Update items status to approved
        $purchaseOrder->items()->where('status', PurchaseOrderItem::STATUS_PENDING)->update([
            'status' => PurchaseOrderItem::STATUS_APPROVED,
        ]);

        return redirect()->route('po.show', $purchaseOrder)
            ->with('success', 'PO berhasil dikonfirmasi oleh supplier.');
    }

    /**
     * Receive partial delivery.
     */
    public function receivePartial(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('receivePartial', $purchaseOrder), 403);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        foreach ($validated['items'] as $item) {
            $poItem = $purchaseOrder->items()->findOrFail($item['item_id']);

            // Update received quantity
            $poItem->update([
                'quantity_received' => $item['quantity_received'],
                'status' => PurchaseOrderItem::STATUS_APPROVED,
            ]);
        }

        // Check if we should move to partially_received status
        $fullyReceived = $purchaseOrder->items->every(function ($item) {
            return $item->quantity_received >= $item->quantity;
        });

        if ($fullyReceived) {
            $purchaseOrder->status = PurchaseOrder::STATUS_PARTIALLY_RECEIVED;
        }

        $purchaseOrder->save();

        return redirect()->route('po.show', $purchaseOrder)
            ->with('success', 'Penerimaan parsial PO berhasil dicatat.');
    }

    /**
     * Receive full delivery.
     */
    public function receiveFully(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('receiveFully', $purchaseOrder), 403);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->status = PurchaseOrder::STATUS_FULLY_RECEIVED;
        $purchaseOrder->delivered_by = auth()->id();
        $purchaseOrder->delivered_at = now();
        $purchaseOrder->save();

        // Update all items to received
        $purchaseOrder->items()->update([
            'quantity_received' => fn($qty) => $qty,
            'status' => PurchaseOrderItem::STATUS_APPROVED,
        ]);

        return redirect()->route('po.show', $purchaseOrder)
            ->with('success', 'PO berhasil diterima secara penuh.');
    }

    /**
     * Close PO.
     */
    public function closePO(PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('closePO', $purchaseOrder), 403);

        $purchaseOrder->status = PurchaseOrder::STATUS_CLOSED;
        $purchaseOrder->save();

        return redirect()->route('po.show', $purchaseOrder)
            ->with('success', 'Purchase Order berhasil ditutup.');
    }

    /**
     * Cancel PO.
     */
    public function cancelPO(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if(!auth()->user()->can('cancelPO', $purchaseOrder), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $purchaseOrder->status = PurchaseOrder::STATUS_CANCELLED;
        $purchaseOrder->notes = $validated['reason'] . ' | ' . ($purchaseOrder->notes ?? '');
        $purchaseOrder->save();

        return redirect()->route('po.show', $purchaseOrder)
            ->with('success', 'Purchase Order dibatalkan.');
    }
}