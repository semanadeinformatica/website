<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Stand extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'sponsor_id',
        'event_day_id',
    ];

    public function event_day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'sponsor' => $this->sponsor->toSearchableArray(),
            'event_day' => $this->event_day->toSearchableArray(),
        ];
    }
}
