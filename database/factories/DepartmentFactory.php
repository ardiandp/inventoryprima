<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'DEPT' . fake()->unique()->numerify('###'),
            'name' => fake()->unique()->company(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}