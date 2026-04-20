<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Inertia\Inertia;
use Inertia\Response;

class InternshipController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Internships/Index', [
            'internships' => Internship::query()
                ->where('is_published', true)
                ->orderBy('deadline_at')
                ->get(),
        ]);
    }
}
