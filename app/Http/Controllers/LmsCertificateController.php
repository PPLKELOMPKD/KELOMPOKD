<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\LmsProgressService;

class LmsCertificateController extends Controller
{
    public function download(Request $request, LmsCourse $course, LmsProgressService $progressService)
    {
        abort_if($request->user()->role !== 'mahasiswa', 403);

        $enrollment = $request->user()->lmsEnrollments()->where('course_id', $course->id)->firstOrFail();

        $progress = $progressService->courseProgress($enrollment);

        abort_if(!$enrollment->is_graduated, 403, 'Sertifikat belum tersedia. Tunggu konfirmasi kelulusan dari perusahaan.');

        $course->load('company.perusahaanProfile');

        $finalGrade = $progressService->calculateFinalGrade($enrollment);

        $pdf = Pdf::loadView('lms.certificate', [
            'student' => $request->user(),
            'course' => $course,
            'grade' => $finalGrade,
            'date' => now()->translatedFormat('d F Y'),
            'certificate_no' => 'SKR-' . strtoupper($course->slug) . '-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT),
            'provider_name' => $course->company->perusahaanProfile->name ?? 'Sikara LMS',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('sertifikat-' . $course->slug . '.pdf');
    }
}
