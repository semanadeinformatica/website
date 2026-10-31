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

    private function ensureCoinFlipSession(ArcadeSession $session, int $editionId): void
    {
        abort_unless(
            $session->game->slug === 'coin-flip' && $session->game->edition_id === $editionId,
            404,
            'Sessão não encontrada'
        );
    }
}
