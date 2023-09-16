<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'time_start',
        'time_end',
        'topic',
        'capacity',
        'event_day_id',
    ];

    public function event_day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function enrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class);
    }
}
