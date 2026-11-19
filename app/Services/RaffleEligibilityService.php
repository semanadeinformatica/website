<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Quest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RaffleEligibilityService
{
    /**
     * Enrollments that may enter a final prize draw.
     *
     * A participant must:
     * - belong to the edition;
     * - have points (one point = one entry);
     * - have completed the task selected by the admin for the ending session;
     * - not be a staff member of the edition.
     *
     * Admins cannot have enrollments, but the participant check keeps the
     * query defensive against inconsistent data.
     *
     * @param  array<int, int>  $excludedEnrollmentIds
     */
    public function eligibleEnrollments(
        int $editionId,
        Quest $requiredQuest,
        array $excludedEnrollmentIds = []
    ): Builder {
        return Enrollment::query()
            ->where('enrollments.edition_id', $editionId)
            ->where('enrollments.points', '>', 0)
            ->whereHas('participant.user', fn (Builder $query) => $query->where('usertype_type', Participant::class))
            ->whereHas('quests', fn (Builder $query) => $query->where('quests.id', $requiredQuest->id))
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
        Quest $requiredQuest,
        array $excludedEnrollmentIds = []
    ): Collection {
        return $this->eligibleEnrollments($editionId, $requiredQuest, $excludedEnrollmentIds)
            ->get(['enrollments.id', 'enrollments.points'])
            ->map(fn (Enrollment $enrollment) => [
                'enrollment_id' => (int) $enrollment->id,
                'weight' => (int) $enrollment->points,
            ])
            ->values();
    }
}
