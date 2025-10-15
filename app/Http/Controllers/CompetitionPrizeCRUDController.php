<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionPrize;
use App\Models\Competition;

use Illuminate\Support\Facades\DB;

class CompetitionPrizeCRUDController extends CRUDController
{
    protected string $model = CompetitionPrize::class;

    protected string $view = 'CompetitionPrizes';

    protected array $rules = [
        'place' => 'required|integer|min:1|max:3',
        'prize_picture' => 'nullable|mimes:jpg,jpeg,png|max:10240',
        'competition_id' => 'required|integer|exists:competitions,id',
    ];

    protected function created(array $new): ?array
    {

        DB::beginTransaction();
        /** @var CompetitionPrize */
        $prize = CompetitionPrize::create($new);

        if (isset($new['prize_picture'])) {
            $prize->updatePrizeImage($new['prize_picture']);
        }

        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        if (isset($new['prize_picture'])) {
            $old->updatePrizeImage($new['prize_picture']);
        }

        return $new;
    }

    protected function with(): array
    {
        return [
            'competitions' => Competition::with('edition')->get(),
        ];
    }

}
