<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\FinalPrize;
use App\Models\RaffleDraw;
use App\Services\RaffleEligibilityService;
use App\Services\RaffleWinnerSelector;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RaffleDrawCRUDController extends CRUDController
{
    protected string $model = RaffleDraw::class;

    protected string $view = 'RaffleDraws';

    protected $load = ['winner.participant.user'];

    protected array $rules = [
        'edition_id' => 'required|exists:editions,id',
        'place' => 'required|integer|min:1|max:3',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'draw_at' => 'nullable|date',
    ];

    public function __construct(private readonly RaffleEligibilityService $eligibility) {}

    protected function indexQuery(Builder $query): void
    {
        $query->whereNotNull('place');
    }

    protected function defaultSortBy(): string
    {
        return 'place';
    }

    protected function storeRules(): array
    {
        $rules = $this->rules;
        $rules['place'] = [
            'required',
            'integer',
            'min:1',
            'max:3',
            Rule::unique('raffle_draws', 'place')->where('edition_id', request('edition_id')),
        ];

        return $rules;
    }

    protected function updateRules($old): array
    {
        $rules = $this->rules;
        $rules['place'] = [
            'required',
            'integer',
            'min:1',
            'max:3',
            Rule::unique('raffle_draws', 'place')
                ->where('edition_id', $old->edition_id)
                ->ignore($old->id),
        ];

        return $rules;
    }

    protected function with(): array
    {
        return [
            'editions' => Edition::query()
                ->select('id', 'name', 'year')
                ->orderByDesc('year')
                ->get(),
        ];
    }

    protected function created(array $new): ?array
    {
        $new['required_event_id'] = $this->closingEventOrFail((int) $new['edition_id'])->id;

        return $new;
    }

    protected function updated($old, array $new): ?array
    {
        abort_if(
            (int) $new['edition_id'] !== (int) $old->edition_id,
            422,
            'A edição de um sorteio não pode ser alterada.'
        );

        $new['required_event_id'] = $this->closingEventOrFail((int) $old->edition_id)->id;

        return $new;
    }

    protected function destroyed($old): bool
    {
        return ($old['winner_enrollment_id'] ?? null) === null;
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $deleted = DB::transaction(function () use ($id) {
            $draw = RaffleDraw::lockForUpdate()->findOrFail($id);

            if (! $this->destroyed($draw->toArray())) {
                return false;
            }

            $draw->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()->back()->dangerBanner(
                'Não é possível eliminar um sorteio com vencedor.'
            );
        }

        Log::alert('Raffle draw deleted', ['raffle_draw_id' => $id]);

        return redirect()->back();
    }

    protected function getIndexData(Request $request): array
    {
        $data = parent::getIndexData($request);

        $editionId = $this->editionId($request);
        $closingEvent = $this->eligibility->closingEventForEdition($editionId);

        $drawsInEdition = RaffleDraw::query()
            ->where('edition_id', $editionId)
            ->whereNotNull('place')
            ->orderBy('place')
            ->orderBy('id')
            ->get();

        $requestedDrawId = $request->query('draw_id');
        $selectedDraw = $requestedDrawId
            ? $drawsInEdition->firstWhere('id', (int) $requestedDrawId)
            : null;
        $selectedDraw ??= $drawsInEdition->firstWhere('winner_enrollment_id', null)
            ?? $drawsInEdition->first();

        $eligibleCount = 0;
        $totalEntries = 0;
        $eligibleParticipants = collect();

        if ($closingEvent !== null && $selectedDraw !== null) {
            $alreadyWonEnrollmentIds = RaffleDraw::query()
                ->where('edition_id', $editionId)
                ->where('id', '!=', $selectedDraw->id)
                ->whereNotNull('winner_enrollment_id')
                ->pluck('winner_enrollment_id')
                ->all();

            $eligibleQuery = $this->eligibility->eligibleEnrollments(
                $editionId,
                $closingEvent,
                $alreadyWonEnrollmentIds
            );

            $eligibleCount = (clone $eligibleQuery)->count();
            $totalEntries = (int) (clone $eligibleQuery)->sum('enrollments.points');

            $eligibleParticipants = (clone $eligibleQuery)
                ->with('participant.user:id,name,email,profile_photo_path')
                ->orderByDesc('points')
                ->orderBy('id')
                ->get()
                ->map(fn (Enrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'name' => $this->participantName($enrollment),
                    'email' => $enrollment->participant?->user?->email,
                    'points' => (int) $enrollment->points,
                ]);
        }

        $data['selectedDrawId'] = $selectedDraw?->id;
        $data['selectedDrawName'] = $selectedDraw?->name;
        $data['requiredEventName'] = $closingEvent?->name;
        $data['hasClosingEvent'] = $closingEvent !== null;
        $data['eligibleCount'] = $eligibleCount;
        $data['totalEntries'] = $totalEntries;
        $data['eligibleParticipants'] = $eligibleParticipants;
        $data['activeEditionId'] = $editionId;

        return $data;
    }

    public function pickWinner(RaffleDraw $draw, RaffleWinnerSelector $selector): RedirectResponse
    {
        Gate::authorize('admin');

        [$winnerName, $drawName] = DB::transaction(function () use ($draw, $selector) {
            $editionId = (int) $draw->edition_id;
            Edition::lockForUpdate()->findOrFail($editionId);

            $lockedDraw = RaffleDraw::lockForUpdate()->findOrFail($draw->id);
            $winner = $this->selectWinnerForDraw($lockedDraw, $editionId, $selector);

            $lockedDraw->update([
                'winner_enrollment_id' => $winner->id,
                'is_active' => false,
                'draw_at' => now(),
            ]);

            return [$this->participantName($winner), $lockedDraw->name];
        });

        Log::info('Raffle draw winner selected', [
            'draw' => $drawName,
            'winner' => $winnerName,
            'draw_id' => $draw->id,
        ]);

        return redirect()->back()->banner("Vencedor selecionado: {$winnerName}!");
    }

    /**
     * Draw every pending final prize, from first place to third place.
     */
    public function drawAll(Request $request, RaffleWinnerSelector $selector): RedirectResponse
    {
        Gate::authorize('admin');

        $editionId = $this->editionId($request);

        $results = DB::transaction(function () use ($editionId, $selector) {
            Edition::lockForUpdate()->findOrFail($editionId);

            $draws = RaffleDraw::query()
                ->where('edition_id', $editionId)
                ->whereNotNull('place')
                ->orderBy('place')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            abort_if($draws->isEmpty(), 422, 'Nenhum prémio final configurado para esta edição.');

            if ($draws->whereNull('winner_enrollment_id')->isEmpty()) {
                return null;
            }

            $results = [];

            foreach ($draws as $draw) {
                if ($draw->winner_enrollment_id !== null) {
                    continue;
                }

                abort_if(! $draw->is_active, 422, "O sorteio \"{$draw->name}\" está inativo.");

                $winner = $this->selectWinnerForDraw($draw, $editionId, $selector);

                $draw->update([
                    'winner_enrollment_id' => $winner->id,
                    'is_active' => false,
                    'draw_at' => now(),
                ]);

                $results[] = [
                    'place' => (int) $draw->place,
                    'draw' => $draw->name,
                    'winner' => $this->participantName($winner),
                ];
            }

            return $results;
        });

        if ($results === null) {
            return redirect()->back()->dangerBanner('Todos os prémios finais já foram sorteados.');
        }

        Log::info('Final prize draws executed', [
            'edition_id' => $editionId,
            'results' => $results,
            'admin_id' => $request->user()?->id,
        ]);

        $summary = collect($results)
            ->sortBy('place')
            ->map(fn (array $result) => "{$result['place']}º — {$result['winner']}")
            ->implode('; ');

        return redirect()->back()->banner("Sorteio concluído: {$summary}.");
    }

    /**
     * Reset every final prize in the edition so they can be drawn again.
     */
    public function resetAll(Request $request): RedirectResponse
    {
        Gate::authorize('admin');

        $editionId = $this->editionId($request);

        $count = RaffleDraw::query()
            ->where('edition_id', $editionId)
            ->whereNotNull('place')
            ->update([
                'winner_enrollment_id' => null,
                'is_active' => true,
                'draw_at' => null,
                'updated_at' => now(),
            ]);

        Log::info('Final prize draws reset', [
            'edition_id' => $editionId,
            'draws' => $count,
            'admin_id' => $request->user()?->id,
        ]);

        return redirect()->back()->banner('Sorteio reposto. Todos os prémios podem ser sorteados novamente.');
    }

    public function resetWinner(RaffleDraw $draw): RedirectResponse
    {
        Gate::authorize('admin');

        $draw->update([
            'winner_enrollment_id' => null,
            'is_active' => true,
            'draw_at' => null,
        ]);

        Log::info('Raffle draw winner reset', [
            'draw_id' => $draw->id,
            'admin_id' => request()->user()?->id,
        ]);

        return redirect()->back()->banner('Vencedor anulado. O prémio pode ser sorteado novamente.');
    }

    /**
     * Presentation data for the closing session animation.
     *
     * Already-drawn prizes replay their stored winners. Pending prizes are
     * simulated in memory and never persisted, so the admin can preview the
     * animation before running the official draw.
     */
    public function simulate(Request $request, RaffleWinnerSelector $selector): Response
    {
        Gate::authorize('admin');

        $editionId = $this->editionId($request);
        $closingEvent = $this->eligibility->closingEventForEdition($editionId);

        Edition::query()->findOrFail($editionId);

        $draws = RaffleDraw::query()
            ->where('edition_id', $editionId)
            ->whereNotNull('place')
            ->with(['winner.participant.user:id,name,profile_photo_path'])
            ->orderBy('place')
            ->orderBy('id')
            ->get();

        $finalPrizes = FinalPrize::query()
            ->where('edition_id', $editionId)
            ->get()
            ->keyBy(fn (FinalPrize $prize) => (int) $prize->place);

        $usedEnrollmentIds = [];
        $prizes = [];
        $hasSimulation = false;

        foreach ($draws as $draw) {
            $eligible = $closingEvent !== null
                ? $this->eligibility->eligibleEnrollments($editionId, $closingEvent, $usedEnrollmentIds)
                    ->with('participant.user:id,name,profile_photo_path')
                    ->get()
                : collect();

            $winner = $draw->winner;
            $isSimulated = false;

            if ($winner === null && $eligible->isNotEmpty()) {
                $entries = $eligible
                    ->map(fn (Enrollment $enrollment) => [
                        'enrollment_id' => (int) $enrollment->id,
                        'weight' => (int) $enrollment->points,
                    ])
                    ->values();

                $winnerId = $selector->select($entries);
                $winner = $eligible->firstWhere('id', $winnerId);
                $isSimulated = true;
                $hasSimulation = true;
            }

            if ($winner !== null) {
                $usedEnrollmentIds[] = (int) $winner->id;
            }

            $pool = $eligible
                ->map(fn (Enrollment $enrollment) => [
                    'id' => (int) $enrollment->id,
                    'name' => $this->participantName($enrollment),
                ])
                ->shuffle()
                ->take(80)
                ->values();

            if ($winner !== null && ! $pool->contains('id', (int) $winner->id)) {
                $pool->prepend([
                    'id' => (int) $winner->id,
                    'name' => $this->participantName($winner),
                ]);
            }

            $prizes[] = [
                'id' => $draw->id,
                'place' => $draw->place !== null ? (int) $draw->place : null,
                'name' => $draw->name,
                'description' => $draw->description,
                'image_url' => $draw->place !== null
                    ? $finalPrizes->get((int) $draw->place)?->final_prize_image_url
                    : null,
                'winner' => $winner !== null ? [
                    'id' => (int) $winner->id,
                    'name' => $this->participantName($winner),
                ] : null,
                'is_simulated' => $isSimulated,
                'eligible_count' => $eligible->count(),
                'pool' => $pool,
            ];
        }

        return Inertia::render('CRUD/RaffleDraws/Simulate', [
            'prizes' => $prizes,
            'isSimulation' => $hasSimulation,
        ]);
    }

    private function selectWinnerForDraw(
        RaffleDraw $draw,
        int $editionId,
        RaffleWinnerSelector $selector
    ): Enrollment {
        abort_if($draw->winner_enrollment_id !== null, 422, 'Vencedor já selecionado');
        abort_if(! $draw->is_active, 422, 'Sorteio inativo');

        $closingEvent = $this->closingEventOrFail($editionId);

        $alreadyWonEnrollmentIds = RaffleDraw::query()
            ->where('edition_id', $editionId)
            ->where('id', '!=', $draw->id)
            ->whereNotNull('winner_enrollment_id')
            ->pluck('winner_enrollment_id')
            ->all();

        $entries = $this->eligibility->entriesFor($editionId, $closingEvent, $alreadyWonEnrollmentIds);

        abort_if($entries->isEmpty(), 422, 'Nenhum participante elegível para o sorteio.');

        $winnerId = $selector->select($entries);
        $winner = Enrollment::with('participant.user')->findOrFail($winnerId);

        abort_if((int) $winner->edition_id !== $editionId, 422, 'Vencedor inválido para esta edição.');

        return $winner;
    }

    private function closingEventOrFail(int $editionId): Event
    {
        $closingEvent = $this->eligibility->closingEventForEdition($editionId);

        abort_if(
            $closingEvent === null,
            422,
            'Não foi encontrada uma Sessão de Encerramento para esta edição.'
        );

        return $closingEvent;
    }

    private function editionId(Request $request): int
    {
        $requested = $request->query('edition_id', $request->input('edition_id'));

        if ($requested !== null && $requested !== '' && $requested !== 'all') {
            return (int) $requested;
        }

        $latestEditionId = (int) Edition::query()->orderByDesc('year')->value('id');

        abort_if($latestEditionId === 0, 422, 'Nenhuma edição encontrada.');

        return $latestEditionId;
    }

    private function participantName(Enrollment $enrollment): string
    {
        return $enrollment->participant?->user?->name ?? "Participante #{$enrollment->id}";
    }
}
