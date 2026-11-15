<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasFinalPrizeImage
{
    public function updateFinalPrizeImage(UploadedFile $image, $storagePath = 'final-prize-images')
    {
        tap($this->image_path, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->finalPrizeDisk())->delete($previous);
            }

            $path = $image->storePublicly($storagePath, [
                'disk' => $this->finalPrizeDisk(),
            ]);

            $this->forceFill(['image_path' => $path])->save();
        });
    }

    public function deleteFinalPrizeImage()
    {
        if ($this->image_path) {
            Storage::disk($this->finalPrizeDisk())->delete($this->image_path);
            $this->forceFill(['image_path' => null])->save();
        }
    }

    public function finalPrizeImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path
                ? Storage::disk($this->finalPrizeDisk())->url($this->image_path)
                : null
        );
    }

    protected function finalPrizeDisk(): string
    {
        return config('jetstream.final_prize_image_disk', 'public');
    }
}
