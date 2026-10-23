use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Models\StockTransactionItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockTransactionController extends Controller
{
    public function store(Request $request)
    {
        abort-if(!$this->policy->create(auth()->user()), 403);

        $validated = $request->validate([
            'receipt_id' => 'nullable|exists:purchase_receipts,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'production_order_id' => 'nullable|exists:production_orders,id',
            'display_number' => ['required', 'string', 'max:50', 'unique:stock_transactions,display_number'],
            'issue_quantity' => ['required', 'decimal:0,2', 'min:0'],
            'notes' => 'nullable|string|max:500',
        ]);

        $receiptId = $validated['receipt_id'] ?? null;
        $poId = $validated['purchase_order_id'] ?? null;

        $baseQuantity = 0;
        if ($receiptId) {
            $receipt = PurchaseReceipt::find($receiptId);
            $baseQuantity = $receipt ? $receipt->accepted_quantity : 0;
        } elseif ($poId) {
            $po = PurchaseOrder::find($poId);
            $baseQuantity = $po ? $po->total_quantity : 0;
        }

        $remainingQuantity = max(0, $baseQuantity - $validated['issue_quantity']);

        $transaction = StockTransaction::create([
            'purchase_receipt_id' => $receiptId,
            'purchase_order_id' => $poId,
            'display_number' => $validated['display_number'],
            'issue_quantity' => $validated['issue_quantity'],
            'remaining_quantity' => $remainingQuantity,
            'performed_by' => auth()->id(),
            'performed_at' => now(),
            'status' => 'draft',
            'notes' => $validated['notes'] ?? '',
        ]);

        $this->createTransactionItems($transaction, $receiptId, $poId);

        return redirect()
            ->route('stock-transactions.index')
            ->with('success', 'Transaksi stok berhasil dibuat dengan nomor ' . $transaction->display_number);
    }

    public function post(StockTransaction $stockTransaction)
    {
        abort-if(!$this->policy->post(auth()->user(), $stockTransaction), 403);

        if ($stockTransaction->issue_quantity <= 0) {
            return back()
                ->with('error', 'Tidak dapat posting: quantity issue harus lebih dari 0.')
                ->with('error-type', 'validation');
        }

        $baseQuantity = 0;
        if ($stockTransaction->purchase_receipt_id) {
            $receipt = PurchaseReceipt::find($stockTransaction->purchase_receipt_id);
            $baseQuantity = $receipt ? $receipt->accepted_quantity : 0;
        }

        if ($stockTransaction->issue_quantity > $baseQuantity) {
            return back()
                ->with('error', 'Tidak dapat posting: quantity issue melebihi stok tersedia.')
                ->with('error-type', 'validation');
        }

        $stockTransaction->update([
            'status' => 'posted',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('stock-transactions.show', $stockTransaction)
            ->with('success', 'Transaksi stok berhasil di-posting. Stok tersedia berkurang sejumlah ' . $stockTransaction->issue_quantity . ' ' . $stockTransaction->items->first()?->unit ?? 'pcs');
    }

    public function cancel(StockTransaction $stockTransaction)
    {
        abort-if(!$this->policy->cancel(auth()->user(), $stockTransaction), 403);

        $stockTransaction->update([
            'status' => 'cancelled',
            'notes' => $stockTransaction->notes . '(Dibatalkan: ' . now()->format('Y-m-d H:i:s') . ')',
        ]);

        return redirect()
            ->route('stock-transactions.index')
            ->with('success', 'Transaksi stok berhasil dibatalkan');
    }