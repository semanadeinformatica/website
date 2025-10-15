<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasPrizeImage
{
    /**
     * Update the prize image of a competition
     *
     * @param  string  $storagePath
     * @return void
     */
    public function updatePrizeImage(UploadedFile $image, $storagePath = 'competition-prize-images')
    {

        tap($this->prize_picture, function ($previous) use ($image, $storagePath) {
            if ($previous) {
                Storage::disk($this->ImageCompetitionTeamDisk())->delete($previous);
            }

            $this->forceFill([
                'prize_picture' => $image->storePublicly(
                    $storagePath, ['disk' => $this->ImageCompetitionTeamDisk()]
                ),
            ])->save();
        });
    }

    /**
     * Delete the 
     *
     * @return void
     */
    public function deleteImageSlot()
    {
        if (is_null($this->prize_picture)) {
            return;
        }

        Storage::disk($this->ImageCompetitionTeamDisk())->delete($this->prize_picture);

        $this->forceFill([
            'prize_picture' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's CV.
     */
    public function ImageCompetitionTeamUrl(): Attribute
    {
        return Attribute::get(fn () => $this->prize_picture ? Storage::disk($this->ImageCompetitionTeamDisk())->url($this->prize_picture) : null); // TODO: should add default image
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

