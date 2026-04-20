<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CvController extends Controller
{
    public function download(Request $request): Response
    {
        $user = $request->user()->load(['mahasiswaProfile', 'skills']);

        $pdf = Pdf::loadView('pdf.cv', [
            'user' => $user,
            'profile' => $user->mahasiswaProfile,
            'skills' => $user->skills,
        ]);

        return $pdf->download('cv-'.$user->id.'.pdf');
    }
}
