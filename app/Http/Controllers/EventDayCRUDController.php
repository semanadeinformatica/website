<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\EventDay;

class EventDayCRUDController extends CRUDController
{
    protected string $model = EventDay::class;

    protected string $view = 'EventDay';

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
