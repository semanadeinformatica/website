<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Gallery/Index');
    }

    public function store(Request $request)
    {
    }
}
