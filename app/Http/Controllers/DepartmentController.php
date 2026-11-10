<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function show(Request $request)
    {
        /** @var Edition */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $departments = $edition->departments()->with([
            'staff' => [
                'participant' => [
                    'user',
                ],
            ],
        ])->get();

        $departments->each(function ($department) {
            $department->staff->each(function ($staff) {
                $staff->participant?->user?->makeHidden('email');
            });
        });

        return Inertia::render('Team', [
            'departments' => $departments,
        ]);
    }
}
