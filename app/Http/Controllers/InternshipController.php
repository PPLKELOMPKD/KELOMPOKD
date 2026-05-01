<?php

namespace App\Http\Controllers;

use App\Models\Internship;
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
        $hasApplied = auth()->user()->applications()
            ->where('internship_id', $internship->id)
            ->exists();

        return Inertia::render('Internships/Show', [
            'internship' => $internship,
            'hasApplied' => $hasApplied 
        ]);
    }
}
