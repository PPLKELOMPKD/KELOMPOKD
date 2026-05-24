<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InternshipController extends Controller
{
    public function index(): Response
    {
        $applied_ids = auth()->user()->applications()->pluck('internship_id')->toArray();

        return Inertia::render('Internships/Index', [
            'internships' => Internship::query()
                ->where('is_published', true)
                ->orderBy('deadline_at')
                ->get(),

            'applieds_id' => $applied_ids
        ]);
    }

    public function show(Internship $internship): Response {
        $hasApplied = auth()->check() 
            ? auth()->user()->applications()->where('internship_id', $internship->id)->exists()
            : false;

        // Get related internships (same company or same location, excluding current)
        $relatedInternships = Internship::where('is_published', true)
            ->where('id', '!=', $internship->id)
            ->where(function ($query) use ($internship) {
                $query->where('company_name', $internship->company_name)
                      ->orWhere('location', $internship->location);
            })
            ->limit(3)
            ->get();

        return Inertia::render('Internships/Show', [
            'internship' => $internship,
            'hasApplied' => $hasApplied,
            'relatedInternships' => $relatedInternships,
        ]);
    }

    public function lowongan(Request $request)
    {
        $internships = Internship::where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $recommendedInternships = [];
        $hasSkills = false;

        if (auth()->check() && auth()->user()->isMahasiswa()) {
            $user = auth()->user();
            
            // Ambil ID lowongan yang sudah dilamar agar tidak direkomendasikan lagi
            $applied_ids = $user->applications()->pluck('internship_id')->toArray();
            
            // Ambil nama skill dan jadikan huruf kecil
            $userSkills = $user->skills()->pluck('name')->map(fn($skill) => strtolower($skill))->toArray();
            $hasSkills = count($userSkills) > 0;

            if ($hasSkills) {
                $recommendedInternships = $internships->map(function ($internship) use ($userSkills) {
                    $requirements = strtolower($internship->requirements ?? '');
                    $matchCount = 0;
                    
                    foreach ($userSkills as $skill) {
                        if (str_contains($requirements, $skill)) {
                            $matchCount++;
                        }
                    }
                    
                    $internship->match_count = $matchCount;
                    return $internship;
                })->filter(function ($internship) use ($applied_ids) {
                    // Filter: minimal 1 skill cocok DAN belum pernah dilamar
                    return $internship->match_count > 0 && !in_array($internship->id, $applied_ids);
                })->sortByDesc('match_count')->take(3)->values();
            }
        }

        // Extract unique values for filter dropdowns
        $companies = $internships->pluck('company_name')->unique()->filter()->values()->toArray();
        $locations = $internships->pluck('location')->unique()->filter()->values()->toArray();
        $workTypeOrder = [
            'Magang',
            'Magang WFO',
            'Magang WFH',
            'Magang Hybrid',
            'Full-time',
            'Part-time',
        ];

        $workTypeRanks = array_flip($workTypeOrder);

        $workTypes = $internships->pluck('work_type')
            ->unique()
            ->filter()
            ->sortBy(fn ($type) => $workTypeRanks[$type] ?? count($workTypeOrder))
            ->values()
            ->toArray();

        $educationLevels = ['D3', 'D4', 'S1', 'S2', 'S3'];
        $salaryRanges = $internships->pluck('salary_range')->unique()->filter()->values()->toArray();

        return Inertia::render('Features/Lowongan', [
            'internships' => $internships,
            'filterOptions' => [
                'companies' => $companies,
                'locations' => $locations,
                'workTypes' => $workTypes,
                'educationLevels' => $educationLevels,
                'salaryRanges' => $salaryRanges,
            ],
            'recommendedInternships' => $recommendedInternships,
            'hasSkills' => $hasSkills
        ]);
    }
}
