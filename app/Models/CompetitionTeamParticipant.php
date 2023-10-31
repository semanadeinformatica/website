<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Scout\Searchable;

class CompetitionTeamParticipant extends Pivot
{
    use Searchable;

    public $timestamps = false;

    public function competition_team(): BelongsTo
    {
        return $this->belongsTo(CompetitionTeam::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function toSearchableArray()
    {
        return [
            'participant' => $this->participant->toSearchableArray(),
            'competition_team' => $this->competition_team->toSearchableArray(),
        ];
    }
}
