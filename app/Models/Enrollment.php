<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Enrollment extends Model
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
        'participant_id',
        'points',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using(EnrollmentProduct::class);
    }

    public function slots(): BelongsToMany
    {
        return $this->belongsToMany(Slot::class);
    }

    public function quests(): BelongsToMany
    {
        return $this->belongsToMany(Quest::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'participant' => $this->participant->user->toSearchableArray(),
            'edition' => $this->edition->name,
        ];
    }
}
