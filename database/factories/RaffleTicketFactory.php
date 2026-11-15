<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaffleTicket>
 */
class RaffleTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_draw_id' => RaffleDraw::factory(),
            'enrollment_id' => Enrollment::factory(),
            'quantity' => 1,
        ];
    }
}
