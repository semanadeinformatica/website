<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'theme',
        'edition_id',
    ];

    protected $with = [
        'stands' => [
            'sponsor' => [
                'company' => [
                    'user',
                ],
            ],
        ],
        'events' => [
            'speakers',
        ],
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function events(): HasMany
    {
        // FIXME: I do not like having to put the ordering here but it is what it is - Nuno Pereira
        return $this->hasMany(Event::class)->orderBy('time_start', 'asc');
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }
}
