<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_quests',
        'points',
    ];

    protected $with = [
        'quests',
    ];

    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(Quest::class);
    }

    public function enrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class);
    }
}
