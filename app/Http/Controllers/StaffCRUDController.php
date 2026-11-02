<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Edition;
use App\Models\Participant;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffCRUDController extends CRUDController
{
    protected string $model = Staff::class;

    protected string $view = 'Staff';

    protected $load = ['participant.user:id,name', 'department:id,name,edition_id'];

    protected array $rules = [
        'participant_id' => 'required|exists:participants,id',
        'department_id' => 'required|exists:departments,id',
        'coordinator' => 'sometimes|boolean',
    ];

    protected function with(): array
    {
        return [
            // Only include participants already used by staff records. The
            // add/edit picker loads other participants on demand.
            'participants' => Participant::query()
                ->select('participants.id', 'participants.user_id')
                ->whereIn('participants.id', Staff::query()->select('participant_id')->distinct())
                ->with(['user:id,name'])
                ->get(),
            'departments' => Department::query()->select('id', 'name', 'edition_id')->get(),
            'editions' => Edition::query()->select('id', 'name')->get(),
        ];
    }

    public function participants(Request $request)
    {
        $search = trim((string) $request->query('query', ''));
        $participantId = $request->query('participant_id');

        return Participant::query()
            ->select('participants.id', 'participants.user_id')
            ->join('users', 'users.id', '=', 'participants.user_id')
            ->when($participantId, fn ($query) => $query->where('participants.id', $participantId))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            }))
            ->with(['user:id,name,email'])
            ->orderBy('users.name')
            ->limit(30)
            ->get()
            ->map(fn (Participant $participant) => [
                'id' => $participant->id,
                'user' => ['name' => $participant->user->name, 'email' => $participant->user->email],
            ]);
    }
}
