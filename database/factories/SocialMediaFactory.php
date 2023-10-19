<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->boolean() ? fake()->email() : null,
            'facebook' => fake()->boolean() ? fake()->regexify('^https:\/\/(www\.)?facebook\.com\/[a-zA-Z0-9.]{5,}\/?$') : null,
            'github' => fake()->boolean() ? fake()->regexify('^https:\/\/(www\.)?github\.com\/[a-zA-Z\d]{1,38}\/?$') : null,
            'instagram' => fake()->boolean() ? fake()->regexify('^https:\/\/(www\.)?instagram\.com\/[a-zA-Z0-9_.]{1,30}\/?$') : null,
            'linkedin' => fake()->boolean() ? fake()->regexify('^https:\/\/(www\.)?linkedin\.com\/(in|company)\/[a-zA-Z0-9-]{3,100}\/?$') : null,
            'twitter' => fake()->boolean() ? fake()->regexify('^https:\/\/(www\.)?(twitter|x)\.com\/[a-zA-Z0-9_]{4,15}\/?$') : null,
            'website' => fake()->boolean() ? 'https://'.fake()->domainName() : null,
        ];
    }
}
