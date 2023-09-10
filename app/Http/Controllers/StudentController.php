<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    protected string $model = Student::class;

    public function show($id)
    {
        $item = $this->model::find($id);

        return Inertia::render("Profile/Show", [
            'item' => $item,
        ]);
    }

    
}