<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Department extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'edition_id',
        'priority',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class)->orderByDesc('coordinator');
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'edition' => $this->edition->name,
        ];
    }
}
