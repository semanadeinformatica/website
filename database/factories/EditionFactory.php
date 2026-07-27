<?php

namespace Database\Factories;

use App\Models\Edition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Edition>
 */
class EditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'SINF '.Carbon::now()->year,
            'year' => Carbon::now()->year,
        ];
    }
}
