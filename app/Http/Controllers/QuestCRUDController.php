<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Quest;

class QuestCRUDController extends CRUDController
{
    protected string $model = Quest::class;

    protected string $view = 'Quest';

    protected array $rules = [
        'name' => 'required|string',
        'points' => 'required|integer',
        'category' => 'required|string|in:company,talk,workshop,milestone,teambuiling',
        'requirement' => 'required|regex:/^(company;[0-9]+)$/',
        'edition_id' => 'required|exists:editions,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'companies' => Company::with('user')->get(),
        ];
    }

    protected function created(array $new): ?array
    {
        $requirement = explode(';', $new['requirement']);

        $requirement_type = match ($requirement[0]) {
            'company' => Company::class,
            default => null,
        };
        $requirement_id = $requirement[1];

        return [
            'name' => $new['name'],
            'points' => $new['points'],
            'category' => strtoupper($new['category']),
            'requirement_type' => $requirement_type,
            'requirement_id' => $requirement_id,
            'edition_id' => $new['edition_id'],
        ];
    }

    protected function updated($old, array $new): ?array
    {
        $requirement = explode(';', $new['requirement']);

        $requirement_type = match ($requirement[0]) {
            'company' => Company::class,
            default => null,
        };
        $requirement_id = $requirement[1];

        return [
            'name' => $new['name'],
            'points' => $new['points'],
            'category' => strtoupper($new['category']),
            'requirement_type' => $requirement_type,
            'requirement_id' => $requirement_id,
            'edition_id' => $new['edition_id'],
        ];
    }
}
