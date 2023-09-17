<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function show()
    {
        $departments = Department::with([
            'staff' => [
                'student' => [
                    'user',
                ],
            ],
        ])->where('edition_id', '=', 1)->get();

        return Inertia::render('Team', [
            'departments' => $departments,
        ]);
    }
}
