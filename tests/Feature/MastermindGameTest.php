<?php

namespace Tests\Feature;

use App\Http\Controllers\ArcadeController;
use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use App\Services\ArcadeWagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MastermindGameTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $game;

    private User $user;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();

        $this->game = ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => 'mastermind',
            'name' => 'Mastermind',
            'is_active' => true,
            'is_pvp' => false,
            'min_wager' => 5,
            'max_wager' => 100,
            'payout_config' => [
                'max_attempts' => 5,
                'attempt_multipliers' => [
                    '1' => 6.0,
                    '2' => 4.0,
                    '3' => 2.5,
                    '4' => 1.8,
                    '5' => 1.5,
                ],
            ],
        ]);

        $participant = Participant::factory()->create();
        $this->user = $participant->user;
        $this->enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 200,
        ]);
    }

    public function test_guest_cannot_start_mastermind(): void
    {
        $response = $this->postJson(route('arcade.mastermind.start'), [
            'wager' => 10,
            'edition' => $this->edition,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_start_with_insufficient_points(): void
    {
        $this->enrollment->update(['points' => 5]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.start'), [
            'wager' => 10,
            'edition' => $this->edition,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_wager_below_min_or_above_max(): void
    {
        $responseLow = $this->actingAs($this->user)->postJson(route('arcade.mastermind.start'), [
            'wager' => 2,
            'edition' => $this->edition,
        ]);
        $responseLow->assertStatus(422);

        $responseHigh = $this->actingAs($this->user)->postJson(route('arcade.mastermind.start'), [
            'wager' => 150,
            'edition' => $this->edition,
        ]);
        $responseHigh->assertStatus(422);
    }

    public function test_user_cannot_play_when_game_is_inactive(): void
    {
        $this->game->update(['is_active' => false]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.start'), [
            'wager' => 10,
            'edition' => $this->edition,
        ]);

        $response->assertStatus(403);
    }

    public function test_start_mastermind_deducts_wager_and_does_not_reveal_code(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.start'), [
            'wager' => 20,
            'edition' => $this->edition,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['session_id', 'max_attempts', 'points', 'balance']);
        $this->assertArrayNotHasKey('code', $response->json());

        $this->assertEquals(180, $this->enrollment->fresh()->points);

        $session = ArcadeSession::findOrFail($response->json('session_id'));
        $this->assertEquals('pending', $session->outcome);
        $this->assertEquals(20, $session->wager);
        $this->assertArrayHasKey('code', $session->server_state);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $session->server_state['code']);
    }

    public function test_invalid_guess_fails_validation(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'pending',
            'server_state' => ['code' => '1234', 'attempts' => []],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => 'abc',
            'edition' => $this->edition,
        ]);
        $response->assertStatus(422);

        $responseLong = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '12345',
            'edition' => $this->edition,
        ]);
        $responseLong->assertStatus(422);
    }

    public function test_hint_calculation_accuracy(): void
    {
        // code = "1122", guess = "1212" -> exact=2, misplaced=2
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'pending',
            'server_state' => ['code' => '1122', 'attempts' => []],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '1212',
            'edition' => $this->edition,
        ]);

        $response->assertOk();
        $response->assertJson([
            'exact' => 2,
            'misplaced' => 2,
            'feedback' => ['exact', 'misplaced', 'misplaced', 'exact'],
            'attempts_used' => 1,
            'outcome' => 'pending',
        ]);
        $this->assertArrayNotHasKey('code', $response->json());
    }

    public function test_user_wins_and_receives_correct_multiplier(): void
    {
        // Solved on attempt 2 (multiplier 4.0)
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 20,
            'outcome' => 'pending',
            'server_state' => [
                'code' => '0371',
                'attempts' => [
                    ['guess' => '1234', 'exact' => 1, 'misplaced' => 1],
                ],
            ],
            'started_at' => now(),
        ]);

        $initialPoints = $this->enrollment->points;

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '0371',
            'edition' => $this->edition,
        ]);

        $response->assertOk();
        $response->assertJson([
            'exact' => 4,
            'misplaced' => 0,
            'attempts_used' => 2,
            'outcome' => 'win',
            'multiplier' => 4.0,
            'payout' => 80,
            'code' => '0371',
        ]);

        $this->assertEquals($initialPoints + 80, $this->enrollment->fresh()->points);
        $this->assertEquals('win', $session->fresh()->outcome);
        $this->assertEquals(80, $session->fresh()->payout);
    }

    public function test_user_loses_on_5th_attempt_and_code_is_revealed(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'pending',
            'server_state' => [
                'code' => '0371',
                'attempts' => [
                    ['guess' => '0000', 'exact' => 1, 'misplaced' => 0],
                    ['guess' => '1111', 'exact' => 1, 'misplaced' => 0],
                    ['guess' => '2222', 'exact' => 0, 'misplaced' => 0],
                    ['guess' => '3333', 'exact' => 1, 'misplaced' => 0],
                ],
            ],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '9999',
            'edition' => $this->edition,
        ]);

        $response->assertOk();
        $response->assertJson([
            'exact' => 0,
            'misplaced' => 0,
            'attempts_used' => 5,
            'outcome' => 'loss',
            'code' => '0371',
        ]);

        $this->assertEquals('loss', $session->fresh()->outcome);
        $this->assertEquals(0, $session->fresh()->payout);
    }

    public function test_stale_session_cannot_bypass_attempt_limit(): void
    {
        $attempts = array_fill(0, 5, [
            'guess' => '0000',
            'exact' => 0,
            'misplaced' => 0,
        ]);

        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'pending',
            'server_state' => [
                'code' => '0371',
                'attempts' => array_slice($attempts, 0, 4),
            ],
            'started_at' => now(),
        ]);

        $staleSession = ArcadeSession::findOrFail($session->id);

        $session->update([
            'server_state' => [
                'code' => '0371',
                'attempts' => $attempts,
            ],
        ]);

        $request = Request::create("/arcade/mastermind/{$session->id}/guess", 'POST', [
            'guess' => '9999',
        ]);
        $request->setUserResolver(fn () => $this->user);

        try {
            app(ArcadeController::class)->guessMastermind(
                $request,
                $staleSession,
                app(ArcadeWagerService::class),
            );
            $this->fail('Expected the stale session to be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $fresh = $session->fresh();
        $this->assertSame('pending', $fresh->outcome);
        $this->assertCount(5, $fresh->server_state['attempts']);
    }

    public function test_cannot_guess_on_resolved_session(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'loss',
            'server_state' => ['code' => '0371', 'attempts' => []],
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '0371',
            'edition' => $this->edition,
        ]);

        $response->assertStatus(422);
    }

    public function test_unauthorized_user_cannot_guess_on_other_session(): void
    {
        $otherParticipant = Participant::factory()->create();
        $otherUser = $otherParticipant->user;
        Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $otherParticipant->id,
            'points' => 100,
        ]);

        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'pending',
            'server_state' => ['code' => '0371', 'attempts' => []],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)->postJson(route('arcade.mastermind.guess', $session), [
            'guess' => '0371',
            'edition' => $this->edition,
        ]);

        $response->assertStatus(403);
    }
}
