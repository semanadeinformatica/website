<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Edition;
use App\Models\Staff;
use App\Models\Student;

class StaffCRUDController extends CRUDController
{
    protected string $model = Staff::class;

    protected string $view = 'Staff';

    protected array $rules = [
        'student_id' => 'required|integer',
        'department_id' => 'required|integer',
        'coordinator' => 'sometimes|boolean',
    ];

    protected function created(array $new): ?array
    {
        return [
            'coordinator' => $new['coordinator'],
            'department_id' => $new['department_id'],
            'student_id' => $new['student_id'],
        ];
    }

    protected function updated(array $old, array $new): ?array
    {
        return [
            'coordinator' => $new['coordinator'],
            'department_id' => $new['department_id'],
            'student_id' => $new['student_id'],
        ];
    }

    protected function with(): array
    {
        return [
            'students' => Student::with('user')->get(),
            'departments' => Department::all(),
            'editions' => Edition::all(),
        ];
    }
}
