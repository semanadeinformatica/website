<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Company $company) {
            if ($company->user_id !== 0) {
                return;
            }

            $company->user()->associate(User::factory()->create([
                'usertype_id' => -1,
                'usertype_type' => Company::class,
            ]));
        })->afterCreating(function (Company $company) {
            if ($company->user_id !== 0) {
                return;
            }

            $company->user->usertype_id = $company->id;
            $company->user->save();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->paragraph(),
            'user_id' => 0,
            'social_media_id' => SocialMedia::factory(),
        ];
    }
}
