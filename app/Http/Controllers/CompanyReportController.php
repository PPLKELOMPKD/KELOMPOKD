<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Month filter (value: 'all' or '1' to '12')
        $month = $request->input('month', 'all');
        $year = $request->input('year', date('Y'));

        // Base query for this company's internships
        $companyInternshipsQuery = Internship::where('company_id', $user->id);

        // Filter internships by month of creation if month filter is active
        $filteredInternshipsQuery = clone $companyInternshipsQuery;
        if ($month !== 'all') {
            $filteredInternshipsQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        // Lowongan Aktif: published, approved, and non-expired
        $activeInternshipsCountQuery = (clone $filteredInternshipsQuery)
            ->where('is_published', true)
            ->where('moderation_status', 'approved');
            
        if ($month === 'all') {
            $activeInternshipsCountQuery = $activeInternshipsCountQuery->where('deadline_at', '>=', now());
        }
        $activeInternshipsCount = $activeInternshipsCountQuery->count();

        // Get all internship IDs for this company to query applications
        $allInternshipIds = (clone $companyInternshipsQuery)->pluck('id');

        // Base query for applications belonging to this company's internships
        $applicationsQuery = Application::whereIn('internship_id', $allInternshipIds);

        // Filter applications by month if month filter is active
        if ($month !== 'all') {
            $applicationsQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        // Total Pelamar
        $totalApplicants = (clone $applicationsQuery)->count();

        // Pelamar Diproses (status is wawancara, lolos, atau tidak lolos)
        $processedApplicants = (clone $applicationsQuery)
            ->whereIn('status', ['wawancara', 'lolos', 'tidak lolos'])
            ->count();

        // Data for comparison per internship
        // We will fetch internships and their application counts for the selected month/year.
        $internshipsData = (clone $companyInternshipsQuery)
            ->withCount(['applications' => function ($query) use ($month, $year) {
                if ($month !== 'all') {
                    $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
                }
            }])
            ->withCount(['applications as accepted_count' => function ($query) use ($month, $year) {
                $query->where('status', 'lolos');
                if ($month !== 'all') {
                    $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
                }
            }])
            ->get()
            ->map(function ($internship) {
                $totalApps = $internship->applications_count;
                $quota = $internship->quota ?: 1;
                
                // TC-02 states: "Sistem menampilkan angka persentase keterisian kuota yang akurat hasil pembagian pelamar dengan kuota"
                // So: total_applicants / quota * 100
                $percentage = $quota > 0 ? round(($totalApps / $quota) * 100) : 0;
                
                return [
                    'id' => $internship->id,
                    'title' => $internship->title,
                    'quota' => $internship->quota,
                    'applicants_count' => $totalApps,
                    'accepted_count' => $internship->accepted_count,
                    'quota_percentage' => $percentage,
                    'is_published' => $internship->is_published,
                    'status' => $internship->deadline_at && $internship->deadline_at->isPast() ? 'Expired' : 'Aktif',
                ];
            });

        return Inertia::render('Company/Reports/Index', [
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
            'stats' => [
                'active_internships' => $activeInternshipsCount,
                'total_applicants' => $totalApplicants,
                'processed_applicants' => $processedApplicants,
            ],
            'internships_data' => $internshipsData,
        ]);
    }
}
