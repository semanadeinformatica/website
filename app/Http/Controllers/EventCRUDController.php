<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventCRUDController extends CRUDController
{
    protected string $model = Event::class;

    protected string $view = 'Event';

    protected array $rules = [
        'name' => 'required|string',
        'time_start' => 'required|date_format:"H:i:s"',
        'time_end' => 'required|date_format:"H:i:s"|after:time_start',
        'description' => 'required|string',
        'room' => 'sometimes|nullable|string',
        'topic' => 'required|string',
        'capacity' => 'nullable|numeric|integer',
        'event_day_id' => 'required|exists:event_days,id',
        'event_type_id' => 'required|exists:event_types,id',
        'users' => 'sometimes|nullable|array|exists:users,id',
    ];

    protected array $search = ['name', 'topic', 'capacity', 'room'];

    protected function created(array $new): ?array
    {
        $users = $new['users'];
        unset($new['users']);

        DB::beginTransaction();
        $event = Event::create($new);

        $event->users()->sync($users);
        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $users = $new['users'];
        unset($new['users']);

        $old->users()->sync($users);

        return $new;
    }

    protected function with(): array
    {
        return [
            'event_days' => EventDay::all(),
            'event_types' => EventType::all(),
            'users' => User::whereIn('usertype_type', [Speaker::class, Company::class])->get(),
        ];
    }
}
