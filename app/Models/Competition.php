<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use League\CommonMark\Util\HtmlFilter;

class Competition extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'theme',
        'date_start',
        'date_end',
        'edition_id',
        'name',
        'slug',
        'registration_link',
        'regulation',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    protected $appends = [
        'regulation_html',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(CompetitionTeam::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'theme' => $this->theme,
            'edition' => $this->edition->name,
        ];
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(CompetitionPrize::class);
    }

    public function regulationHtml(): Attribute
    {
        return Attribute::get(fn () => Str::markdown($this->regulation, [
            'html_input' => HtmlFilter::STRIP,
            'allow_unsafe_links' => false,
        ]));
    }
}
