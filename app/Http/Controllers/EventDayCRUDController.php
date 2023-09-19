<?php

namespace App\Http\Controllers;

use App\Models\Edition;

class EventDayCRUDController extends CRUDController
{
    protected array $rules = [
        'edition_id' => 'required|integer|in:editions,id',
        'date' => 'required|date',
        'theme' => 'required|string',
    ];

    protected $with = ['date', 'theme'];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
