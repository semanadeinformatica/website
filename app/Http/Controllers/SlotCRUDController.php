<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\Slot;
use Illuminate\Support\Facades\DB;

class SlotCRUDController extends CRUDController
{
    protected string $model = Slot::class;

    protected string $view = 'Slot';

    protected array $rules = [
        'total_quests' => 'required|integer',
        'selected_quests' => 'sometimes|array',
    ];

    protected function created(array $new): ?array
    {
        $selectedQuests = $new['selected_quests'];
        unset($new['selected_quests']);

        DB::beginTransaction();
        $slot = Slot::create($new);

        $quests = Quest::find($selectedQuests);

        $slot->quests()->attach($quests);
        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $selectedQuests = $new['selected_quests'];
        unset($new['selected_quests']);

        DB::beginTransaction();
        $quests = Quest::find($selectedQuests);

        $old->quests()->attach($quests);
        DB::commit();

        return $new;
    }

    protected function with(): array
    {
        return [
            'quests' => Quest::all(),
        ];
    }

    protected array $search = ['name'];
}
