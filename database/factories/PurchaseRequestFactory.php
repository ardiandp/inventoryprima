<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display_number' => null,
            'requested_by' => null,
            'request_type' => 'regular',
            'reason' => fake()->sentence(),
            'priority' => 'normal',
            'projected_date' => null,
            'department_id' => null,
            'project_id' => null,
            'status' => 'draft',
            'reviewed_by' => null,
            'rejection_reason' => null,
            'comment' => null,
            'approved_by' => null,
            'po_number' => null,
            'attachment_id' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_review',
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

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
