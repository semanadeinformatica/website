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
        'required_event_id',
        'required_quest_id',
        'place',
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
        'place' => 'integer',
        'is_active' => 'boolean',
        'draw_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * The legacy ticket totals are not appended to every serialization:
     * final prize draws are points-based and the public raffle controller
     * loads the aggregate explicitly when it needs it.
     *
     * @var array<int, string>
     */
    protected $appends = [];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function required_event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'required_event_id');
    }

    public function requiredEvent(): BelongsTo
    {
        return $this->required_event();
    }

    public function required_quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'required_quest_id');
    }

    public function requiredQuest(): BelongsTo
    {
        return $this->required_quest();
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
        if (array_key_exists('total_tickets', $this->attributes)) {
            return (int) $this->attributes['total_tickets'];
        }

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
