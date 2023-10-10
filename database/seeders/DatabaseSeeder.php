<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Department;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Participant;
use App\Models\Product;
use App\Models\Quest;
use App\Models\Slot;
use App\Models\SocialMedia;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorTier;
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

        Admin::truncate();
        Company::truncate();
        Competition::truncate();
        CompetitionTeam::truncate();
        Department::truncate();
        Edition::truncate();
        Enrollment::truncate();
        Event::truncate();
        EventDay::truncate();
        EventType::truncate();
        Participant::truncate();
        Product::truncate();
        Quest::truncate();
        Slot::truncate();
        SocialMedia::truncate();
        Speaker::truncate();
        Sponsor::truncate();
        SponsorTier::truncate();
        Staff::truncate();
        Stand::truncate();
        User::truncate();

        DB::commit();
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Clearing the database');
        self::cleanDatabase();

        $this->command->info('Creating the users');
        $participants = Participant::factory(100)->create();
        $participant_users = $participants->pluck('user');
        $companies = Company::factory(static::PLATINUM_COUNT + static::GOLD_COUNT + static::SILVER_COUNT)->create();
        $company_users = $companies->pluck('user');
        $speakers = Speaker::factory(10)->create();
        $speaker_users = $speakers->pluck('user');

        if (! User::where('email', '=', static::DEFAULT_ADMIN_EMAIL)->exists()) {
            User::factory()->admin()->create([
                'email' => static::DEFAULT_ADMIN_EMAIL,
            ]);
        } else {
            $this->command->warn('Admin user already exists');
        }

        $this->command->info('Creating the edition');
        $edition = Edition::factory()->create();

        $this->command->info('Creating the events');
        $start_date = new Carbon(fake()->date());
        $event_day_factory = EventDay::factory()->recycle($edition);
        $event_days = collect()->range(1, 7)->map(fn () => $event_day_factory->create([
            'date' => $start_date->addDays(1)->toDateString(),
        ]));

        $event_types = EventType::factory(2)->create();

        $events = $event_days->flatMap(fn ($day) => Event::factory(2)
            ->recycle($day)
            ->recycle($event_types)
            ->hasAttached($speaker_users->random(fake()->numberBetween(1, 2)))
            ->create()->concat(
                Event::factory(1)
                    ->recycle($day)
                    ->recycle($event_types)
                    ->hasAttached($company_users->random(fake()->numberBetween(1, 5)))
                    ->create()));

        $this->command->info('Creating the departments and staff');
        $departments = Department::factory(10)->recycle($edition)->create();
        Staff::factory(20)->recycle($departments)->recycle($participants)->create();

        $this->command->info('Creating the sponsors, tiers and stands');

        $sponsorTiers = SponsorTier::factory(3)->recycle($edition)->create();

        $sponsors = Sponsor::factory(20)
            ->recycle($edition)
            ->recycle($companies)
            ->recycle($sponsorTiers)
            ->create();

        $stands = Stand::factory(20)->recycle($event_days)->recycle($sponsors)->create();

        $this->command->info('Creating the products');
        $products = Product::factory(10)->recycle($edition)->create();

        $this->command->info('Creating the competitions');
        $competitions = Competition::factory(3)->recycle($edition)->create();
        $competition_teams = CompetitionTeam::factory(90)->recycle($competitions)->create();

        $this->command->info('Creating the quests');
        $slots = Slot::factory(30)->create();
        $quests = Quest::factory(20)
            ->recycle($edition)
            ->recycle($stands)
            ->for(Stand::factory(), 'requirement')
            ->create()->concat(
                Quest::factory(20)
                    ->recycle($edition)
                    ->recycle($events)
                    ->for(Event::factory(), 'requirement')
                    ->create()
            )->each(fn ($quest) => $quest->slots()->attach($slots->random()));

        $this->command->info('Creating the enrollments');
        $participants->random(50)->map(fn ($participant) => Enrollment::factory()
            ->recycle($edition)
            ->recycle($participant)
            ->hasAttached($events->random(3))
            ->hasAttached($products->random(3))
            ->hasAttached($quests->random(20))
            ->create());
    }
}
