<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EnrollmentFlagTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    private function enrollment(): Enrollment
    {
        return Enrollment::factory()->for($this->edition)->create();
    }

    private function updatePayload(Enrollment $enrollment, array $overrides = []): array
    {
        return array_merge([
            'participant_id' => $enrollment->participant_id,
            'edition_id' => $enrollment->edition_id,
            'points' => $enrollment->points ?? 0,
            'flagged' => false,
            'flag_reason' => '',
        ], $overrides);
    }

    public function test_admin_can_flag_an_enrollment(): void
    {
        $enrollment = $this->enrollment();

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->updatePayload($enrollment, [
                'flagged' => true,
                'flag_reason' => 'Multiple accounts',
            ]))
            ->assertRedirect();

        $enrollment->refresh();
        $this->assertNotNull($enrollment->flagged_at);
        $this->assertSame('Multiple accounts', $enrollment->flag_reason);
    }

    public function test_admin_can_unflag_an_enrollment(): void
    {
        $enrollment = $this->enrollment();
        $enrollment->forceFill([
            'flagged_at' => now(),
            'flag_reason' => 'Multiple accounts',
        ])->save();

        $this->actingAs($this->admin)
            ->put(route('admin.enrollments.update', $enrollment), $this->updatePayload($enrollment))
            ->assertRedirect();

        $enrollment->refresh();
        $this->assertNull($enrollment->flagged_at);
        $this->assertNull($enrollment->flag_reason);
    }

    public function test_flagged_user_sees_the_warning_on_their_profile(): void
    {
        $enrollment = $this->enrollment();
        $enrollment->forceFill(['flagged_at' => now()])->save();

        $this->actingAs($enrollment->participant->user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('isFlagged', true)
            );
    }

    public function test_other_users_do_not_see_the_flag(): void
    {
        $enrollment = $this->enrollment();
        $enrollment->forceFill(['flagged_at' => now()])->save();

        $this->actingAs($this->admin)
            ->get(route('user.profile', ['user' => $enrollment->participant->user_id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('isFlagged', false)
            );
    }

    public function test_non_admin_cannot_flag_an_enrollment(): void
    {
        $enrollment = $this->enrollment();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.enrollments.update', $enrollment), $this->updatePayload($enrollment, [
                'flagged' => true,
            ]))
            ->assertForbidden();
    }
}
