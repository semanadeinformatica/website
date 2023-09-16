<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stand extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'event_day_id',
    ];

    public function event_days(): BelongsTo
    {
        return $this->belongsTo(EventDay::class);
    }

    public function sponsors(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }
}
