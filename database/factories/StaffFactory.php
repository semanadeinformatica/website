<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
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
            'student_id' => \App\Models\Student::factory(),
            'department_id' => \App\Models\Department::factory(),
        ];
    }
}
