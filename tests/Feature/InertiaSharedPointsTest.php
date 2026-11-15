<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaSharedPointsTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
    }

    public function test_guest_gets_null_enrollment_points(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('enrollment_points', null)
        );
    }

    public function test_non_participant_gets_null_enrollment_points(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('enrollment_points', null)
        );
    }

    public function test_unenrolled_participant_gets_null_enrollment_points(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->actingAs($participant->user)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('enrollment_points', null)
        );
    }

    public function test_enrolled_participant_gets_shared_enrollment_points(): void
    {
        $participant = Participant::factory()->create();
        Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 350,
        ]);

        $response = $this->actingAs($participant->user)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('enrollment_points', 350)
        );
    }
}
