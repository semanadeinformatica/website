<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'linkedin',
        'twitter',
        'facebook',
        'website',
        'email',
    ];

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }
}