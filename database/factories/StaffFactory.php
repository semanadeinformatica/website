<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Participant;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coordinator' => fake()->boolean(20),
            'participant_id' => Participant::factory(),
            'department_id' => Department::factory(),
        ];
    }
}
