<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    protected function destroyed($old): bool
    {
        return ! ArcadeSession::where('arcade_game_id', $old['id'] ?? null)->exists();
    }

    public function destroy($id)
    {
        $deleted = DB::transaction(function () use ($id) {
            $game = ArcadeGame::lockForUpdate()->findOrFail($id);

            if (! $this->destroyed($game->toArray())) {
                return false;
            }

            $game->delete();

            return true;
        });

        if (! $deleted) {
            return redirect()->back()->dangerBanner(
                'Não é possível eliminar um jogo com sessões registadas.'
            );
        }

        Log::alert('Arcade game deleted', ['arcade_game_id' => $id]);

        return redirect()->back();
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
