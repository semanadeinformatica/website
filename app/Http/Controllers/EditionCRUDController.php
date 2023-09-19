<?php

namespace App\Http\Controllers;

class EditionCRUDController extends CRUDController
{
    protected array $rules = [
        'name' => 'required|string',
        'year' => 'required|integer',
    ];

    protected array $search = ['name', 'year'];
}
