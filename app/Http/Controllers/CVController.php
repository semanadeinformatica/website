<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CVController extends Controller
{
    /**
     * Delete the current user's CV.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Student::where("user_id", $request->user()->id)->first()->deleteCV();

        return back(303)->with('status', 'cv-deleted');
    }

    /**
     * Update the current user's CV.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        Student::where("user_id", $request->user()->id)->first()->updateCV($request->cv);

        return back(303)->with('status', 'cv-updated');
    }
}
