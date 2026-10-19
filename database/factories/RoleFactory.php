<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'super_admin', 'warehouse', 'production', 'purchasing',
                'director', 'finance', 'qc',
            ]),
            'guard_name' => 'web',
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}