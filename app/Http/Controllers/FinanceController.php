<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Policies\FinancePolicy;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    protected $policy;

    public function __construct(FinancePolicy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view', Invoice::class);

        $invoices = Invoice::with(['purchaseOrder', 'paidBy'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->orderBy('due_date', 'asc')
            ->paginate(20);

        return view('finance.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Invoice::class);

        $invoices = Invoice::pending()->get();
        $purchaseOrders = \App\Models\PurchaseOrder::where('status', 'fully_received')
            ->whereNotNull('total_amount')
            ->get();

        return view('finance.create', compact('invoices', 'purchaseOrders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'display_number' => 'required|string|max:50|unique:invoices',
            'amount' => 'required|decimal:0,2',
            'tax_amount' => 'nullable|decimal:0,2',
            'total' => 'required|decimal:0,2',
            'due_date' => 'required|date|after_or_today',
            'status' => 'required|in:draft,sent,partial,paid,cancelled,void',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $invoice = Invoice::create($validated);

        // Auto-set status based on conditions
        if ($validated['amount'] > 0 && $validated['status'] !== 'paid') {
            $invoice->status = 'sent';
        }

        $invoice->save();

        return redirect()
            ->route('finance.index')
            ->with('success', 'Tagihan berhasil dibuat dengan nomor ' . $invoice->display_number);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        // Check if user can pay this invoice
        $canPay = $this->policy->pay(auth()->user(), $invoice);

        $payments = $invoice->payments()->latest()->get();

        return view('finance.show', compact('invoice', 'payments', 'canPay'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $purchaseOrders = \App\Models\PurchaseOrder::where('status', 'fully_received')
            ->whereNotNull('total_amount')
            ->where('id', '!=', $invoice->purchase_order_id)
            ->get();

        return view('finance.edit', compact('invoice', 'purchaseOrders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'purchase_order_id' => 'sometimes|required|exists:purchase_orders,id',
            'display_number' => 'sometimes|required|string|max:50|unique:invoices,display_number,' . $invoice->id,
            'amount' => 'sometimes|required|decimal:0,2',
            'tax_amount' => 'nullable|decimal:0,2',
            'total' => 'sometimes|required|decimal:0,2',
            'due_date' => 'sometimes|required|date|after_or_today',
            'status' => 'sometimes|required|in:draft,sent,partial,paid,cancelled,void',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $invoice->update($validated);

        return redirect()
            ->route('finance.show', $invoice)
            ->with('success', 'Tagihan berhasil diperbarui');
    }

    /**
     * Process payment for an invoice.
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'amount' => ['required', 'decimal:0,2', Rule::gte('invoice.amount')],
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Start transaction for atomicity
        \DB::transaction(function () use ($invoice, $validated) {
            // Create payment record
            $payment = $invoice->payments()->create([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'paid_by' => auth()->id(),
                'paid_at' => now(),
            ]);

            // Update invoice status
            $remaining = $invoice->amount - $invoice->payments()->sum('amount');

            if ($remaining <= 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->status === 'draft') {
                $invoice->status = 'partial';
            }

            $invoice->save();

            // Important: Finance cannot modify stock
            // Stock changes must go through proper stock posting flow
        });

        return redirect()
            ->route('finance.show', $invoice)
            ->with('success', 'Pembayaran berhasil dicatat. Tagihan ' . ($invoice->total - $invoice->payments()->sum('amount') > 0 ? 'belum lunas' : 'lunas'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->route('finance.index')
            ->with('success', 'Tagihan berhasil dibatalkan');
    }
}