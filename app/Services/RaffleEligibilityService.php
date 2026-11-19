<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RaffleEligibilityService
{
    /**
     * Find the edition's closing session.
     *
     * The final prize draw is only open to participants who checked in at the
     * closing session, so this event is the source of truth for attendance.
     */
    public function closingEventForEdition(int $editionId): ?Event
    {
        return Event::query()
            ->whereHas('event_day', fn (Builder $query) => $query->where('edition_id', $editionId))
            ->where(function (Builder $query) {
                $query->whereRaw('LOWER(events.name) LIKE ?', ['%encerramento%'])
                    ->orWhereRaw("LOWER(COALESCE(events.topic, '')) LIKE ?", ['%encerramento%']);
            })
            ->orderByRaw("CASE WHEN LOWER(COALESCE(events.topic, '')) = 'encerramento' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();
    }

    /**
     * Enrollments that may enter a final prize draw.
     *
     * A participant must:
     * - belong to the edition;
     * - have points (one point = one entry);
     * - have attended the closing session (recorded through its quest);
     * - not be a staff member of the edition.
     *
     * Admins cannot have enrollments, but the participant check keeps the
     * query defensive against inconsistent data.
     *
     * @param  array<int, int>  $excludedEnrollmentIds
     */
    public function eligibleEnrollments(
        int $editionId,
        Event $closingEvent,
        array $excludedEnrollmentIds = []
    ): Builder {
        return Enrollment::query()
            ->where('enrollments.edition_id', $editionId)
            ->where('enrollments.points', '>', 0)
            ->whereHas('participant.user', fn (Builder $query) => $query->where('usertype_type', Participant::class))
            ->whereHas('quests', function (Builder $query) use ($editionId, $closingEvent) {
                $query->where('quests.edition_id', $editionId)
                    ->where('quests.requirement_type', Event::class)
                    ->where('quests.requirement_id', $closingEvent->id);
            })
            ->whereDoesntHave('participant.staff', function (Builder $query) use ($editionId) {
                $query->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('edition_id', $editionId));
            })
            ->when($excludedEnrollmentIds !== [], fn (Builder $query) => $query->whereNotIn('enrollments.id', $excludedEnrollmentIds))
            ->orderByDesc('enrollments.points')
            ->orderBy('enrollments.id');
    }

    /**
     * Build the weighted entries used by the winner selector.
     *
     * @param  array<int, int>  $excludedEnrollmentIds
     * @return Collection<int, array{enrollment_id: int, weight: int}>
     */
    public function entriesFor(
        int $editionId,
        Event $closingEvent,
        array $excludedEnrollmentIds = []
    ): Collection {
        return $this->eligibleEnrollments($editionId, $closingEvent, $excludedEnrollmentIds)
            ->get(['enrollments.id', 'enrollments.points'])
            ->map(fn (Enrollment $enrollment) => [
                'enrollment_id' => (int) $enrollment->id,
                'weight' => (int) $enrollment->points,
            ])
            ->values();
    }
}
