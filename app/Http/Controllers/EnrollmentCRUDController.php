<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;

class EnrollmentCRUDController extends CRUDController
{
    protected string $model = Enrollment::class;

    protected string $view = 'Enrollment';

    protected array $rules = [
        'participant_id' => 'required|exists:participants,id',
        'edition_id' => 'required|exists:editions,id',
        'points' => 'required|integer',
        'flagged' => 'sometimes|boolean',
        'flag_reason' => 'nullable|string|max:255',
    ];

    protected function updated($old, array $new): ?array
    {
        if (array_key_exists('flagged', $new)) {
            $flagged = (bool) $new['flagged'];
            unset($new['flagged']);

            $new['flagged_at'] = $flagged ? ($old->flagged_at ?? now()) : null;

            if (! $flagged) {
                $new['flag_reason'] = null;
            }
        }

        return $new;
    }

    protected function with(): array
    {
        return [
            'participants' => Participant::query()
                ->select('id', 'user_id')
                ->without('socialMedia')
                ->with(['user' => fn ($query) => $query->without('usertype')->select('id', 'name')])
                ->get()
                ->each(fn (Participant $participant) => $participant->setAppends([])),
            'editions' => Edition::query()->select('id', 'name')->get(),
        ];
    }
}
