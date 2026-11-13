<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Department;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorTier;
use App\Models\Staff;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicDataExposureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_do_not_expose_private_data(): void
    {
        $edition = Edition::factory()->create();
        $eventDay = EventDay::factory()->for($edition)->create();
        $event = Event::factory()->for($eventDay, 'event_day')->create();

        $speaker = Speaker::factory()->create();
        $event->users()->attach($speaker->user);

        $department = Department::factory()->for($edition)->create();
        $staff = Staff::factory()->for($department)->create();
        $staff->participant->forceFill(['cv_path' => 'cvs/public-data-test.pdf'])->save();

        $tier = SponsorTier::factory()->for($edition)->create();
        $sponsor = Sponsor::factory()->for($edition)->for($tier, 'tier')->create();

        $competition = Competition::factory()->for($edition)->create([
            'slug' => 'public-data-competition',
        ]);
        $team = CompetitionTeam::factory()->for($competition)->create();
        $team->members()->attach($staff->participant);

        $emails = [
            $speaker->user->email,
            $staff->participant->user->email,
            $sponsor->company->user->email,
        ];

        foreach (['/', '/team', '/program', '/event/'.$event->id, '/competition/'.$competition->slug] as $url) {
            $response = $this->get($url);

            $response->assertOk();

            foreach ($emails as $email) {
                $response->assertDontSee($email);
            }

            $response->assertDontSee('public-data-test.pdf');
            $response->assertDontSee('cv_url');
        }
    }

    public function test_cv_route_redirects_guests_to_login(): void
    {
        $user = User::factory()->create();
        $user->usertype->forceFill(['cv_path' => 'cvs/test.pdf'])->save();

        $this->get(route('user.cv', ['user' => $user->id]))
            ->assertRedirect(route('login'));
    }

    public function test_cv_route_allows_the_owner(): void
    {
        Storage::fake(config('jetstream.cv_disk'));
        Edition::factory()->create();

        $user = User::factory()->create();
        $user->usertype->forceFill(['cv_path' => 'cvs/test.pdf'])->save();
        Storage::disk(config('jetstream.cv_disk'))->put('cvs/test.pdf', 'pdf');

        $this->actingAs($user)
            ->get(route('user.cv', ['user' => $user->id]))
            ->assertOk();
    }

    public function test_cv_route_forbids_other_participants(): void
    {
        Edition::factory()->create();

        $owner = User::factory()->create();
        $owner->usertype->forceFill(['cv_path' => 'cvs/test.pdf'])->save();

        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('user.cv', ['user' => $owner->id]))
            ->assertForbidden();
    }

    public function test_cv_upload_rejects_non_pdf_files(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('current-user-cv.update'), [
                'cv' => UploadedFile::fake()->create('cv.html', 10, 'text/html'),
            ])
            ->assertSessionHasErrors('cv');
    }

    public function test_cv_route_forbids_company_from_viewing_non_member_participants(): void
    {
        Storage::fake(config('jetstream.cv_disk'));

        $edition = Edition::factory()->create();
        $tier = SponsorTier::factory()->for($edition)->create(['canSeeCV' => true]);

        $companyUser = User::factory()->company()->create();

        $member = Participant::factory()->create();
        $this->linkParticipantToCompany($companyUser, $edition, $member, $tier);

        // A participant the company only met in a previous edition must not be accessible.
        $oldEdition = Edition::factory()->create(['year' => now()->year - 1]);
        $oldTier = SponsorTier::factory()->for($oldEdition)->create(['canSeeCV' => true]);
        $oldMember = Participant::factory()->create();
        $this->linkParticipantToCompany($companyUser, $oldEdition, $oldMember, $oldTier);

        $outsider = Participant::factory()->create();

        foreach ([$member, $oldMember, $outsider] as $participant) {
            $participant->forceFill(['cv_path' => 'cvs/test.pdf'])->save();
        }
        Storage::disk(config('jetstream.cv_disk'))->put('cvs/test.pdf', 'pdf');

        $this->actingAs($companyUser)
            ->get(route('user.cv', ['user' => $member->user_id]))
            ->assertOk();

        $this->actingAs($companyUser)
            ->get(route('user.cv', ['user' => $outsider->user_id]))
            ->assertForbidden();

        $this->actingAs($companyUser)
            ->get(route('user.cv', ['user' => $oldMember->user_id]))
            ->assertForbidden();
    }

    private function linkParticipantToCompany(User $companyUser, Edition $edition, Participant $participant, SponsorTier $tier): void
    {
        $sponsor = Sponsor::factory()
            ->for($edition)
            ->for($tier, 'tier')
            ->for($companyUser->usertype, 'company')
            ->create();
        $eventDay = EventDay::factory()->for($edition)->create();
        $stand = Stand::factory()->for($sponsor, 'sponsor')->for($eventDay, 'event_day')->create();
        $quest = Quest::factory()->for($edition)->create([
            'requirement_type' => Stand::class,
            'requirement_id' => $stand->id,
        ]);
        $enrollment = Enrollment::factory()->for($edition)->for($participant, 'participant')->create();

        DB::table('enrollment_quest')->insert([
            'enrollment_id' => $enrollment->id,
            'quest_id' => $quest->id,
        ]);
    }
}
