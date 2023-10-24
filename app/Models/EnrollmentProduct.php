<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Laravel\Scout\Searchable;

class EnrollmentProduct extends Pivot
{
    use Searchable;

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function toSearchableArray()
    {
        return [
            'enrollment' => $this->enrollment->toSearchableArray(),
            'product' => $this->product->toSearchableArray(),
            'redeemed' => $this->redeemed ? 'redeemed' : 'not redeemed',
        ];
    }
}
