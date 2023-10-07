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

    protected $casts = [
        'date' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function talks(): HasMany
    {
        return $this->events()->whereHas('type', function ($query) {
            $query->where('name', 'talk');
        });
    }

    public function workshops(): HasMany
    {
        return $this->events()->whereHas('type', function ($query) {
            $query->where('name', 'workshop');
        });
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }
}
