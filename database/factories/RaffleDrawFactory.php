<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\RaffleDraw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaffleDraw>
 */
class RaffleDrawFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'edition_id' => Edition::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'ticket_price' => 50,
            'is_active' => true,
            'draw_at' => null,
            'winner_enrollment_id' => null,
        ];
    }
}
