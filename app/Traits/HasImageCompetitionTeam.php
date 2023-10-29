<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasImageCompetitionTeam
{
    /**
     * Update the user's CV.
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updateImageCompetitionTeam(UploadedFile $image, $storagePath = 'competition-team-images')
    {

        tap($this->image_path, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->ImageCompetitionTeamDisk())->delete($previous);
            }

            $this->forceFill([
                'image_path' => $image->storePublicly(
                    $storagePath, ['disk' => $this->ImageCompetitionTeamDisk()]
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

        Storage::disk($this->ImageCompetitionTeamDisk())->delete($this->image_path);

        $this->forceFill([
            'image_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's CV.
     */
    public function ImageCompetitionTeamUrl(): Attribute
    {
        return Attribute::get(fn () => Storage::disk($this->ImageCompetitionTeamDisk())->url($this->image_path)); // TODO: should add default image
    }

    /**
     * Get the disk that CVs should be stored on.
     *
     * @return string
     */
    protected function ImageCompetitionTeamDisk()
    {
        return config('jetstream.competition_team_image_disk', 'public');
    }
}
