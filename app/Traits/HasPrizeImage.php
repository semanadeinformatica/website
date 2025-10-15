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

            // `storePublicly()` already prefixes with $storagePath, so don’t prepend it again
            $path = $image->storePublicly($storagePath, [
                'disk' => $this->competitionPrizeDisk(),
            ]);

            $this->forceFill(['prize_picture' => $path])->save();
        });
    }

    public function deletePrizeImage()
    {
        if ($this->prize_picture) {
            Storage::disk($this->competitionPrizeDisk())->delete($this->prize_picture);
            $this->forceFill(['prize_picture' => null])->save();
        }
    }

    public function prizeImageUrl(): Attribute
    {
        return Attribute::get(fn () =>
            $this->prize_picture
                ? Storage::disk($this->competitionPrizeDisk())->url($this->prize_picture)
                : asset('images/default-prize.png')
        );
    }

    protected function competitionPrizeDisk(): string
    {
        return 'competition_prize';
    }
}
