<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Internship;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class CompanyReportController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX – Halaman laporan analitik (chart + tabel keterisian)
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user  = $request->user();
        $month = $request->input('month', 'all');
        $year  = $request->input('year', date('Y'));

        $companyInternshipsQuery  = Internship::where('company_id', $user->id);
        $filteredInternshipsQuery = clone $companyInternshipsQuery;

        if ($month !== 'all') {
            $filteredInternshipsQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        $activeInternshipsCountQuery = (clone $filteredInternshipsQuery)
            ->where('is_published', true)
            ->where('moderation_status', 'approved');
        if ($month === 'all') {
            $activeInternshipsCountQuery->where('deadline_at', '>=', now());
        }
        $activeInternshipsCount = $activeInternshipsCountQuery->count();

        $allInternshipIds  = (clone $companyInternshipsQuery)->pluck('id');
        $applicationsQuery = Application::whereIn('internship_id', $allInternshipIds);

        if ($month !== 'all') {
            $applicationsQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        $totalApplicants     = (clone $applicationsQuery)->count();
        $processedApplicants = (clone $applicationsQuery)
            ->whereIn('status', ['wawancara', 'lolos', 'tidak lolos'])
            ->count();

        $internshipsData = (clone $companyInternshipsQuery)
            ->withCount(['applications' => function ($q) use ($month, $year) {
                if ($month !== 'all') {
                    $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
                }
            }])
            ->withCount(['applications as accepted_count' => function ($q) use ($month, $year) {
                $q->where('status', 'lolos');
                if ($month !== 'all') {
                    $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
                }
            }])
            ->get()
            ->map(function ($internship) {
                $totalApps  = $internship->applications_count;
                $quota      = $internship->quota ?: 1;
                $percentage = $quota > 0 ? round(($totalApps / $quota) * 100) : 0;

                return [
                    'id'               => $internship->id,
                    'title'            => $internship->title,
                    'quota'            => $internship->quota,
                    'applicants_count' => $totalApps,
                    'accepted_count'   => $internship->accepted_count,
                    'quota_percentage' => $percentage,
                    'is_published'     => $internship->is_published,
                    'status'           => $internship->deadline_at && $internship->deadline_at->isPast() ? 'Expired' : 'Aktif',
                ];
            });

        return Inertia::render('Company/Reports/Index', [
            'filters' => ['month' => $month, 'year' => $year],
            'stats'   => [
                'active_internships'  => $activeInternshipsCount,
                'total_applicants'    => $totalApplicants,
                'processed_applicants' => $processedApplicants,
            ],
            'internships_data' => $internshipsData,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // DOWNLOAD LAPORAN REKRUTMEN (PDF atau CSV/Excel)
    // GET /perusahaan/reports/download?format=pdf|excel&month=all
    // ──────────────────────────────────────────────────────────────
    public function downloadRecruitment(Request $request)
    {
        $user   = $request->user();
        $format = strtolower($request->input('format', 'pdf'));
        $month  = $request->input('month', 'all');
        $year   = $request->input('year', date('Y'));

        // ── Data Rekrutmen ───────────────────────────────────────
        $companyInternshipsQuery = Internship::where('company_id', $user->id);
        $allInternshipIds        = (clone $companyInternshipsQuery)->pluck('id');

        $applicationsQuery = Application::whereIn('internship_id', $allInternshipIds);
        if ($month !== 'all') {
            $applicationsQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        $totalApplicants     = (clone $applicationsQuery)->count();
        $processedApplicants = (clone $applicationsQuery)
            ->whereIn('status', ['wawancara', 'lolos', 'tidak lolos'])->count();
        $acceptedApplicants  = (clone $applicationsQuery)->where('status', 'lolos')->count();
        $interviewApplicants = (clone $applicationsQuery)->where('status', 'wawancara')->count();
        $rejectedApplicants  = (clone $applicationsQuery)->where('status', 'tidak lolos')->count();

        // Per lowongan
        $internshipsData = (clone $companyInternshipsQuery)
            ->withCount(['applications' => function ($q) use ($month, $year) {
                if ($month !== 'all') {
                    $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
                }
            }])
            ->withCount(['applications as lolos_count' => fn ($q) => $q->where('status', 'lolos')])
            ->withCount(['applications as wawancara_count' => fn ($q) => $q->where('status', 'wawancara')])
            ->withCount(['applications as tidak_lolos_count' => fn ($q) => $q->where('status', 'tidak lolos')])
            ->get()
            ->map(function ($i) {
                $quota      = $i->quota ?: 1;
                $percentage = $quota > 0 ? round(($i->applications_count / $quota) * 100) : 0;
                return [
                    'id'               => $i->id,
                    'title'            => $i->title,
                    'quota'            => $i->quota ?? '-',
                    'applicants_count' => $i->applications_count,
                    'lolos_count'      => $i->lolos_count,
                    'wawancara_count'  => $i->wawancara_count,
                    'tidak_lolos_count'=> $i->tidak_lolos_count,
                    'quota_percentage' => $percentage,
                    'status'           => ($i->deadline_at && $i->deadline_at->isPast()) ? 'Expired' : 'Aktif',
                    'is_published'     => $i->is_published ? 'Ya' : 'Tidak',
                ];
            });

        $monthLabel = $this->getMonthLabel($month, $year);
        $companyName = $user->name;
        $generatedAt = now()->format('d M Y H:i');

        $data = compact(
            'companyName', 'monthLabel', 'generatedAt',
            'totalApplicants', 'processedApplicants', 'acceptedApplicants',
            'interviewApplicants', 'rejectedApplicants', 'internshipsData'
        );

        if ($format === 'excel') {
            return $this->exportRecruitmentExcel($data, $monthLabel, $companyName);
        }

        // Default: PDF
        $pdf = Pdf::loadView('reports.recruitment', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'Laporan-Rekrutmen-' . str_replace(' ', '-', $monthLabel) . '.pdf';
        return $pdf->download($filename);
    }

    // ──────────────────────────────────────────────────────────────
    // DOWNLOAD LAPORAN EVENT (PDF atau CSV/Excel)
    // GET /perusahaan/reports/events/download?format=pdf|excel
    // ──────────────────────────────────────────────────────────────
    public function downloadEvents(Request $request)
    {
        $user   = $request->user();
        $format = strtolower($request->input('format', 'pdf'));
        $month  = $request->input('month', 'all');
        $year   = $request->input('year', date('Y'));

        $eventsQuery = Event::where('company_id', $user->id);
        if ($month !== 'all') {
            $eventsQuery->whereMonth('date', $month)->whereYear('date', $year);
        }

        $events = $eventsQuery->withCount([
                'registrations',
                'registrations as attended_count' => fn ($q) => $q->where('status', 'attended'),
                'registrations as cancelled_count' => fn ($q) => $q->where('status', 'cancelled'),
            ])
            ->withAvg('ratings', 'rating')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($e) {
                return [
                    'id'               => $e->id,
                    'title'            => $e->title,
                    'category'         => $e->category ?? '-',
                    'type'             => $e->type ?? '-',
                    'date'             => optional($e->date)->format('d M Y') ?? '-',
                    'location'         => $e->location ?? 'Online',
                    'status'           => ucfirst($e->status),
                    'max_participants'  => $e->max_participants ?? '∞',
                    'total_registered' => $e->registrations_count,
                    'attended_count'   => $e->attended_count,
                    'cancelled_count'  => $e->cancelled_count,
                    'avg_rating'       => $e->ratings_avg_rating ? round($e->ratings_avg_rating, 2) : '-',
                ];
            });

        $totalEvents    = $events->count();
        $publishedEvents = $events->filter(fn ($e) => strtolower($e['status']) === 'published')->count();
        $totalRegistered = $events->sum('total_registered');
        $totalAttended   = $events->sum('attended_count');

        $monthLabel  = $this->getMonthLabel($month, $year);
        $companyName = $user->name;
        $generatedAt = now()->format('d M Y H:i');

        $data = compact(
            'companyName', 'monthLabel', 'generatedAt', 'events',
            'totalEvents', 'publishedEvents', 'totalRegistered', 'totalAttended'
        );

        if ($format === 'excel') {
            return $this->exportEventsExcel($data, $monthLabel, $companyName);
        }

        $pdf = Pdf::loadView('reports.events', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'Laporan-Event-' . str_replace(' ', '-', $monthLabel) . '.pdf';
        return $pdf->download($filename);
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────
    private function getMonthLabel(string $month, string $year): string
    {
        if ($month === 'all') {
            return 'Semua-Periode-' . $year;
        }
        $months = [
            '1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        return ($months[$month] ?? $month) . '-' . $year;
    }

    private function exportRecruitmentExcel(array $data, string $monthLabel, string $companyName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'Laporan-Rekrutmen-' . str_replace(' ', '-', $monthLabel) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel agar bisa baca UTF-8
            fputs($out, "\xEF\xBB\xBF");

            // Header info
            fputcsv($out, ['LAPORAN REKRUTMEN - ' . $data['companyName']]);
            fputcsv($out, ['Periode: ' . $data['monthLabel']]);
            fputcsv($out, ['Dibuat pada: ' . $data['generatedAt']]);
            fputcsv($out, []);

            // Summary
            fputcsv($out, ['RINGKASAN']);
            fputcsv($out, ['Total Pelamar',     $data['totalApplicants']]);
            fputcsv($out, ['Pelamar Diproses',  $data['processedApplicants']]);
            fputcsv($out, ['Diterima (Lolos)',  $data['acceptedApplicants']]);
            fputcsv($out, ['Wawancara',         $data['interviewApplicants']]);
            fputcsv($out, ['Tidak Lolos',       $data['rejectedApplicants']]);
            fputcsv($out, []);

            // Detail per lowongan
            fputcsv($out, ['DETAIL PER LOWONGAN']);
            fputcsv($out, ['No', 'Nama Lowongan', 'Status', 'Dipublikasi', 'Kuota', 'Total Pelamar', 'Wawancara', 'Diterima', 'Tidak Lolos', '% Keterisian']);

            foreach ($data['internshipsData'] as $index => $item) {
                fputcsv($out, [
                    $index + 1,
                    $item['title'],
                    $item['status'],
                    $item['is_published'],
                    $item['quota'],
                    $item['applicants_count'],
                    $item['wawancara_count'],
                    $item['lolos_count'],
                    $item['tidak_lolos_count'],
                    $item['quota_percentage'] . '%',
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    private function exportEventsExcel(array $data, string $monthLabel, string $companyName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'Laporan-Event-' . str_replace(' ', '-', $monthLabel) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['LAPORAN EVENT - ' . $data['companyName']]);
            fputcsv($out, ['Periode: ' . $data['monthLabel']]);
            fputcsv($out, ['Dibuat pada: ' . $data['generatedAt']]);
            fputcsv($out, []);

            fputcsv($out, ['RINGKASAN']);
            fputcsv($out, ['Total Event',         $data['totalEvents']]);
            fputcsv($out, ['Event Published',     $data['publishedEvents']]);
            fputcsv($out, ['Total Pendaftar',     $data['totalRegistered']]);
            fputcsv($out, ['Total Hadir',         $data['totalAttended']]);
            fputcsv($out, []);

            fputcsv($out, ['DETAIL EVENT']);
            fputcsv($out, ['No', 'Judul Event', 'Kategori', 'Tipe', 'Tanggal', 'Lokasi', 'Status', 'Maks. Peserta', 'Terdaftar', 'Hadir', 'Batal', 'Avg. Rating']);

            foreach ($data['events'] as $index => $event) {
                fputcsv($out, [
                    $index + 1,
                    $event['title'],
                    $event['category'],
                    $event['type'],
                    $event['date'],
                    $event['location'],
                    $event['status'],
                    $event['max_participants'],
                    $event['total_registered'],
                    $event['attended_count'],
                    $event['cancelled_count'],
                    $event['avg_rating'],
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }
}
