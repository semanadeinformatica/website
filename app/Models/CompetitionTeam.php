<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CompetitionTeam extends Model
{
    use HasFactory;

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
}
