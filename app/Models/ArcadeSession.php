<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'enrollment_id',
        'arcade_game_id',
        'challenger_enrollment_id',
        'wager',
        'outcome',
        'payout',
        'server_state',
        'started_at',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'server_state' => 'array',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'server_state',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'challenger_enrollment_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    /**
     * Scope a query to only include pending sessions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('outcome', 'pending');
    }

    /**
     * Scope a query to only include sessions for a specific enrollment.
     */
    public function scopeForEnrollment(Builder $query, int $enrollmentId): Builder
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    /**
     * Scope a query to only include sessions won today for a specific enrollment and game.
     */
    public function scopeWonToday(Builder $query, int $enrollmentId, int $gameId): Builder
    {
        return $query->where('enrollment_id', $enrollmentId)
            ->where('arcade_game_id', $gameId)
            ->where('outcome', 'win')
            ->whereDate('created_at', today());
    }
}
