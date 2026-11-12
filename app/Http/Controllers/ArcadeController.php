<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
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

            $wagerService->deductWager($challengerEnrollment->id, $lockedSession->wager);

            // 0 = creator wins, 1 = challenger wins
            $result = random_int(0, 1);
            $houseEdgePct = (int) ($game->payout_config['house_edge_pct'] ?? 10);
            $totalPot = $lockedSession->wager * 2;
            $payout = intdiv($totalPot * (100 - $houseEdgePct), 100);

            $creatorEnrollment = $lockedSession->enrollment;
            $winner = $result === 0 ? 'creator' : 'challenger';
            $winningEnrollment = $result === 0 ? $creatorEnrollment : $challengerEnrollment;

            $wagerService->creditPayout($winningEnrollment, $payout);

            $lockedSession->update([
                'challenger_enrollment_id' => $challengerEnrollment->id,
                'outcome' => $result === 0 ? 'win' : 'loss',
                'payout' => $payout,
                'resolved_at' => now(),
            ]);

            return [
                'winner' => $winner,
                'payout' => $payout,
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

    public function cancelCoinFlip(Request $request, ArcadeSession $session): JsonResponse
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

        DB::transaction(function () use ($session, $enrollment) {
            $lockedSession = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($lockedSession->outcome !== 'pending', 422, 'Sessão já processada');

            $enrollment->increment('points', $lockedSession->wager);
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

        $user = $request->user();
        $enrollment = $user?->isParticipant()
            ? $user->usertype->enrollments()->where('edition_id', $edition->id)->first()
            : null;

        $query = ArcadeSession::pending()
            ->where('arcade_game_id', $game->id)
            ->whereNull('challenger_enrollment_id');

        if ($enrollment) {
            $query->where('enrollment_id', '!=', $enrollment->id);
        }

        return response()->json($query->latest()->paginate(10));
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
            // Auto-resolve any previous abandoned pending session
            $previous = ArcadeSession::where('enrollment_id', $enrollment->id)
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

                    $wagerService->creditPayout($enrollment, $prevPayout);
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

            $wagerService->deductWager($enrollment->id, $wager);

            return ArcadeSession::create([
                'enrollment_id' => $enrollment->id,
                'arcade_game_id' => $game->id,
                'wager' => $wager,
                'outcome' => 'pending',
                'server_state' => [
                    'bombs' => $bombs,
                    'clicks' => 0,
                    'clicked' => [],
                ],
                'started_at' => now(),
            ]);
        });

        return response()->json([
            'session_id' => $session->id,
            'grid_size' => 9,
            'bomb_count' => $bombCount,
            'click_multipliers' => $clickMultipliers,
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
                $wagerService->creditPayout($locked->enrollment, $payout);

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
            $locked = ArcadeSession::lockForUpdate()->findOrFail($session->id);
            abort_if($locked->outcome !== 'pending', 422, 'Sessão já resolvida');

            $state = $locked->server_state ?? [];
            $clicks = (int) ($state['clicks'] ?? 0);
            abort_if($clicks < 1, 422, 'É necessário pelo menos 1 clique seguro para recolher os pontos');

            $cfg = $locked->game->payout_config ?? [];
            $multipliers = $cfg['click_multipliers'] ?? [1.2, 1.5, 2.0, 3.0, 5.0];
            $currentMultiplier = (float) $multipliers[min($clicks - 1, count($multipliers) - 1)];

            $payout = (int) floor($locked->wager * $currentMultiplier);

            $wagerService->checkDailyWinCap($locked->enrollment_id, $locked->game);
            $wagerService->creditPayout($locked->enrollment, $payout);

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

    private function ensureBugSweeperSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'bug-sweeper' && $session->game->edition_id === $editionId,
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
