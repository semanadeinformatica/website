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

    protected function editionRelationPath(): ?string
    {
        return 'event_day';
    }

    protected array $rules = [
        'name' => 'required|string',
        'time_start' => 'required|date_format:"H:i:s"',
        'time_end' => 'required|date_format:"H:i:s"|after:time_start',
        'description' => 'sometimes|nullable|string',
        'location' => 'sometimes|nullable|string',
        'external_url' => 'sometimes|nullable|string',
        'topic' => 'required|string',
        'enroll_in_site' => 'required|boolean',
        'capacity' => 'nullable|numeric|integer',
        'event_day_id' => 'required|exists:event_days,id',
        'event_type_id' => 'required|exists:event_types,id',
        'users' => 'sometimes|nullable|array|exists:users,id',
    ];

    protected function created(array $new): ?array
    {
        $users = $new['users'];
        unset($new['users']);

        DB::beginTransaction();
        /** @var Event $event */
        $event = Event::create($new);

        $event->users()->sync($users);
        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $users = $new['users'];
        unset($new['users']);

        /** @var Event $old */
        $old->users()->sync($users);

        return $new;
    }

    protected $load = ['users'];

    protected function editLoad(): array
    {
        return [
            'users' => fn ($query) => $query->without('usertype')->select('users.id', 'users.name'),
        ];
    }

    protected function with(): array
    {
        return [
            'event_days' => EventDay::query()->select('id', 'date', 'theme', 'edition_id')->get(),
            'event_types' => EventType::query()->select('id', 'name')->get(),
            'users' => User::query()
                ->select('id', 'name', 'usertype_type')
                ->without('usertype')
                ->whereIn('usertype_type', [Speaker::class, Company::class])
                ->get(),
        ];
    }
}
