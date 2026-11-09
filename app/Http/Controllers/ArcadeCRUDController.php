<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\Edition;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        return $this->decodePayoutConfig($new);
    }

    protected function updated($old, array $new): ?array
    {
        return $this->decodePayoutConfig($new);
    }

    private function decodePayoutConfig(array $new): array
    {
        $decoded = json_decode($new['payout_config'], true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'payout_config' => 'A configuração de pagamento deve ser um objeto JSON.',
            ]);
        }

        $new['payout_config'] = $decoded;

        return $new;
    }
}
