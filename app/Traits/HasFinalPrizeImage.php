<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

trait HasFinalPrizeImage
{
    public static function bootHasFinalPrizeImage(): void
    {
        static::deleting(function (self $prize) {
            $prize->deleteFinalPrizeImage();
        });
    }

    public function updateFinalPrizeImage(UploadedFile $image, $storagePath = 'final-prize-images')
    {
        $disk = $this->finalPrizeDisk();
        $previous = $this->image_path;

        $path = $image->storePublicly($storagePath, [
            'disk' => $disk,
        ]);

        if ($path === false) {
            throw ValidationException::withMessages([
                'image' => 'Não foi possível guardar a imagem do prémio.',
            ]);
        }

        try {
            $this->forceFill(['image_path' => $path])->save();
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        if ($previous) {
            Storage::disk($disk)->delete($previous);
        }
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
