<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Enrollment;
use App\Services\ArcadeWagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function startBugSweeper(Request $request, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate(['wager' => 'required|integer|min:1']);

        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'bug-sweeper')
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
        $bombCount = (int) ($cfg['bomb_count'] ?? 3);
        $positions = range(0, 8);
        shuffle($positions);
        $bombs = array_slice($positions, 0, $bombCount);
        $clickMultipliers = $cfg['click_multipliers'] ?? [1.2, 1.5, 2.0, 3.0, 5.0];

        $session = DB::transaction(function () use ($enrollment, $wager, $game, $bombs, $wagerService) {
            // Lock the enrollment first: concurrent starts for the same
            // participant must not both observe a missing pending session.
            $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);

            // Auto-resolve any previous abandoned pending session
            $previous = ArcadeSession::where('enrollment_id', $lockedEnrollment->id)
                ->where('arcade_game_id', $game->id)
                ->where('outcome', 'pending')
                ->lockForUpdate()
                ->first();

            if ($previous) {
                $prevState = $previous->server_state ?? [];
                $prevClicks = (int) ($prevState['clicks'] ?? 0);

                if ($prevClicks >= 1) {
                    $prevCfg = $game->payout_config ?? [];
                    $prevMultipliers = $prevCfg['click_multipliers'] ?? [1.2, 1.5, 2.0, 3.0, 5.0];
                    $prevMult = (float) $prevMultipliers[min($prevClicks - 1, count($prevMultipliers) - 1)];
                    $prevPayout = (int) floor($previous->wager * $prevMult);
                    $prevPayout = $this->payoutWithinDailyCap($lockedEnrollment, $game, $prevPayout);

                    $wagerService->creditPayout($lockedEnrollment, $prevPayout);
                    $previous->update([
                        'outcome' => 'cashout',
                        'payout' => $prevPayout,
                        'resolved_at' => now(),
                    ]);
                } else {
                    $previous->update([
                        'outcome' => 'loss',
                        'payout' => 0,
                        'resolved_at' => now(),
                    ]);
                }
            }

            $wagerService->deductWager($lockedEnrollment->id, $wager);

            return [
                'session' => ArcadeSession::create([
                    'enrollment_id' => $lockedEnrollment->id,
                    'arcade_game_id' => $game->id,
                    'wager' => $wager,
                    'outcome' => 'pending',
                    'server_state' => [
                        'bombs' => $bombs,
                        'clicks' => 0,
                        'clicked' => [],
                    ],
                    'started_at' => now(),
                ]),
                'balance' => $lockedEnrollment->fresh()->points,
            ];
        });

        return response()->json([
            'session_id' => $session['session']->id,
            'grid_size' => 9,
            'bomb_count' => $bombCount,
            'click_multipliers' => $clickMultipliers,
            'balance' => $session['balance'],
        ], 201);
    }

    public function clickBugSweeper(Request $request, ArcadeSession $session, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate(['cell_index' => 'required|integer|min:0|max:8']);

        $edition = $request->input('edition') ?? $session->game->edition;
        $this->ensureBugSweeperSession($session, $edition->id);

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(! $enrollment || $session->enrollment_id !== $enrollment->id, 403, 'Acesso não autorizado');

        $cellIndex = $request->integer('cell_index');

        $result = DB::transaction(function () use ($session, $cellIndex, $wagerService) {
            // Lock the enrollment before the session so payouts are serialized
            // with starts/cashouts and cannot race past the daily win cap.
            $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($session->enrollment_id);
            $locked = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($locked->outcome !== 'pending', 422, 'Sessão já resolvida');

            $state = $locked->server_state ?? [];
            $clicked = $state['clicked'] ?? [];
            abort_if(in_array($cellIndex, $clicked, true), 422, 'Célula já revelada');

            $bombs = $state['bombs'] ?? [];
            $isBomb = in_array($cellIndex, $bombs, true);

            if ($isBomb) {
                $locked->update([
                    'outcome' => 'loss',
                    'payout' => 0,
                    'resolved_at' => now(),
                ]);

                return [
                    'is_bomb' => true,
                    'bomb_positions' => $bombs,
                    'outcome' => 'loss',
                ];
            }

            // Safe cell click
            $clicks = ($state['clicks'] ?? 0) + 1;
            $clicked[] = $cellIndex;
            $state['clicks'] = $clicks;
            $state['clicked'] = $clicked;

            $cfg = $locked->game->payout_config ?? [];
            $multipliers = $cfg['click_multipliers'] ?? [1.2, 1.5, 2.0, 3.0, 5.0];
            $currentMultiplier = (float) $multipliers[min($clicks - 1, count($multipliers) - 1)];

            $totalCells = 9;
            $maxSafeClicks = $totalCells - count($bombs);
            $clearedBoard = $clicks >= $maxSafeClicks || $clicks >= count($multipliers);

            if ($clearedBoard) {
                $payout = (int) floor($locked->wager * $currentMultiplier);
                $payout = $this->payoutWithinDailyCap($lockedEnrollment, $locked->game, $payout);
                $wagerService->creditPayout($lockedEnrollment, $payout);

                $locked->update([
                    'outcome' => 'cashout',
                    'payout' => $payout,
                    'server_state' => $state,
                    'resolved_at' => now(),
                ]);

                return [
                    'is_bomb' => false,
                    'current_multiplier' => $currentMultiplier,
                    'clicks' => $clicks,
                    'can_cashout' => false,
                    'payout' => $payout,
                    'bomb_positions' => $bombs,
                    'outcome' => 'cashout',
                ];
            }

            $locked->update(['server_state' => $state]);

            return [
                'is_bomb' => false,
                'current_multiplier' => $currentMultiplier,
                'clicks' => $clicks,
                'can_cashout' => true,
            ];
        });

        return response()->json($result);
    }

    public function cashoutBugSweeper(Request $request, ArcadeSession $session, ArcadeWagerService $wagerService): JsonResponse
    {
        $edition = $request->input('edition') ?? $session->game->edition;
        $this->ensureBugSweeperSession($session, $edition->id);

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(! $enrollment || $session->enrollment_id !== $enrollment->id, 403, 'Acesso não autorizado');

        $result = DB::transaction(function () use ($session, $wagerService) {
            $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($session->enrollment_id);
            $locked = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($locked->outcome !== 'pending', 422, 'Sessão já resolvida');

            $state = $locked->server_state ?? [];
            $clicks = (int) ($state['clicks'] ?? 0);
            abort_if($clicks < 1, 422, 'É necessário pelo menos 1 clique seguro para recolher os pontos');

            $cfg = $locked->game->payout_config ?? [];
            $multipliers = $cfg['click_multipliers'] ?? [1.2, 1.5, 2.0, 3.0, 5.0];
            $currentMultiplier = (float) $multipliers[min($clicks - 1, count($multipliers) - 1)];

            $payout = (int) floor($locked->wager * $currentMultiplier);
            $payout = $this->payoutWithinDailyCap($lockedEnrollment, $locked->game, $payout);

            $wagerService->creditPayout($lockedEnrollment, $payout);

            $locked->update([
                'outcome' => 'cashout',
                'payout' => $payout,
                'resolved_at' => now(),
            ]);

            return [
                'payout' => $payout,
                'bomb_positions' => $state['bombs'] ?? [],
                'outcome' => 'cashout',
            ];
        });

        return response()->json($result);
    }

    private function payoutWithinDailyCap(Enrollment $enrollment, ArcadeGame $game, int $payout): int
    {
        if ($game->daily_win_cap === null || $payout <= 0) {
            return $payout;
        }

        $wonToday = (int) ArcadeSession::wonToday($enrollment->id, $game->id)->sum('payout');

        return min($payout, max(0, $game->daily_win_cap - $wonToday));
    }

    private function ensureBugSweeperSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'bug-sweeper' && $session->game->edition_id === $editionId,
            404,
            'Sessão não encontrada'
        );
    }

    public function startMastermind(Request $request, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate(['wager' => 'required|integer|min:1']);

        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'mastermind')
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

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $cfg = $game->payout_config;
        $maxAttempts = (int) ($cfg['max_attempts'] ?? 5);

        $sessionData = DB::transaction(function () use ($enrollment, $wager, $game, $code, $wagerService) {
            $lockedEnrollment = $wagerService->deductWager($enrollment->id, $wager);

            $session = ArcadeSession::create([
                'enrollment_id' => $enrollment->id,
                'arcade_game_id' => $game->id,
                'wager' => $wager,
                'outcome' => 'pending',
                'server_state' => [
                    'code' => $code,
                    'attempts' => [],
                ],
                'started_at' => now(),
            ]);

            return [
                'session_id' => $session->id,
                'points' => $lockedEnrollment->points,
            ];
        });

        return response()->json([
            'session_id' => $sessionData['session_id'],
            'max_attempts' => $maxAttempts,
            'points' => $sessionData['points'],
            'balance' => $sessionData['points'],
        ], 201);
    }

    public function guessMastermind(Request $request, ArcadeSession $session, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate([
            'guess' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        abort_unless($session->game->slug === 'mastermind', 404, 'Sessão não encontrada');

        $edition = $request->input('edition') ?? $session->game->edition;
        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        abort_if(! $enrollment || $session->enrollment_id !== $enrollment->id, 403, 'Acesso não autorizado');

        $guessStr = (string) $request->string('guess')->value();

        $result = DB::transaction(function () use ($session, $enrollment, $wagerService, $guessStr) {
            $lockedSession = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($lockedSession->outcome !== 'pending', 422, 'Sessão já processada');

            $cfg = $lockedSession->game->payout_config;
            $maxAttempts = (int) ($cfg['max_attempts'] ?? 5);
            $serverState = $lockedSession->server_state ?? [];
            $attempts = $serverState['attempts'] ?? [];
            abort_if(count($attempts) >= $maxAttempts, 422, 'Número máximo de tentativas atingido');

            $codeStr = (string) ($serverState['code'] ?? '');
            $code = str_split($codeStr);
            $guess = str_split($guessStr);
            $exact = 0;
            $misplaced = 0;
            $feedback = array_fill(0, 4, 'wrong');
            $codeRemain = [];

            for ($i = 0; $i < 4; $i++) {
                if ($code[$i] === $guess[$i]) {
                    $feedback[$i] = 'exact';
                    $exact++;
                } else {
                    $codeRemain[] = $code[$i];
                }
            }

            for ($i = 0; $i < 4; $i++) {
                if ($feedback[$i] === 'exact') {
                    continue;
                }
                $idx = array_search($guess[$i], $codeRemain);
                if ($idx !== false) {
                    $feedback[$i] = 'misplaced';
                    $misplaced++;
                    array_splice($codeRemain, $idx, 1);
                }
            }

            $attempts[] = [
                'guess' => $guessStr,
                'exact' => $exact,
                'misplaced' => $misplaced,
                'feedback' => $feedback,
            ];
            $serverState['attempts'] = $attempts;

            $attemptsUsed = count($attempts);
            $isWin = ($exact === 4);
            $isGameOver = $isWin || ($attemptsUsed >= $maxAttempts);

            $outcome = 'pending';
            $payout = 0;
            $multiplier = 0.0;
            $balance = $enrollment->points;

            if ($isWin) {
                $outcome = 'win';
                $multipliers = $cfg['attempt_multipliers'] ?? [];
                $multiplier = (float) ($multipliers[(string) $attemptsUsed] ?? 1.0);
                $payout = (int) floor($lockedSession->wager * $multiplier);

                $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);
                $wonToday = (int) ArcadeSession::wonToday($lockedEnrollment->id, $lockedSession->game->id)->sum('payout');
                $dailyWinCap = $lockedSession->game->daily_win_cap;

                if ($dailyWinCap !== null) {
                    $remainingAllowance = max(0, $dailyWinCap - $wonToday);
                    $payout = min($payout, $remainingAllowance);
                }

                $wagerService->creditPayout($lockedEnrollment, $payout);
                $balance = $lockedEnrollment->fresh()->points;

                $lockedSession->update([
                    'outcome' => 'win',
                    'payout' => $payout,
                    'server_state' => $serverState,
                    'resolved_at' => now(),
                ]);
            } elseif ($isGameOver) {
                $outcome = 'loss';
                $lockedSession->update([
                    'outcome' => 'loss',
                    'payout' => 0,
                    'server_state' => $serverState,
                    'resolved_at' => now(),
                ]);
            } else {
                $lockedSession->update([
                    'server_state' => $serverState,
                ]);
            }

            $response = [
                'exact' => $exact,
                'misplaced' => $misplaced,
                'feedback' => $feedback,
                'attempts_used' => $attemptsUsed,
                'max_attempts' => $maxAttempts,
                'outcome' => $outcome,
                'points' => $balance,
                'balance' => $balance,
            ];

            if ($outcome === 'win') {
                $response['payout'] = $payout;
                $response['multiplier'] = $multiplier;
                $response['code'] = $codeStr;
            } elseif ($outcome === 'loss') {
                $response['code'] = $codeStr;
            }

            return $response;
        });

        return response()->json($result);
    }

    private function ensureCoinFlipSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'coin-flip' && $session->game->edition_id === $editionId,
            404,
            'Sessão não encontrada'
        );
    }

    public function resolveHighLow(Request $request, ArcadeWagerService $wagerService): JsonResponse
    {
        $request->validate([
            'wager' => 'required|integer|min:1',
            'bet_type' => 'required|in:under,over,exact',
        ]);

        $edition = $request->input('edition');
        abort_if(! $edition, 404, 'Edição não encontrada');

        $game = ArcadeGame::where('slug', 'high-low')
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        Gate::authorize('play', $game);

        $user = $request->user();
        $enrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->firstOrFail();

        $wager = $request->integer('wager');
        $betType = (string) $request->input('bet_type');
        $cfg = $game->payout_config ?? [];

        abort_if($wager < $game->min_wager || $wager > $game->max_wager, 422, 'Aposta fora dos limites');

        $underThreshold = (int) ($cfg['under_threshold'] ?? 48);
        $overThreshold = (int) ($cfg['over_threshold'] ?? 52);
        $exactNumber = (int) ($cfg['exact_number'] ?? 50);

        $underMultiplier = (float) ($cfg['under_multiplier'] ?? 2.0);
        $overMultiplier = (float) ($cfg['over_multiplier'] ?? 2.0);
        $exactMultiplier = (float) ($cfg['exact_multiplier'] ?? 20.0);

        $result = random_int(1, 100);

        $won = match ($betType) {
            'under' => $result < $underThreshold,
            'over' => $result > $overThreshold,
            'exact' => $result === $exactNumber,
        };

        $multiplier = match ($betType) {
            'under' => $underMultiplier,
            'over' => $overMultiplier,
            'exact' => $exactMultiplier,
        };

        $potentialPayout = (int) floor($wager * $multiplier);
        $payout = $won ? $potentialPayout : 0;

        DB::transaction(function () use ($enrollment, $wager, $payout, $potentialPayout, $game, $result, $betType, $won, $wagerService) {
            $lockedEnrollment = $wagerService->deductWager($enrollment->id, $wager);
            if ($potentialPayout > 0) {
                $wagerService->checkDailyWinCap($lockedEnrollment->id, $game, $potentialPayout);
            }
            if ($won && $payout > 0) {
                $wagerService->creditPayout($lockedEnrollment, $payout);
            }
            ArcadeSession::create([
                'enrollment_id' => $enrollment->id,
                'arcade_game_id' => $game->id,
                'wager' => $wager,
                'outcome' => $won ? 'win' : 'loss',
                'payout' => $payout,
                'server_state' => ['rolled' => $result, 'bet_type' => $betType],
                'started_at' => now(),
                'resolved_at' => now(),
            ]);
        });

        return response()->json([
            'result' => $result,
            'won' => $won,
            'payout' => $payout,
            'multiplier' => $multiplier,
            'balance' => $enrollment->fresh()->points,
        ]);
    }
}
