<?php

namespace App\Http\Controllers;

use App\Models\EventDay;
use App\Models\Sponsor;
use App\Models\Stand;

class StandCRUDController extends CRUDController
{
    protected string $model = Stand::class;

    protected string $view = 'Stand';

    protected array $rules = [
        'sponsor_id' => 'integer|in:sponsors,id',
        'event_day_id' => 'integer|in:event_days,id',
    ];

    protected function with(): array
    {
        return [
            'sponsors' => Sponsor::with('company.user')->get(),
            'eventDays' => EventDay::all(),
        ];
    }
}
