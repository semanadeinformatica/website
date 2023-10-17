<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class CompetitionTeam extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'points',
        'competition_id',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'competition' => $this->competition->name,
            'edition' => $this->competition->edition->name,
        ];
    }
}
