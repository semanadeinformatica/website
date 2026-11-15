<?php

namespace App\Models;

use App\Traits\HasFinalPrizeImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalPrize extends Model
{
    use HasFactory;
    use HasFinalPrizeImage;

    protected $appends = ['final_prize_image_url'];

    protected $fillable = [
        'edition_id',
        'place',
        'title',
        'image_path',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
