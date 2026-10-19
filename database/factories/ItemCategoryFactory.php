<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemCategory>
 */
class ItemCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CAT' . fake()->unique()->numerify('###'),
            'name' => fake()->unique()->randomElement([
                'Bahan Baku', 'Barang Jadi', 'Peralatan', 'Consumable',
                'Sparepart', 'Packaging', 'MRO',
            ]),
            'description' => fake()->sentence(),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}