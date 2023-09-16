<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Edition;

class DepartmentCRUDController extends CRUDController
{
    protected string $model = Department::class;

    protected string $view = 'Department';

    protected array $rules = [
        'edition_id' => 'required|exists:editions,id',
        'name' => 'required|string',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
