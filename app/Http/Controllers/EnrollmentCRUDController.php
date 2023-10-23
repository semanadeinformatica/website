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
    ];

    protected function with(): array
    {
        return [
            'participants' => Participant::with('user')->get(),
            'editions' => Edition::all(),
        ];
    }
}
