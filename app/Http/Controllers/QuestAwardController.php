<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class QuestAwardController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var Edition|null $edition */
        $edition = $request->input('edition');

        /** @var Participant|null $participant */
        $participant = null;
        /** @var Enrollment|null $enrollment */
        $enrollment = null;
        /** @var Collection<int, array<string, mixed>> $quests */
        $quests = collect();

        if ($edition !== null && $request->filled('participant')) {
            $participant = Participant::query()
                ->select('id', 'user_id')
                ->with(['user:id,name,email'])
                ->find($request->integer('participant'));
        }

        if ($participant !== null) {
            $enrollment = $participant->enrollments()
                ->where('edition_id', $edition->id)
                ->first();

            if ($enrollment !== null) {
                /** @var User $user */
                $user = $request->user();

                $quests = $this->questsForEnrollment($edition, $enrollment, $user);
            }
        }

        return Inertia::render('Admin/QuestAward', [
            'edition' => $edition?->only(['id', 'name', 'year']),
            'participant' => $participant === null ? null : [
                'id' => $participant->id,
                'name' => $participant->user?->name,
                'email' => $participant->user?->email,
            ],
            'enrollment' => $enrollment === null ? null : [
                'id' => $enrollment->id,
                'points' => $enrollment->points,
            ],
            'quests' => $quests,
            'selectedParticipantId' => $participant?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'quest_id' => ['required', 'integer', 'exists:quests,id'],
        ], [
            'participant_id.required' => 'Seleciona um participante.',
            'participant_id.exists' => 'O participante selecionado não existe.',
            'quest_id.required' => 'Seleciona uma tarefa.',
            'quest_id.exists' => 'A tarefa selecionada não existe.',
        ]);

        /** @var Edition|null $edition */
        $edition = $request->input('edition');

        $participant = Participant::findOrFail($validated['participant_id']);
        $quest = Quest::findOrFail($validated['quest_id']);

        $redirect = redirect()->route('admin.questAwards.index', [
            'participant' => $participant->id,
        ]);

        if ($edition === null) {
            return $redirect->dangerBanner('Não foi possível identificar a edição atual.');
        }

        if ((int) $quest->edition_id !== (int) $edition->id) {
            return $redirect->dangerBanner('A tarefa selecionada não pertence à edição atual.');
        }

        /** @var User $user */
        $user = $request->user();

        try {
            $response = DB::transaction(function () use ($edition, $participant, $quest, $user, $redirect) {
                // Serialize awards for this enrollment so concurrent awards cannot both
                // miss the slot-unlock threshold in the database trigger.
                $enrollment = $participant->enrollments()
                    ->where('edition_id', $edition->id)
                    ->lockForUpdate()
                    ->first();

                if ($enrollment === null) {
                    return $redirect->dangerBanner("O participante não se inscreveu no {$edition->name}.");
                }

                if ($enrollment->quests()->whereKey($quest->id)->exists()) {
                    return $redirect->dangerBanner(
                        "O participante já realizou a tarefa \"{$quest->name}\"."
                    );
                }

                if ($this->missingRequiredEventEnrollment($quest, $enrollment)) {
                    return $redirect->dangerBanner(
                        $this->missingRequiredEventEnrollmentMessage($quest)
                    );
                }

                // Keep the policy as the final authority in case the checks above drift.
                if ($user->cannot('give', [$quest, $enrollment])) {
                    Log::warning('Current user is not allowed to manually give quest "{quest}" to user {user}', [
                        'quest' => $quest->name,
                        'user' => $participant->user->name,
                    ]);

                    return $redirect->dangerBanner(
                        'Não foi possível atribuir a tarefa ao participante.'
                    );
                }

                $enrollment->quests()->attach($quest);

                return $redirect->banner(
                    "Tarefa \"{$quest->name}\" atribuída a {$participant->user->name}."
                );
            });
        } catch (UniqueConstraintViolationException) {
            // Safety net in case the row lock is bypassed by another writer.
            return $redirect->dangerBanner(
                "O participante já realizou a tarefa \"{$quest->name}\"."
            );
        }

        Log::info('Quest {quest} manually given to user {user} by admin {admin}', [
            'quest' => $quest->name,
            'user' => $participant->user->name,
            'admin' => $user->name,
        ]);

        return $response;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function questsForEnrollment(Edition $edition, Enrollment $enrollment, User $user): Collection
    {
        $completedQuestIds = $enrollment->quests()->pluck('quests.id')->all();
        $joinedEventIds = $enrollment->events()->pluck('events.id')->all();

        return $edition->quests()
            ->with(['requirement' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Event::class => ['type'],
                    Stand::class => ['sponsor.company.user', 'event_day'],
                ]);
            }])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function (Quest $quest) use ($completedQuestIds, $joinedEventIds, $enrollment, $user) {
                $completed = in_array($quest->id, $completedQuestIds, true);
                $missingRequiredEvent = $quest->requirement_type === Event::class
                    && ! in_array($quest->requirement_id, $joinedEventIds, true);
                $available = ! $completed
                    && ! $missingRequiredEvent
                    && $user->can('give', [$quest, $enrollment]);

                return [
                    'id' => $quest->id,
                    'name' => $quest->name,
                    'category' => $quest->category,
                    'requirement_type' => match ($quest->requirement_type) {
                        Event::class => 'event',
                        Stand::class => 'stand',
                        default => 'general',
                    },
                    'requirement_label' => $this->requirementLabel($quest),
                    'completed' => $completed,
                    'available' => $available,
                    'unavailable_reason' => match (true) {
                        $completed => null,
                        $missingRequiredEvent => $this->missingRequiredEventEnrollmentMessage($quest),
                        ! $available => 'Esta tarefa não pode ser atribuída manualmente a este participante.',
                        default => null,
                    },
                ];
            });
    }

    private function requirementLabel(Quest $quest): string
    {
        if ($quest->requirement_type === Event::class) {
            /** @var Event|null $event */
            $event = $quest->requirement;

            return $event === null ? 'Evento' : "Evento: {$event->name}";
        }

        if ($quest->requirement_type === Stand::class) {
            /** @var Stand|null $stand */
            $stand = $quest->requirement;

            if ($stand === null) {
                return 'Banca';
            }

            $company = $stand->sponsor?->company?->user?->name;
            $day = $stand->event_day?->date?->format('d/m');

            if ($company === null) {
                return 'Banca';
            }

            return $day === null ? "Banca: {$company}" : "Banca: {$company} ({$day})";
        }

        return 'Geral';
    }

    private function missingRequiredEventEnrollment(Quest $quest, Enrollment $enrollment): bool
    {
        return $quest->requirement_type === Event::class
            && ! $enrollment->events()->where('event_id', $quest->requirement_id)->exists();
    }

    private function missingRequiredEventEnrollmentMessage(Quest $quest): string
    {
        /** @var Event|null $event */
        $event = $quest->requirement;

        if ($event === null) {
            return 'O participante não está inscrito no evento necessário para esta tarefa.';
        }

        $type = $event->type?->name;

        return $type === null
            ? "O participante não está inscrito no evento \"{$event->name}\"."
            : "O participante não está inscrito no evento \"{$event->name}\" ({$type}).";
    }
}
