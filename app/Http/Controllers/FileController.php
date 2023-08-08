<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ProcessesFiles;

class FileController extends Controller
{
    use ProcessesFiles;

    public function download(Request $request)
    {
        return $this->downloadFromUrl($request->url, 'public'); //hardcoded for now
    }
}
