<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArcadeLobbyTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $activeGame;

    private ArcadeGame $inactiveGame;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();

        $this->activeGame = ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
            'is_active' => true,
            'is_pvp' => true,
            'min_wager' => 10,
            'max_wager' => 500,
            'payout_config' => ['house_edge_pct' => 10],
        ]);

        $this->inactiveGame = ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => 'binary-blitz',
            'name' => 'Binary Blitz',
            'is_active' => false,
            'is_pvp' => false,
            'min_wager' => 5,
            'max_wager' => 100,
            'payout_config' => [],
        ]);
    }

    public function test_guest_can_access_arcade_lobby(): void
    {
        $response = $this->get(route('arcade.lobby'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Arcade')
            ->has('games', 1)
            ->where('games.0.name', 'Coin Flip')
            ->where('isEnrolled', false)
            ->where('isParticipant', false)
            ->where('points', null)
        );
    }

    public function test_logged_in_non_enrolled_user_sees_enroll_cta_state(): void
    {
        $participant = Participant::factory()->create();
        $user = $participant->user;

        $response = $this->actingAs($user)->get(route('arcade.lobby'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Arcade')
            ->has('games', 1)
            ->where('isEnrolled', false)
            ->where('isParticipant', true)
            ->where('points', null)
        );
    }

    public function test_enrolled_participant_sees_points_balance(): void
    {
        $participant = Participant::factory()->create();
        $user = $participant->user;
        Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 350,
        ]);

        $response = $this->actingAs($user)->get(route('arcade.lobby'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Arcade')
            ->has('games', 1)
            ->where('isEnrolled', true)
            ->where('isParticipant', true)
            ->where('points', 350)
        );
    }

    public function test_only_active_games_are_displayed(): void
    {
        $response = $this->get(route('arcade.lobby'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Arcade')
            ->has('games', 1)
            ->where('games.0.id', $this->activeGame->id)
        );
    }
}
