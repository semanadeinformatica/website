<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResume;
use App\Models\Resume;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ResumeController extends Controller
{
    public function index()
    {
        $resumes = Resume::latest()->get();

        return Inertia::render('Resume/Index', ['resumes' => $resumes]);
    }

    public function create()
    {
        return Inertia::render('Resume/Create');
    }

    public function store(StoreResume $request)
    {
        $resume_path = '';

        if ($request->hasFile('resume')) {
            $resume_path = $request->file('resume')->store('resumes', 'public');
        }

        $data = resume::create([
            'resume' => $resume_path,
        ]);

        return Redirect::route('dashboard');
    }
}
