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
            $this->forceFill([
                'cv_path' => $cv->storePublicly(
                    $storagePath, ['disk' => $this->CVDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->CVDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's CV.
     *
     * @return void
     */
    public function deleteCV()
    {
        //missing check to verify if user can manage CVs

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
    public function CVUrl(): Attribute
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
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.cv_disk', 'public');
    }
}
