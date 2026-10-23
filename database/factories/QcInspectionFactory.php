<?php

namespace Database\Factories;

use App\Models\QcInspection;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

class QcInspectionFactory extends Factory
{
    protected $model = QcInspection::class;

    public function definition(): array
    {
        $receipts = PurchaseReceipt::where('status', 'fully_received')->limit(10)->get();
        $pos = PurchaseOrder::limit(10)->get();

        $receiptId = $receipts->random()->id ?? 1;
        $poId = $pos->random()->id ?? 1;

        $statuses = ['draft', 'received', 'pending_inspection', 'partially_accepted', 'accepted', 'rejected', 'posted'];
        $statusWeight = [
            'draft' => 30,
            'received' => 20,
            'pending_inspection' => 20,
            'partially_accepted' => 10,
            'accepted' => 15,
            'rejected' => 10,
            'posted' => 5,
        ];
        $status = array_rand($statusWeight);

        $acceptedQty = random_int(0, 500);
        $rejectedQty = random_int(0, max(0, 200 - $acceptedQty));

        return [
            'receipt_id' => $receiptId,
            'purchase_order_id' => $poId,
            'display_number' => 'QC-' . Str::upper(Str::random(8)),
            'observations' => $this->faker->sentence(3),
            'accepted_quantity' => $acceptedQty,
            'rejected_quantity' => $rejectedQty,
            'status' => $statuses[$status],
            'inspected_by' => rand(1, 7),
            'inspected_at' => $this->faker->date_time_this_month(),
            'notes' => $this->faker->optional()->sentence(2),
        ];
    }
}