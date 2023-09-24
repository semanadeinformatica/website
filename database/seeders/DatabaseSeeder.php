<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Department;
use App\Models\Edition;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\Participant;
use App\Models\Product;
use App\Models\Quest;
use App\Models\SocialMedia;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\Staff;
use App\Models\Stand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    const DEFAULT_ADMIN_EMAIL = 'admin@example.com';

    const PLATINUM_COUNT = 3;

    const GOLD_COUNT = 7;

    const SILVER_COUNT = 8;

    private function cleanDatabase()
    {
        DB::beginTransaction();

        User::truncate();
        Company::truncate();
        Participant::truncate();
        SocialMedia::truncate();
        Admin::truncate();
        Edition::truncate();
        Department::truncate();
        Staff::truncate();
        Event::truncate();
        Speaker::truncate();
        Quest::truncate();
        Sponsor::truncate();
        Product::truncate();

        DB::commit();
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        self::cleanDatabase();

        $participants = User::factory(100)->create();
        $companies = User::factory(static::PLATINUM_COUNT + static::GOLD_COUNT + static::SILVER_COUNT)->company()->create();
        $speakers = User::factory(10)->speaker()->create();

        if (! User::where('email', '=', static::DEFAULT_ADMIN_EMAIL)->exists()) {
            User::factory()->admin()->create([
                'email' => static::DEFAULT_ADMIN_EMAIL,
            ]);
        }

        $edition = Edition::factory()->create();

        $start_date = new Carbon(fake()->date());
        $event_day_factory = EventDay::factory()->recycle($edition);
        $event_days = array_map(fn () => $event_day_factory->create([
            'date' => $start_date->addDays(1)->toDateString(),
        ]), range(1, 7));

        $departments = Department::factory(10)->recycle($edition)->create();
        Staff::factory(20)->recycle($departments)->recycle($participants->pluck('usertype'))->create();

        foreach ($event_days as $day) {
            Event::factory(2)->recycle($day)->hasAttached($speakers->random(fake()->numberBetween(1, 2)))->create();
            Event::factory(1)->recycle($day)->hasAttached($companies->random(fake()->numberBetween(1, 5)))->create();
        }

        $sponsors = [];
        foreach ($companies as $i => $company) {
            Quest::factory()->recycle($edition)->for($company->usertype, 'requirement')->create();
            $sponsors[] = Sponsor::factory()->recycle($edition)->recycle($company->usertype)->create([
                'tier' => $i < static::PLATINUM_COUNT ? 'PLATINUM' : ($i < static::PLATINUM_COUNT + static::GOLD_COUNT ? 'GOLD' : 'SILVER'),
            ]);
        }

        Stand::factory(20)->recycle($event_days)->recycle($sponsors)->create();

        Product::factory(10)->recycle($edition)->create();

        $competitions = Competition::factory(3)->recycle($edition)->create();
        CompetitionTeam::factory(90)->recycle($competitions)->create();
    }
}
