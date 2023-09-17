<?php

namespace App\Http\Controllers;

use App\Models\Edition;

class EditionCRUDController extends CRUDController
{
    protected string $model = Edition::class;

    protected string $view = 'Edition';

    protected array $rules = [
        'name' => 'required|string',
        'year' => 'required|integer',
    ];

    protected array $search = ['name', 'year'];
}
