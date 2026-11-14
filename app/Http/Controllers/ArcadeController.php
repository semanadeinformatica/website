<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Enrollment;
use App\Services\ArcadeWagerService;
use App\Services\BinaryBlitzQuestionGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ArcadeController extends Controller
{
    public function lobby(Request $request): Response
    {
        $edition = $request->input('edition');
        $games = $edition
            ? ArcadeGame::where('edition_id', $edition->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        $enrollment = null;
        $user = $request->user();
        if ($user?->isParticipant() && $edition) {
            $enrollment = $user->usertype->enrollments()
                ->where('edition_id', $edition->id)->first();
        }

        return Inertia::render('Arcade', [
            'games' => $games,
            'points' => $enrollment?->points,
            'isEnrolled' => $enrollment !== null,
            'isParticipant' => $user?->isParticipant() ?? false,
        ]);
    }

    public function startCoinFlip(Request $request, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate(['wager' => 'required|integer|min:1']);

        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'coin-flip')
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        Gate::authorize('play', $game);

        $user = $request->user();
        $enrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        $wager = $request->integer('wager');
        abort_if($wager < $game->min_wager || $wager > $game->max_wager, 422, 'Aposta fora dos limites');

        $session = DB::transaction(function () use ($enrollment, $wager, $game, $wagerService) {
            $wagerService->deductWager($enrollment->id, $wager);

            return ArcadeSession::create([
                'enrollment_id' => $enrollment->id,
                'arcade_game_id' => $game->id,
                'wager' => $wager,
                'outcome' => 'pending',
                'started_at' => now(),
            ]);
        });

        return response()->json(['session_id' => $session->id], 201);
    }

    public function joinCoinFlip(Request $request, ArcadeSession $session, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate(['wager' => 'required|integer|min:1']);

        $game = $session->game;
        $edition = $request->input('edition') ?? $game->edition;

        $this->ensureCoinFlipSession($session, $edition->id);
        Gate::authorize('play', $game);

        $user = $request->user();
        $challengerEnrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        abort_if($challengerEnrollment->id === $session->enrollment_id, 422, 'Não podes jogar contra ti mesmo');
        abort_if($session->outcome !== 'pending' || $session->challenger_enrollment_id !== null, 422, 'Sessão indisponível');
        abort_if($request->integer('wager') !== (int) $session->wager, 422, 'Valor da aposta deve coincidir com o da sessão');

        $resultData = DB::transaction(function () use ($session, $challengerEnrollment, $game, $wagerService) {
            $lockedSession = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($lockedSession->outcome !== 'pending' || $lockedSession->challenger_enrollment_id !== null, 422, 'Sessão já ocupada');

            $enrollmentIds = [$lockedSession->enrollment_id, $challengerEnrollment->id];
            sort($enrollmentIds);

            $lockedEnrollments = Enrollment::query()
                ->whereIn('id', $enrollmentIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $creatorEnrollment = $lockedEnrollments->get($lockedSession->enrollment_id);
            $challengerLockedEnrollment = $lockedEnrollments->get($challengerEnrollment->id);

            abort_if($creatorEnrollment === null || $challengerLockedEnrollment === null, 422, 'Enrollment não encontrado');

            $wagerService->deductWager($challengerEnrollment->id, $lockedSession->wager);

            // 0 = creator wins, 1 = challenger wins
            $result = random_int(0, 1);
            $houseEdgePct = (int) ($game->payout_config['house_edge_pct'] ?? 10);
            $totalPot = $lockedSession->wager * 2;
            $payout = intdiv($totalPot * (100 - $houseEdgePct), 100);

            $creatorWon = ($result === 0);
            $winner = $creatorWon ? 'creator' : 'challenger';
            $winningEnrollment = $creatorWon ? $creatorEnrollment : $challengerLockedEnrollment;

            $wagerService->creditPayout($winningEnrollment, $payout);

            $lockedSession->update([
                'challenger_enrollment_id' => $challengerLockedEnrollment->id,
                'outcome' => $creatorWon ? 'win' : 'loss',
                'payout' => $payout,
                'resolved_at' => now(),
            ]);

            return [
                'winner' => $winner,
                'outcome' => $creatorWon ? 'loss' : 'win',
                'payout' => $payout,
                'player_payout' => $creatorWon ? 0 : $payout,
                'resolved_at' => $lockedSession->resolved_at,
            ];
        });

        return response()->json($resultData);
    }

    public function statusCoinFlip(Request $request, ArcadeSession $session): JsonResponse
    {
        $edition = $request->input('edition') ?? $session->game->edition;
        $this->ensureCoinFlipSession($session, $edition->id);

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(
            ! $enrollment || ($session->enrollment_id !== $enrollment->id && $session->challenger_enrollment_id !== $enrollment->id),
            403,
            'Acesso não autorizado'
        );

        $isChallenger = $session->challenger_enrollment_id === $enrollment->id;
        $outcome = $session->outcome;
        $payout = $session->payout;

        if (in_array($outcome, ['win', 'loss'], true)) {
            $won = $isChallenger ? $outcome === 'loss' : $outcome === 'win';
            $outcome = $won ? 'win' : 'loss';
            $payout = $won ? $session->payout : 0;
        }

        return response()->json([
            'outcome' => $outcome,
            'payout' => $payout,
            'challenger_enrollment_id' => $session->challenger_enrollment_id,
            'resolved_at' => $session->resolved_at,
        ]);
    }

    public function cancelCoinFlip(Request $request, ArcadeSession $session, ArcadeWagerService $wagerService): JsonResponse
    {
        $edition = $request->input('edition') ?? $session->game->edition;
        $this->ensureCoinFlipSession($session, $edition->id);

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(! $enrollment || $session->enrollment_id !== $enrollment->id, 403, 'Acesso não autorizado');
        abort_if($session->outcome !== 'pending', 422, 'Sessão não pode ser cancelada');
        abort_if($session->started_at > now()->subMinutes(5), 422, 'Aguarde 5 minutos antes de cancelar');

        DB::transaction(function () use ($session, $wagerService) {
            $lockedSession = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($lockedSession->outcome !== 'pending' || $lockedSession->challenger_enrollment_id !== null, 422, 'Sessão já processada');

            $wagerService->refundWager($lockedSession->enrollment_id, $lockedSession->wager);
            $lockedSession->update([
                'outcome' => 'expired',
                'resolved_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Sessão cancelada com sucesso']);
    }

    public function pendingCoinFlips(Request $request): JsonResponse
    {
        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'coin-flip')
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        abort_unless($game->is_active, 422, 'Jogo inativo');

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        $query = ArcadeSession::pending()
            ->where('arcade_game_id', $game->id)
            ->whereNull('challenger_enrollment_id')
            ->with(['enrollment.participant.user:id,name,profile_photo_path']);

        if ($enrollment) {
            $query->where('enrollment_id', '!=', $enrollment->id);
        }

        $sessions = $query->latest()->paginate(10)->through(function (ArcadeSession $session) {
            $creator = $session->enrollment->participant?->user;

            return [
                'id' => $session->id,
                'enrollment_id' => $session->enrollment_id,
                'arcade_game_id' => $session->arcade_game_id,
                'wager' => $session->wager,
                'outcome' => $session->outcome,
                'started_at' => $session->started_at,
                'resolved_at' => $session->resolved_at,
                'created_at' => $session->created_at,
                'updated_at' => $session->updated_at,
                'enrollment' => [
                    'id' => $session->enrollment_id,
                    'participant' => [
                        'user' => $creator ? [
                            'id' => $creator->id,
                            'name' => $creator->name,
                            'profile_photo_url' => $creator->profile_photo_url,
                        ] : null,
                    ],
                ],
            ];
        });

        return response()->json($sessions);
    }

    public function startBinaryBlitz(
        Request $request,
        ArcadeWagerService $wagerService,
        BinaryBlitzQuestionGenerator $generator
    ): JsonResponse {
        $request->validate(['wager' => 'required|integer|min:1']);

        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'binary-blitz')
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        Gate::authorize('play', $game);

        $user = $request->user();
        $enrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        $wager = $request->integer('wager');
        abort_if($wager < $game->min_wager || $wager > $game->max_wager, 422, 'Aposta fora dos limites');

        $wagerService->checkDailyWinCap($enrollment->id, $game);

        $cfg = $game->payout_config ?? [];
        $questionCount = max(1, min(10, (int) ($cfg['question_count'] ?? 3)));
        $timeLimit = max(1, min(120, (int) ($cfg['time_limit_seconds'] ?? 12)));
        $questions = $generator->generate($questionCount);
        $deadline = now()->addSeconds($timeLimit);

        $sessionData = DB::transaction(function () use ($enrollment, $wager, $game, $questions, $deadline, $wagerService) {
            $lockedEnrollment = $wagerService->deductWager($enrollment->id, $wager);

            $session = ArcadeSession::create([
                'enrollment_id' => $enrollment->id,
                'arcade_game_id' => $game->id,
                'wager' => $wager,
                'outcome' => 'pending',
                'server_state' => [
                    'questions' => $questions,
                    'deadline' => $deadline->toISOString(),
                ],
                'started_at' => now(),
            ]);

            return [
                'session_id' => $session->id,
                'points' => $lockedEnrollment->points,
            ];
        });

        $prompts = collect($questions)->map(fn (array $question) => [
            'prompt' => $question['prompt'],
        ])->all();

        return response()->json([
            'session_id' => $sessionData['session_id'],
            'prompts' => $prompts,
            'deadline' => $deadline->toISOString(),
            'points' => $sessionData['points'],
            'balance' => $sessionData['points'],
        ], 201);
    }

    public function submitBinaryBlitz(
        Request $request,
        ArcadeSession $session,
        ArcadeWagerService $wagerService
    ): JsonResponse {
        $edition = $request->input('edition') ?? $session->game->edition;
        $this->ensureBinaryBlitzSession($session, $edition->id);

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(! $enrollment || $session->enrollment_id !== $enrollment->id, 403, 'Acesso não autorizado');

        $state = $session->server_state ?? [];
        $deadline = isset($state['deadline']) ? Carbon::parse($state['deadline']) : null;

        if ($deadline === null || now()->gt($deadline)) {
            DB::transaction(function () use ($session) {
                $lockedSession = ArcadeSession::lockForUpdate()->find($session->id);

                if ($lockedSession && $lockedSession->outcome === 'pending') {
                    $lockedSession->update([
                        'outcome' => 'expired',
                        'resolved_at' => now(),
                    ]);
                }
            });

            abort(422, 'Tempo esgotado');
        }

        abort_if($session->outcome !== 'pending', 422, 'Sessão já finalizada');

        $questions = $state['questions'] ?? [];
        $total = count($questions);
        abort_if($total === 0, 422, 'Sessão inválida');

        $request->validate([
            'answers' => ['required', 'array', 'size:'.$total],
            'answers.*' => ['required', 'integer'],
        ]);

        $answers = array_map('intval', $request->input('answers'));
        $correct = 0;
        $feedback = [];

        foreach ($questions as $index => $question) {
            $isCorrect = (int) $question['answer'] === $answers[$index];
            $feedback[] = $isCorrect;

            if ($isCorrect) {
                $correct++;
            }
        }

        $cfg = $session->game->payout_config ?? [];
        $winMultiplier = (float) ($cfg['win_multiplier'] ?? 2.5);
        $partialMultiplier = (float) ($cfg['partial_multiplier'] ?? 1.0);
        $allCorrect = $correct === $total;
        $partial = ! $allCorrect && ($correct * 3) >= ($total * 2);

        $payout = $allCorrect
            ? (int) floor($session->wager * $winMultiplier)
            : ($partial ? (int) floor($session->wager * $partialMultiplier) : 0);
        $outcome = $allCorrect ? 'win' : ($partial ? 'partial' : 'loss');

        $result = DB::transaction(function () use ($session, $enrollment, $correct, $total, $feedback, $allCorrect, $outcome, $payout, $wagerService) {
            $lockedSession = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($lockedSession->outcome !== 'pending', 422, 'Sessão já finalizada');

            $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);

            if ($allCorrect && $lockedSession->game->daily_win_cap !== null) {
                $wonToday = (int) ArcadeSession::wonToday($lockedEnrollment->id, $lockedSession->game->id)->sum('payout');
                $payout = min($payout, max(0, $lockedSession->game->daily_win_cap - $wonToday));
            }

            if ($payout > 0) {
                $wagerService->creditPayout($lockedEnrollment, $payout);
            }

            $lockedSession->update([
                'outcome' => $outcome,
                'payout' => $payout,
                'resolved_at' => now(),
            ]);

            return [
                'correct' => $correct,
                'total' => $total,
                'payout' => $payout,
                'outcome' => $outcome,
                'feedback' => $feedback,
                'balance' => $lockedEnrollment->fresh()->points,
            ];
        });

        return response()->json($result);
    }

    private function ensureBinaryBlitzSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'binary-blitz' && $session->game->edition_id === $editionId,
            404,
            'Sessão não encontrada'
        );
    }

    private function ensureCoinFlipSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'coin-flip' && $session->game->edition_id === $editionId,
            404,
            'Sessão não encontrada'
        );
    }
}
