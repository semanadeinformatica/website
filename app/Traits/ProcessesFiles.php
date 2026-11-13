<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ProcessesFiles
{
    public function downloadFromUrl($url)
    {
        return Storage::disk('public')->download($url);
    }
}
