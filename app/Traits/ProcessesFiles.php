<?php

namespace App\Traits;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

trait ProcessesFiles {
    
    function downloadFromUrl($url, $storageName) {
        return Storage::disk($storageName)->download($url);
    }
}