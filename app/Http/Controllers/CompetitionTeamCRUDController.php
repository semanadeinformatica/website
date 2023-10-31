<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

class CompetitionTeamCRUDController extends CRUDController
{
    protected string $model = CompetitionTeam::class;

    protected string $view = 'CompetitionTeam';

    protected array $rules = [
        'name' => 'required|string',
        'points' => 'required|integer',
        'competition_id' => 'required|integer|exists:competitions,id',
        'image' => 'nullable|mimes:jpg,jpeg,png|max:10240',
    ];

    protected function created(array $new): ?array
    {
        DB::beginTransaction();
        /** @var CompetitionTeam */
        $team = CompetitionTeam::create($new);

        if (isset($new['image'])) {
            $team->updateImageCompetitionTeam($new['image']);
        }

        DB::commit();

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        if (isset($new['image'])) {
            $old->updateImageCompetitionTeam($new['image']);
        }

        return $new;
    }

    protected function with(): array
    {
        return [
            'competitions' => Competition::with('edition')->get(),
            'participants' => Participant::with('user')->get(),
        ];
    }
}
