<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Edition;
use App\Models\Event;
use App\Models\Product;
use App\Models\Quest;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(100)->create();
        $companies = User::factory(10)->company()->create();
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $edition = Edition::factory()->create();

        $events = Event::factory(10)->recycle($edition)->create();
        Speaker::factory(10)->recycle($events)->create();

        foreach ($companies as $company) {
            Quest::factory()->recycle($edition)->for($company, 'requirement')->create();
        }

        Product::factory(10)->recycle($edition)->create();
    }
}
