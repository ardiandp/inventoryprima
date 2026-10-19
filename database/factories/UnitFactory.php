<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->randomElement(['PCS', 'BOX', 'KG', 'LTR', 'MTR', 'SET', 'ROL', 'BDL']),
            'name' => fake()->unique()->randomElement([
                'Pcs', 'Box', 'Kg', 'Liter', 'Meter', 'Set', 'Roll', 'Bundle',
            ]),
            'symbol' => fake()->randomElement(['pcs', 'box', 'kg', 'L', 'm', 'set', 'roll', 'bdl']),
            'is_active' => true,
        ];
    }
}