<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'coordinator',
        'department_id',
        'student_id',
    ];

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
