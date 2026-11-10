<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Edition;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorTier;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDataExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_do_not_expose_account_emails(): void
    {
        $edition = Edition::factory()->create();
        $eventDay = EventDay::factory()->for($edition)->create();
        $event = Event::factory()->for($eventDay, 'event_day')->create();

        $speaker = Speaker::factory()->create();
        $event->users()->attach($speaker->user);

        $department = Department::factory()->for($edition)->create();
        $staff = Staff::factory()->for($department)->create();

        $tier = SponsorTier::factory()->for($edition)->create();
        $sponsor = Sponsor::factory()->for($edition)->for($tier, 'tier')->create();

        $emails = [
            $speaker->user->email,
            $staff->participant->user->email,
            $sponsor->company->user->email,
        ];

        foreach (['/', '/team', '/program', '/event/'.$event->id] as $url) {
            $response = $this->get($url);

            $response->assertOk();

            foreach ($emails as $email) {
                $response->assertDontSee($email);
            }
        }
    }
}
