<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use League\CommonMark\Util\HtmlFilter;

class Event extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'time_start',
        'time_end',
        'topic',
        'capacity',
        'event_day_id',
        'description',
        'event_type_id',
        'location',
    ];

    public function event_day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function enrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function scopeTalk(Builder $query): void
    {
        $query->whereHas('type', function ($query) {
            $query->where('name', 'talk');
        });
    }

    public function scopeWorkshop(Builder $query): void
    {
        $query->whereHas('type', function ($query) {
            $query->where('name', 'workshop');
        });
    }

    public function descriptionHtml(): Attribute
    {
        return Attribute::get(fn () => Str::markdown($this->description ?? '', [
            'html_input' => HtmlFilter::STRIP,
            'allow_unsafe_links' => false,
        ]));
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'topic' => $this->topic,
            'event_day' => $this->event_day->toSearchableArray(),
        ];
    }
}
