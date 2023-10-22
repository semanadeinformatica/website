<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class EventDay extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'date',
        'theme',
        'edition_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function talks(): HasMany
    {
        return $this->events()->talk();
    }

    public function activities(): HasMany
    {
        return $this->events()->activity();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class)->orderBy('time_start');
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'theme' => $this->theme,
            'date' => $this->date->format('Y-m-d'),
            'edition' => $this->edition->name,
        ];
    }
}
