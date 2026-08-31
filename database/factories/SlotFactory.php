<?php

namespace Database\Factories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slot>
 */
class SlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_quests' => fake()->numberBetween(1, 30),
            'points' => fake()->numberBetween(1, 50),
            'name' => fake()->word(),
        ];
    }
}
