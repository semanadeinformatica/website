<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\HasProfilePhoto;

class Speaker extends Model
{
    use HasFactory;
    use HasProfilePhoto;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'organization',
        'event_id',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected $with = ['socialMedia'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }
}
