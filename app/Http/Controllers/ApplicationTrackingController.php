<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $applications = $user->applications()
            ->with('internship')
            ->latest()
            ->get()
            ->map(fn ($application) => [
                'id' => $application->id,
                'status' => $application->status,
                'applied_at' => $application->created_at->format('d M Y, H:i'),
                'applied_at_relative' => $application->created_at->diffForHumans(),
                'updated_at' => $application->updated_at->format('d M Y, H:i'),
                'internship' => [
                    'id' => $application->internship->id,
                    'title' => $application->internship->title,
                    'company_name' => $application->internship->company_name,
                    'location' => $application->internship->location,
                    'deadline_at' => optional($application->internship->deadline_at)->format('d M Y'),
                    'is_published' => $application->internship->is_published,
                ],
            ]);

        $statusCounts = [
            'total' => $applications->count(),
            'submitted' => $applications->where('status', 'submitted')->count(),
            'reviewed' => $applications->where('status', 'reviewed')->count(),
            'interview' => $applications->where('status', 'interview')->count(),
            'accepted' => $applications->where('status', 'accepted')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Applications/Index', [
            'applications' => $applications->values(),
            'statusCounts' => $statusCounts,
            'filters' => [
                'status' => $request->query('status', 'all'),
            ],
        ]);
    }
}
