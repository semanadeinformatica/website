<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Participant;

use App\Models\User;



use PDF;

class CertificateController extends Controller
{
    public function generateCertificate(Request $request)
    {
        $request->validate([
            'event_title' => 'required|string|max:255',
            'event_type' => 'required|string|max:255',
            'date' => 'required|string',
            'time' => 'required|string',
        ]);

        $imageData = '';
        if (Storage::disk('public')->exists('images/cy-sinf-small.svg')) {
            $imageContent = Storage::disk('public')->get('images/cy-sinf-small.svg');
            $imageData = 'data:image/svg+xml;base64,' . base64_encode($imageContent);
        }

        dd($imageData);

        $data = [
            'participant_name' => auth()->user()->name,
            'event_title' => $request->event_title,
            'event_type' => $request->event_type,
            'date' => $request->date,
            'time' => $request->time,
            '$imageData' => $imageData,
            'event_date' => '21 e 23 de outubro de 2025',
            'coordinators' => [
                'diogo' => 'Diogo Fernandes',
                'eduarda' => 'Eduarda Magno'
            ]
        ];

        $pdf = PDF::loadView('participation', $data);

        return $pdf->download('document.pdf');
    }

    public function previewCertificate(Request $request)
    {
        $request->validate([
            'participant_name' => 'required|string|max:255',
            'talk_title' => 'required|string|max:255',
            'date' => 'required|string',
            'time' => 'required|string',
        ]);

        $data = [
            'participant_name' => $request->participant_name,
            'talk_title' => $request->talk_title,
            'date' => $request->date,
            'time' => $request->time,
            'event_date' => '21 e 23 de outubro de 2025',
            'coordinators' => [
                'diogo' => 'Diogo Fernandes',
                'eduarda' => 'Eduarda Magno'
            ]
        ];

        return view('certificates.participation', $data);
    }
}
