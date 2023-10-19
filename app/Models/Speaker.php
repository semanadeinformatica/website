<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use League\CommonMark\Util\HtmlFilter;

class Speaker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'organization',
        'social_media_id',
    ];

    protected $with = ['socialMedia'];

    protected $appends = ['description_html'];

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'organization' => $this->organization,
            'social_media' => $this->socialMedia?->toSearchableArray(),
        ];
    }

    public function descriptionHtml(): Attribute
    {
        return Attribute::get(fn () => Str::markdown($this->description, [
            'html_input' => HtmlFilter::STRIP,
            'allow_unsafe_links' => false,
        ]));
    }
}
