<?php

namespace App\Http\Controllers;

use App\Models\EventDay;
use App\Models\Sponsor;
use App\Models\Stand;

class StandCRUDController extends CRUDController
{
    protected string $model = Stand::class;

    protected string $view = 'Stand';

    protected function editionRelationPath(): ?string
    {
        return 'event_day';
    }

    protected array $rules = [
        'sponsor_id' => 'required|integer|exists:sponsors,id',
        'event_day_id' => 'required|integer|exists:event_days,id',
    ];

    protected function with(): array
    {
        return [
            'sponsors' => Sponsor::query()
                ->select('id', 'company_id')
                ->with([
                    'company:id,user_id',
                    'company.user' => fn ($query) => $query->without('usertype')->select('id', 'name'),
                ])
                ->get(),
            'eventDays' => EventDay::query()->select('id', 'date', 'edition_id')->get(),
        ];
    }
}
