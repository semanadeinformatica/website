<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Sponsor extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sponsor_tier_id',
        'edition_id',
        'company_id',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(SponsorTier::class, 'sponsor_tier_id');
    }

    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'company' => $this->company->user->toSearchableArray(),
            'edition' => $this->edition->name,
            'tier' => $this->tier->name,
        ];
    }
}
