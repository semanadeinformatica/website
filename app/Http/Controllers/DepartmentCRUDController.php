<?php

namespace App\Http\Controllers;

use App\Models\Edition;

class DepartmentCRUDController extends CRUDController
{
    protected array $search = ['name'];

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
