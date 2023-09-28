<?php

namespace App\Http\Controllers;

use App\Models\EventType;

class EventTypeCRUDController extends CRUDController
{
    protected string $model = EventType::class;

    protected string $view = 'EventType';

    protected array $rules = [
        'name' => 'required|string',
    ];
}
