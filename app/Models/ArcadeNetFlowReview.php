<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeNetFlowReview extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLEARED = 'cleared';

    public const STATUS_FLAGGED = 'flagged';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'low_enrollment_id',
        'high_enrollment_id',
        'status',
        'note',
        'reviewed_by',
        'reviewed_match_count',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function lowEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'low_enrollment_id');
    }

    public function highEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'high_enrollment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
