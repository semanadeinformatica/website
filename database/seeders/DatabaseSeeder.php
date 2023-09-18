<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Department;
use App\Models\CompetitionInfo;
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
        $companies = User::factory(10)->company()->create();

        if (! User::where('email', '=', DatabaseSeeder::DEFAULT_ADMIN_EMAIL)->exists()) {
            User::factory()->admin()->create([
                'email' => DatabaseSeeder::DEFAULT_ADMIN_EMAIL,
            ]);
        }

        $edition = Edition::factory()->create();

        $start_date = new Carbon(fake()->date());
        $event_day_factory = EventDay::factory()->recycle($edition);
        $event_days = array_map(fn () => $event_day_factory->create([
            'date' => $start_date->addDays(1)->toDateString(),
        ]), range(0, 7));

        $departments = Department::factory(10)->recycle($edition)->create();
        Staff::factory(20)->recycle($departments)->recycle($participants->pluck('usertype'))->create();

        foreach ($event_days as $day) {
            $events = Event::factory(10)->recycle($day)->create();
            Speaker::factory(10)->recycle($events)->create();
        }

        $sponsors = [];
        foreach ($companies as $company) {
            Quest::factory()->recycle($edition)->for($company->usertype, 'requirement')->create();
            $sponsors[] = Sponsor::factory()->recycle($edition)->recycle($company->usertype)->create();
        }

        Stand::factory(20)->recycle($event_days)->recycle($sponsors)->create();

        Product::factory(10)->recycle($edition)->create();

        CompetitionInfo::factory()->recycle($edition)->recycle($participants)->create();
    }
}
