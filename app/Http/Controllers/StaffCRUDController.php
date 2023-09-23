<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Edition;
use App\Models\Participant;

class StaffCRUDController extends CRUDController
{
    protected array $rules = [
        'participant_id' => 'required|exists:participants,id',
        'department_id' => 'required|exists:departments,id',
        'coordinator' => 'sometimes|boolean',
    ];

    protected function with(): array
    {
        return [
            'participants' => Participant::with('user')->get(),
            'departments' => Department::all(),
            'editions' => Edition::all(),
        ];
    }
}
