<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Student;

class EnrollmentCRUDController extends CRUDController
{
    protected string $model = Enrollment::class;

    protected string $view = 'Enrollment';

    protected array $rules = [
        'student_id' => 'required|exists:students,id',
        'edition_id' => 'required|exists:editions,id',
        'points' => 'required|integer',
    ];

    protected array $search = ['student_id', 'edition_id', 'points'];

    protected function with(): array
    {
        return [
            'students' => Student::with('user')->get(),
            'editions' => Edition::all(),
        ];
    }
}
