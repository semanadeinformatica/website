<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sponsor>
 */
class SponsorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tier' => fake()->randomElement(['PLATINUM', 'GOLD', 'SILVER']),
            'edition_id' => Edition::factory(),
            'company_id' => Company::factory(),
        ];
    }
}