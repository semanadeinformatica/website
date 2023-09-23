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
        'quests' => 'sometimes|array|exists:quests,id',
        'points' => 'required|integer',
    ];

    protected function created(array $new): ?array
    {
        $quests = $new['quests'];
        unset($new['quests']);

        DB::beginTransaction();
        $slot = Slot::create($new);

        $slot->quests()->sync($quests);
        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $quests = $new['quests'];
        unset($new['quests']);

        $old->quests()->sync($quests);

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
