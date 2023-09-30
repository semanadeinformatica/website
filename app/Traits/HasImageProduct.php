<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImageProduct
{
    /**
     * Update the user's CV.
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updateImageProduct(UploadedFile $image, $storagePath = 'product-images')
    {

        tap($this->image_path, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->ImageProductDisk())->delete($previous);
            }

            $this->forceFill([
                'image_path' => $image->storePublicly(
                    $storagePath, ['disk' => $this->ImageProductDisk()]
                ),
            ])->save();
        });
    }

    /**
     * Delete the user's CV.
     *
     * @return void
     */
    public function deleteImageProduct()
    {
        if (is_null($this->image_path)) {
            return;
        }

        Storage::disk($this->ImageProductDisk())->delete($this->image_path);

        $this->forceFill([
            'image_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's CV.
     */
    public function ImageProductUrl(): Attribute
    {
        return Attribute::get(function () {
            return Storage::disk($this->ImageProductDisk())->url($this->image_path);
        });
    }

    /**
     * Get the disk that CVs should be stored on.
     *
     * @return string
     */
    protected function ImageProductDisk()
    {
        return config('jetstream.product_image_disk', 'public');
    }
}
