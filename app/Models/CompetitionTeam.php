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
        'competition_info_id',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(CompetitionInfo::class);
    }
}
