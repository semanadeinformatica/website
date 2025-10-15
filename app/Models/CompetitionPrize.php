<?php

namespace App\Models;

use App\Traits\HasPrizeImage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionPrize extends Model
{
    use HasFactory;
    use HasPrizeImage;

    protected $appends = ['prize_image_url'];

    protected $fillable = [
        'competition_id',
        'place',
        'prize_picture',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
