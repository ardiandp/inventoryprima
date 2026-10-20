<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRequestItem>
 */
class PurchaseRequestItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_request_id' => null,
            'item_id' => null,
            'unit_id' => null,
            'quantity' => 0,
            'unit_price' => null,
            'minimum_term' => null,
            'specification' => null,
            'status' => 'pending',
            'rejection_reason' => null,
            'comment' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
