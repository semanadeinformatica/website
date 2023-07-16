<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    ];

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }

    public function socialMedia(): BelongsTo {
        return $this->belongsTo(SocialMedia::class);
    }
}