<?php

namespace Database\Factories;

use App\Models\EventDay;
use App\Models\Sponsor;
use App\Models\Stand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stand>
 */
class StandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_day_id' => EventDay::factory(),
            'sponsor_id' => Sponsor::factory(),
        ];
    }
}
