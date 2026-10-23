<?php

namespace Database\Factories;

use App\Models\StockTransaction;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

class StockTransactionFactory extends Factory
{
    protected $model = StockTransaction::class;

    public function definition(): array
    {
        $receipts = PurchaseReceipt::where('status', 'fully_received')->limit(10)->get();
        $pos = PurchaseOrder::limit(10)->get();

        $receiptId = $receipts->random()->id ?? 1;
        $poId = $pos->random()->id ?? 1;

        $statuses = ['draft', 'posted', 'cancelled'];
        $statusWeights = [
            'draft' => 60,
            'posted' => 30,
            'cancelled' => 10,
        ];
        $status = array_rand($statusWeights);

        $issueQty = random_int(0, 500);

        return [
            'purchase_receipt_id' => $receiptId,
            'purchase_order_id' => $poId,
            'display_number' => 'STK-' . Str::upper(Str::random(8)),
            'issue_quantity' => $issueQty,
            'accepted_quantity' => random_int(0, max(0, 500 - $issueQty)),
            'remaining_quantity' => max(0, random_int(0, 500 - $issueQty)),
            'status' => $statuses[$status],
            'performed_by' => rand(1, 7),
            'performed_at' => $this->faker->date_time_this_month(),
            'notes' => $this->faker->optional()->sentence(3),
        ];
    }
}