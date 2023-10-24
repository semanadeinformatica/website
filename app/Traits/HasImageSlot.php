<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImageSlot
{
    /**
     * Update the user's CV.
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updateImageSlot(UploadedFile $image, $storagePath = 'slot-images')
    {

        tap($this->image_path, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->ImageSlotDisk())->delete($previous);
            }

            $this->forceFill([
                'image_path' => $image->storePublicly(
                    $storagePath, ['disk' => $this->ImageSlotDisk()]
                ),
            ])->save();
        });
    }

    /**
     * Delete the user's CV.
     *
     * @return void
     */
    public function deleteImageSlot()
    {
        if (is_null($this->image_path)) {
            return;
        }

        Storage::disk($this->ImageSlotDisk())->delete($this->image_path);

        $this->forceFill([
            'image_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's CV.
     */
    public function ImageSlotUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::disk($this->ImageSlotDisk())->url($this->image_path) : url('images/default_sticker.png'));
    }

    /**
     * Get the disk that CVs should be stored on.
     *
     * @return string
     */
    protected function ImageSlotDisk()
    {
        return config('jetstream.slot_image_disk', 'public');
    }
}
