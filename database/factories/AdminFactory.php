<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Admin $admin) {
            if ($admin->user_id !== 0) {
                return;
            }

            $admin->user()->associate(User::factory()->create([
                'usertype_id' => -1,
                'usertype_type' => Admin::class,
            ]));
        })->afterCreating(function (Admin $admin) {
            if ($admin->user_id !== 0) {
                return;
            }

            $admin->user->usertype_id = $admin->id;
            $admin->user->save();
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
            'user_id' => 0,
        ];
    }
}
