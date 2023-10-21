<?php

namespace App\Models;

use App\Traits\HasCV;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasCV;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'social_media_id',
    ];

    protected $with = ['socialMedia'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'cv_url',
        'quest_qr_code',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function competitionTeams(): BelongsToMany
    {
        return $this->belongsToMany(CompetitionTeam::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'social_media' => $this->socialMedia?->toSearchableArray(),
        ];
    }

    public function questQrCode(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->quest_code) {
                return null;
            }

            $svg = (new Writer(
                new ImageRenderer(
                    new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(248, 245, 231), new Rgb(0, 113, 114))),
                    new SvgImageBackEnd
                )
            ))->writeString($this->quest_code);

            return trim(substr($svg, strpos($svg, "\n") + 1));
        });
    }
}
