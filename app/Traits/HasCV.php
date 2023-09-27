<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasCV
{
    /**
     * Update the user's CV.
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updateCV(UploadedFile $cv, $storagePath = 'cvs')
    {
        tap($this->cv_path, function ($previous) use ($cv, $storagePath) {
            if ($previous) {
                Storage::disk($this->CVDisk())->delete($previous);
            } else {
                // Give points for CV submission
            }

            $this->forceFill([
                'cv_path' => $cv->storePublicly(
                    $storagePath, ['disk' => $this->CVDisk()]
                ),
            ])->save();
        });
    }

    /**
     * Delete the user's CV.
     *
     * @return void
     */
    public function deleteCV()
    {
        //#TODO: missing check to verify if user can manage CVs

        if (is_null($this->cv_path)) {
            return;
        }

        Storage::disk($this->CVDisk())->delete($this->cv_path);

        $this->forceFill([
            'cv_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's CV.
     */
    public function CvUrl(): Attribute
    {
        return Attribute::get(function () {
            return Storage::disk($this->CVDisk())->url($this->cv_path);
        });
    }

    /**
     * Get the disk that CVs should be stored on.
     *
     * @return string
     */
    protected function CVDisk()
    {
        return config('jetstream.cv_disk', 'public');
    }
}
