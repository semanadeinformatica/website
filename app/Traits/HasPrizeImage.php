<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasPrizeImage
{
    public function updatePrizeImage(UploadedFile $image, $storagePath = 'competition-prize-images')
    {
        tap($this->prize_picture, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->competitionPrizeDisk())->delete($previous);
            }

            $this->forceFill([
                'prize_picture' => $image->storePublicly(
                    $storagePath, ['disk' => $this->competitionPrizeDisk()]
                ),
            ])->save();
        });
    }

    public function deletePrizeImage()
    {
        if (is_null($this->prize_picture)) {
            return;
        }

        Storage::disk($this->competitionPrizeDisk())->delete($this->prize_picture);

        $this->forceFill(['prize_picture' => null])->save();
    }

    public function prizeImageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->prize_picture) {
                return Storage::disk($this->competitionPrizeDisk())->url($this->prize_picture);
            }

            return asset('images/default-prize.png');
        });
    }

    protected function competitionPrizeDisk(): string
    {
        return config('filesystems.default', 'public');
    }
}
