<?php

namespace App\Http\Controllers;

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
        $request->user()->deleteCV();

        return back(303)->with('status', 'cv-deleted');
    }

    /**
     * Update the current user's CV.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        dd($request->user()->usertype());
        $request->user()->usertype()->updateCV($request->cv);

        return back(303)->with('status', 'cv-updated');
    }
}
