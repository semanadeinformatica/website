<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Staff extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'coordinator',
        'department_id',
        'participant_id',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'department' => $this->department->toSearchableArray(),
            'edition' => $this->department->edition->name,
            'participant' => $this->participant->user->toSearchableArray(),
        ];
    }
}
