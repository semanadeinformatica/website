<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use App\Services\BinaryBlitzQuestionGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class BinaryBlitzGameTest extends TestCase
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
            'slug' => 'binary-blitz',
            'name' => 'Binary Blitz',
            'is_active' => true,
            'is_pvp' => false,
            'min_wager' => 10,
            'max_wager' => 100,
            'daily_win_cap' => 500,
            'payout_config' => [
                'question_count' => 3,
                'time_limit_seconds' => 12,
                'win_multiplier' => 2.5,
                'partial_multiplier' => 1.0,
            ],
        ]);

        $participant = Participant::factory()->create();
        $this->user = $participant->user;
        $this->enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 300,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_start_returns_prompts_without_answers_and_deducts_wager(): void
    {
        Carbon::setTestNow('2026-11-14 12:00:00');

        $generator = Mockery::mock(BinaryBlitzQuestionGenerator::class);
        $generator->shouldReceive('generate')->once()->with(3)->andReturn($this->questions());
        $this->app->instance(BinaryBlitzQuestionGenerator::class, $generator);

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.start', [
            'edition' => $this->edition,
            'wager' => 20,
        ]));

        $response->assertCreated()
            ->assertJsonPath('prompts.0.prompt', '0b1011 em decimal?')
            ->assertJsonPath('deadline', now()->addSeconds(12)->toISOString())
            ->assertJsonMissingPath('prompts.0.answer')
            ->assertJsonMissingPath('answers');

        $this->assertSame(280, $this->enrollment->fresh()->points);

        $session = ArcadeSession::latest('id')->firstOrFail();
        $this->assertSame('pending', $session->outcome);
        $this->assertSame(20, $session->wager);
        $this->assertCount(3, $session->server_state['questions']);
        $this->assertSame('2026-11-14T12:00:12.000000Z', $session->server_state['deadline']);
    }

    public function test_it_rejects_a_submission_after_the_server_deadline(): void
    {
        Carbon::setTestNow('2026-11-14 12:00:00');

        $session = $this->sessionWithDeadline(now()->subMillisecond());

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ]);

        $response->assertStatus(422);
        $this->assertSame('expired', $session->fresh()->outcome);
        $this->assertSame(300, $this->enrollment->fresh()->points);
    }

    public function test_all_correct_answers_pay_the_win_multiplier(): void
    {
        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ]);

        $response->assertOk()->assertJson([
            'correct' => 3,
            'total' => 3,
            'payout' => 50,
            'outcome' => 'win',
            'feedback' => [true, true, true],
        ]);

        $this->assertSame(350, $this->enrollment->fresh()->points);
        $this->assertSame('win', $session->fresh()->outcome);
    }

    public function test_two_out_of_three_correct_answers_refund_the_wager(): void
    {
        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 0],
        ]);

        $response->assertOk()->assertJson([
            'correct' => 2,
            'total' => 3,
            'payout' => 20,
            'outcome' => 'partial',
            'feedback' => [true, true, false],
        ]);

        $this->assertSame(320, $this->enrollment->fresh()->points);
    }

    public function test_one_or_zero_correct_answers_lose(): void
    {
        foreach ([[11, 0, 0], [0, 0, 0]] as $answers) {
            $session = $this->sessionWithDeadline(now()->addSeconds(12));

            $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
                'edition' => $this->edition,
                'answers' => $answers,
            ]);

            $response->assertOk()->assertJson([
                'outcome' => 'loss',
                'payout' => 0,
            ]);
        }

        $this->assertSame(300, $this->enrollment->fresh()->points);
    }

    public function test_daily_cap_is_clamped_before_credit(): void
    {
        $this->game->update(['daily_win_cap' => 30]);

        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ]);

        $response->assertOk()->assertJson([
            'payout' => 30,
            'outcome' => 'win',
        ]);

        $this->assertSame(330, $this->enrollment->fresh()->points);
        $this->assertSame(30, (int) ArcadeSession::wonToday($this->enrollment->id, $this->game->id)->sum('payout'));
    }

    public function test_start_is_rejected_when_daily_cap_is_reached(): void
    {
        $this->game->update(['daily_win_cap' => 10]);
        ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 10,
            'outcome' => 'win',
            'payout' => 10,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.start', [
            'edition' => $this->edition,
            'wager' => 20,
        ]));

        $response->assertStatus(422);
        $this->assertSame(300, $this->enrollment->fresh()->points);
    }

    public function test_submission_requires_the_session_owner(): void
    {
        $otherParticipant = Participant::factory()->create();
        $otherUser = $otherParticipant->user;
        Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $otherParticipant->id,
            'points' => 300,
        ]);

        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $this->actingAs($otherUser)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ])->assertStatus(403);
    }

    public function test_resolved_session_cannot_be_submitted_twice(): void
    {
        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ])->assertOk();

        $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31, 98],
        ])->assertStatus(422);

        $this->assertSame(350, $this->enrollment->fresh()->points);
    }

    public function test_answers_must_match_the_question_count(): void
    {
        $session = $this->sessionWithDeadline(now()->addSeconds(12));

        $this->actingAs($this->user)->postJson(route('arcade.binary-blitz.submit', $session), [
            'edition' => $this->edition,
            'answers' => [11, 31],
        ])->assertStatus(422);
    }

    private function sessionWithDeadline(Carbon $deadline): ArcadeSession
    {
        return ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 20,
            'outcome' => 'pending',
            'server_state' => [
                'questions' => $this->questions(),
                'deadline' => $deadline->toISOString(),
            ],
            'started_at' => now(),
        ]);
    }

    /**
     * @return array<int, array{prompt: string, answer: int}>
     */
    private function questions(): array
    {
        return [
            ['prompt' => '0b1011 em decimal?', 'answer' => 11],
            ['prompt' => '0x1F em decimal?', 'answer' => 31],
            ['prompt' => '14 × 7 = ?', 'answer' => 98],
        ];
    }
}
