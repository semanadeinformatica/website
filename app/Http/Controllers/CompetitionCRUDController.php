<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Edition;
use Illuminate\Validation\Rule;

class CompetitionCRUDController extends CRUDController
{
    protected string $model = Competition::class;

    protected string $view = 'Competition';

    protected array $rules = [
        'theme' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after_or_equal:date_start',
        'edition_id' => 'required|integer|exists:editions,id',
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255',
        'description' => 'nullable|string',
        'registration_link' => 'required|url|max:255',
        'regulation' => 'required|string',
    ];

    protected function storeRules(): array
    {
        return [
            ...$this->rules,
            'slug' => ['required', 'string', 'max:255', Rule::unique('competitions', 'slug')],
        ];
    }

    protected function updateRules($old): array
    {
        return [
            ...$this->rules,
            'slug' => ['required', 'string', 'max:255', Rule::unique('competitions', 'slug')->ignore($old?->id)],
        ];
    }

    protected function with(): array
    {
        return [
            'editions' => Edition::query()->select('id', 'name')->get(),
        ];
    }
}
