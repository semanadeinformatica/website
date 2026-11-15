<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\FinalPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinalPrize>
 */
class FinalPrizeFactory extends Factory
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
            'place' => 1,
            'title' => fake()->words(3, true),
        ];
    }
}
