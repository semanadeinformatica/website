<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class RaffleDraw extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'edition_id',
        'name',
        'description',
        'ticket_price',
        'is_active',
        'draw_at',
        'winner_enrollment_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'draw_at' => 'datetime',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'winner_enrollment_id');
    }

    /**
     * Get the total number of tickets sold across all participants.
     */
    public function getTotalTicketsAttribute(): int
    {
        return (int) ($this->relationLoaded('tickets')
            ? $this->tickets->sum('quantity')
            : $this->tickets()->sum('quantity'));
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
