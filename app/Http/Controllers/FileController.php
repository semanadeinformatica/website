<?php

namespace App\Http\Controllers;

use App\Traits\ProcessesFiles;
use Illuminate\Http\Request;

class FileController extends Controller
{
    use ProcessesFiles;

    public function download(Request $request)
    {
        return $this->downloadFromUrl($request->url, $request->storageDisk);
    }
}
