<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme',
        'date_start',
        'date_end',
        'edition_id',
        'first_place',
        'second_place',
        'third_place',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(CompetitionTeam::class);
    }

    public function firstPlace(): BelongsTo
    {
        return $this->belongsTo(CompetitionTeam::class, 'first_place');
    }

    public function secondPlace(): BelongsTo
    {
        return $this->belongsTo(CompetitionTeam::class, 'second_place');
    }

    public function thirdPlace(): BelongsTo
    {
        return $this->belongsTo(CompetitionTeam::class, 'third_place');
    }
}
