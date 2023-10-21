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
        'name' => 'required|string',
        'image' => 'nullable|mimes:jpg,jpeg,png|max:1024',
    ];

    protected function created(array $new): ?array
    {
        $quests = $new['quests'];
        unset($new['quests']);

        DB::beginTransaction();
        $slot = Slot::create($new);

        $slot->quests()->sync($quests);

        if (isset($new['image'])) {
            $slot->updateImageSlot($new['image']);
        }

        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $quests = $new['quests'];
        unset($new['quests']);

        $old->quests()->sync($quests);

        if (isset($new['image'])) {
            $old->updateImageSlot($new['image']);
        }

        return $new;
    }

    protected function with(): array
    {
        return [
            'quests' => Quest::all(),
        ];
    }
}
