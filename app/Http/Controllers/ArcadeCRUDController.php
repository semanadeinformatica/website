<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\Edition;
use Illuminate\Validation\Rule;

class ArcadeCRUDController extends CRUDController
{
    protected string $model = ArcadeGame::class;

    protected string $view = 'ArcadeGames';

    protected array $rules = [
        'edition_id' => 'required|exists:editions,id',
        'slug' => 'required|string|max:64',
        'name' => 'required|string|max:255',
        'is_active' => 'boolean',
        'is_pvp' => 'boolean',
        'min_wager' => 'required|integer|min:1',
        'max_wager' => 'required|integer|min:1|gte:min_wager',
        'daily_win_cap' => 'nullable|integer|min:1',
        'payout_config' => 'required|json',
    ];

    protected function storeRules(): array
    {
        return array_merge($this->rules, [
            'slug' => [
                'required', 'string', 'max:64',
                Rule::unique('arcade_games')->where('edition_id', request('edition_id')),
            ],
        ]);
    }

    protected function updateRules($old): array
    {
        return array_merge($this->rules, [
            'slug' => [
                'required', 'string', 'max:64',
                Rule::unique('arcade_games')->where('edition_id', request('edition_id'))
                    ->ignore($old->id),
            ],
        ]);
    }

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }

    protected function created(array $new): ?array
    {
        $new['payout_config'] = json_decode($new['payout_config'], true);

        return $new;
    }

    protected function updated($old, array $new): ?array
    {
        $new['payout_config'] = json_decode($new['payout_config'], true);

        return $new;
    }
}
