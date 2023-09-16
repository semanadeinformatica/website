<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function show(Student $student)
    {
        return Inertia::render('Profile/Show', [
            'student' => $student->load('user')->toArray(),
        ]);
    }
}
