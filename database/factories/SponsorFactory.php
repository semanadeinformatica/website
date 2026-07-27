<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Sponsor;
use App\Models\SponsorTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
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
            'sponsor_tier_id' => SponsorTier::factory(),
            'edition_id' => Edition::factory(),
            'company_id' => Company::factory(),
        ];
    }
}
