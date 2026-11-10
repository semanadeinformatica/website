<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class CVController extends Controller
{
    public function show(Request $request, User $user)
    {
        if ($request->user() === null) {
            return redirect()->route('login');
        }

        $edition = $request->edition;

        if ($edition === null) {
            abort(404);
        }

        Gate::authorize('viewCVOf', [$user, $edition]);

        $participant = $user->usertype;

        if (! $participant instanceof Participant || $participant->cv_path === null) {
            abort(404);
        }

        $disk = Storage::disk(config('jetstream.cv_disk'));
        $mimeType = $disk->mimeType($participant->cv_path) ?: 'application/octet-stream';

        return $disk->response(
            $participant->cv_path,
            null,
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
            $mimeType === 'application/pdf' ? 'inline' : 'attachment'
        );
    }

    /**
     * Delete the current user's CV.
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request)
    {
        Participant::where('user_id', $request->user()->id)->first()->deleteCV();

        return back(303)->with('status', 'cv-deleted');
    }

    /**
     * Update the current user's CV.
     *
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'cv' => ['required', File::types(['pdf'])->max('4mb')],
        ]);

        Participant::where('user_id', $request->user()->id)->first()->updateCV($request->file('cv'));

        return back(303)->with('status', 'cv-updated');
    }
}
