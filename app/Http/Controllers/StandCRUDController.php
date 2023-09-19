<?php

namespace App\Http\Controllers;

use App\Models\EventDay;
use App\Models\Sponsor;

class StandCRUDController extends CRUDController
{
    protected array $rules = [
        'sponsor_id' => 'required|integer|in:sponsors,id',
        'event_day_id' => 'required|integer|in:event_days,id',
    ];

    protected function with(): array
    {
        return [
            'sponsors' => Sponsor::with('company.user')->get(),
            'eventDays' => EventDay::all(),
        ];
    }
}
