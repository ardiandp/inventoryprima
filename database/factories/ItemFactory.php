<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'BRG' . fake()->unique()->numerify('#####'),
            'name' => fake()->unique()->words(2, true),
            'category_id' => null, // diisi oleh seeder setelah kategori tersedia
            'default_unit_id' => null, // diisi oleh seeder setelah unit tersedia
            'specification' => fake()->sentence(),
            'min_stock' => fake()->randomElement([0, 10, 50, 100]),
            'max_stock' => fake()->randomElement([100, 500, 1000]),
            'requires_qc' => fake()->boolean(30),
            'is_active' => true,
        ];
    }
}