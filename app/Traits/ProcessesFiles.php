<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ProcessesFiles
{
    public function downloadFromUrl($url, $storageName)
    {
        return Storage::disk($storageName)->download($url);
    }
}
