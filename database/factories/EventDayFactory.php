<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\EventDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDay>
 */
class EventDayFactory extends Factory
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
            'date' => fake()->date(),
            'theme' => fake()->realText(),
        ];
    }
}
