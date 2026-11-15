<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\FinalPrize;
use Illuminate\Validation\Rule;

class FinalPrizeCRUDController extends CRUDController
{
    protected string $model = FinalPrize::class;

    protected string $view = 'FinalPrizes';

    protected array $rules = [
        'edition_id' => 'required|integer|exists:editions,id',
        'place' => 'required|integer|min:1|max:3',
        'title' => 'required|string|max:255',
        'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:10240',
    ];

    protected function storeRules(): array
    {
        return [
            ...$this->rules,
            'place' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                Rule::unique('final_prizes', 'place')
                    ->where('edition_id', request('edition_id')),
            ],
        ];
    }

    protected function updateRules($old): array
    {
        return [
            ...$this->rules,
            'place' => [
                'required',
                'integer',
                'min:1',
                'max:3',
                Rule::unique('final_prizes', 'place')
                    ->where('edition_id', request('edition_id', $old->edition_id))
                    ->ignore($old->id),
            ],
        ];
    }

    protected function with(): array
    {
        return [
            'editions' => Edition::query()->select('id', 'name')->get(),
        ];
    }

    protected function created(array $new): ?array
    {
        $prize = FinalPrize::create([
            'edition_id' => $new['edition_id'],
            'place' => $new['place'],
            'title' => $new['title'],
        ]);

        if (isset($new['image'])) {
            $prize->updateFinalPrizeImage($new['image']);
        }

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        if (isset($new['image'])) {
            $old->updateFinalPrizeImage($new['image']);
        }

        return [
            'edition_id' => $new['edition_id'],
            'place' => $new['place'],
            'title' => $new['title'],
        ];
    }
}
