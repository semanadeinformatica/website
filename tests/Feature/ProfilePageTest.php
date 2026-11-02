<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_without_sponsor_is_redirected_when_viewing_another_profile(): void
    {
        Edition::factory()->create();
        $company = Company::factory()->create();
        $participant = Participant::factory()->create();

        $response = $this->actingAs($company->user)
            ->get(route('user.profile', ['user' => $participant->user_id]));

        $response->assertRedirect(route('profile.show'));
    }

    public function test_company_without_sponsor_can_view_own_profile(): void
    {
        Edition::factory()->create();
        $company = Company::factory()->create();

        $this->actingAs($company->user)
            ->get(route('profile.show'))
            ->assertOk();
    }
}
