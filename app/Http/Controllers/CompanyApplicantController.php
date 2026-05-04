<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompanyApplicantController extends Controller
{
    /**
     * Display the full list of applicants for the company's internships.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $internshipIds = $user->internships()->pluck('id');

        $query = Application::whereIn('internship_id', $internshipIds)
            ->with(['user.mahasiswaProfile', 'internship']);

        // ── Filter by status ──
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // ── Search by name or email ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ── Sorting ──
        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('direction', 'desc');
        $allowedSorts = ['created_at', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $paginated = $query->paginate(15)->withQueryString();

        $statusColorMap = [
            'submitted'       => 'blue',
            'menunggu ulasan' => 'blue',
            'wawancara'       => 'purple',
            'lolos'           => 'green',
            'tidak lolos'     => 'red',
        ];

        $applicants = collect($paginated->items())->map(function ($app) use ($statusColorMap) {
            $applicant = $app->user;
            $profile   = $applicant?->mahasiswaProfile;
            $name      = $applicant?->name ?? 'Pelamar';
            $words     = explode(' ', $name);
            $initials  = strtoupper(
                (substr($words[0] ?? '', 0, 1)) . (substr($words[1] ?? '', 0, 1))
            );
            $status = strtolower($app->status ?? 'menunggu ulasan');

            return [
                'id'          => $app->id,
                'name'        => $name,
                'email'       => $applicant?->email ?? '-',
                'initials'    => $initials,
                'position'    => $app->internship?->title ?? '-',
                'university'  => $profile?->university ?? '-',
                'major'       => $profile?->department ?? '-',
                'gpa'         => $profile?->gpa ?? '-',
                'phone'       => $profile?->phone ?? '-',
                'bio'         => $profile?->bio ?? '-',
                'date'        => optional($app->created_at)->format('d M Y'),
                'dateRelative'=> optional($app->created_at)->diffForHumans(),
                'status'      => ucwords($status),
                'statusRaw'   => $status,
                'statusColor' => $statusColorMap[$status] ?? 'blue',
            ];
        });

        // ── Stats for the header ──
        $allAppsQuery = Application::whereIn('internship_id', $internshipIds);
        $statsData = [
            'total'           => (clone $allAppsQuery)->count(),
            'menunggu_ulasan' => (clone $allAppsQuery)->where('status', 'menunggu ulasan')->count(),
            'submitted'       => (clone $allAppsQuery)->where('status', 'submitted')->count(),
            'wawancara'       => (clone $allAppsQuery)->where('status', 'wawancara')->count(),
            'lolos'           => (clone $allAppsQuery)->where('status', 'lolos')->count(),
            'tidak_lolos'     => (clone $allAppsQuery)->where('status', 'tidak lolos')->count(),
        ];

        // Internship list for filter dropdown
        $internships = $user->internships()
            ->select('id', 'title')
            ->orderBy('title')
            ->get()
            ->map(fn ($i) => ['id' => $i->id, 'title' => $i->title]);

        return Inertia::render('Company/Applicants/Index', [
            'applicants'  => $applicants,
            'pagination'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'links'        => $paginated->linkCollection()->toArray(),
            ],
            'stats'       => $statsData,
            'internships' => $internships,
            'filters'     => [
                'status'    => $request->get('status', 'all'),
                'search'    => $request->get('search', ''),
                'sort'      => $request->get('sort', 'created_at'),
                'direction' => $request->get('direction', 'desc'),
            ],
        ]);
    }

    /**
     * Display the detail of a specific applicant.
     */
    public function show(Request $request, Application $application): Response
    {
        $user = $request->user();
        $internshipIds = $user->internships()->pluck('id');

        // Ensure the application belongs to one of this company's internships
        abort_unless($internshipIds->contains($application->internship_id), 403);

        $application->load(['user.mahasiswaProfile', 'user.skills', 'internship']);

        $applicant = $application->user;
        $profile   = $applicant?->mahasiswaProfile;
        $name      = $applicant?->name ?? 'Pelamar';
        $words     = explode(' ', $name);
        $initials  = strtoupper(
            (substr($words[0] ?? '', 0, 1)) . (substr($words[1] ?? '', 0, 1))
        );

        $statusColorMap = [
            'submitted'       => 'blue',
            'menunggu ulasan' => 'blue',
            'wawancara'       => 'purple',
            'lolos'           => 'green',
            'tidak lolos'     => 'red',
        ];
        $status = strtolower($application->status ?? 'menunggu ulasan');

        // Other applications by this student to this company's internships
        $otherApplications = Application::where('user_id', $applicant->id)
            ->whereIn('internship_id', $internshipIds)
            ->where('id', '!=', $application->id)
            ->with('internship')
            ->get()
            ->map(fn ($a) => [
                'id'       => $a->id,
                'position' => $a->internship?->title ?? '-',
                'status'   => ucwords(strtolower($a->status)),
                'date'     => optional($a->created_at)->format('d M Y'),
            ]);

        $skills = $applicant->skills->map(fn ($s) => [
            'id'   => $s->id,
            'name' => $s->name,
        ]);

        return Inertia::render('Company/Applicants/Show', [
            'applicant' => [
                'id'          => $application->id,
                'name'        => $name,
                'email'       => $applicant?->email ?? '-',
                'initials'    => $initials,
                'phone'       => $profile?->phone ?? '-',
                'university'  => $profile?->university ?? '-',
                'major'       => $profile?->department ?? '-',
                'studyProgram'=> $profile?->study_program ?? '-',
                'gpa'         => $profile?->gpa ?? '-',
                'location'    => $profile?->location ?? '-',
                'bio'         => $profile?->bio ?? '-',
                'position'    => $application->internship?->title ?? '-',
                'internshipId'=> $application->internship?->id,
                'companyName' => $application->internship?->company_name ?? '-',
                'date'        => optional($application->created_at)->format('d M Y'),
                'dateRelative'=> optional($application->created_at)->diffForHumans(),
                'updatedAt'   => optional($application->updated_at)->format('d M Y, H:i'),
                'status'      => ucwords($status),
                'statusRaw'   => $status,
                'statusColor' => $statusColorMap[$status] ?? 'blue',
                'skills'      => $skills,
                'otherApplications' => $otherApplications,
            ],
        ]);
    }

    /**
     * Update the status of an application (accept, reject, interview, etc.).
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $user = $request->user();
        $internshipIds = $user->internships()->pluck('id');

        abort_unless($internshipIds->contains($application->internship_id), 403);

        $request->validate([
            'status' => ['required', 'string', 'in:menunggu ulasan,wawancara,lolos,tidak lolos'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $application->status;
        $newStatus = $request->status;

        $application->update([
            'status' => $newStatus,
        ]);

        // ── Send notification to the student ──
        $statusLabels = [
            'menunggu ulasan' => 'Menunggu Ulasan',
            'wawancara'       => 'Wawancara',
            'lolos'           => 'Diterima',
            'tidak lolos'     => 'Ditolak',
        ];

        $internshipTitle = $application->internship?->title ?? 'posisi magang';
        $companyName = $user->name;
        $statusLabel = $statusLabels[$newStatus] ?? ucwords($newStatus);

        // Determine notification type
        $notifType = match ($newStatus) {
            'lolos'       => 'check',
            'tidak lolos' => 'user',
            'wawancara'   => 'calendar',
            default       => 'user',
        };

        // Determine notification title and message
        $notifTitle = match ($newStatus) {
            'lolos'       => '🎉 Selamat! Anda Diterima',
            'tidak lolos' => 'Update Status Lamaran',
            'wawancara'   => '📋 Undangan Wawancara',
            default       => 'Update Status Lamaran',
        };

        $notifMessage = match ($newStatus) {
            'lolos'       => "Selamat! Lamaran Anda untuk posisi {$internshipTitle} di {$companyName} telah diterima. Silakan cek email Anda untuk informasi lebih lanjut.",
            'tidak lolos' => "Mohon maaf, lamaran Anda untuk posisi {$internshipTitle} di {$companyName} belum dapat kami terima saat ini. Tetap semangat dan jangan ragu untuk melamar posisi lainnya!",
            'wawancara'   => "Anda diundang untuk wawancara pada posisi {$internshipTitle} di {$companyName}. Silakan periksa email Anda untuk detail jadwal wawancara.",
            default       => "Status lamaran Anda untuk posisi {$internshipTitle} di {$companyName} telah diperbarui menjadi {$statusLabel}.",
        };

        Notification::create([
            'user_id' => $application->user_id,
            'title'   => $notifTitle,
            'message' => $notifMessage,
            'type'    => $notifType,
        ]);

        $redirectRoute = $request->get('redirect', 'index');

        $flashMessage = "Status pelamar berhasil diubah menjadi \"{$statusLabel}\".";

        if ($redirectRoute === 'show') {
            return redirect()
                ->route('perusahaan.applicants.show', $application->id)
                ->with('success', $flashMessage);
        }

        return redirect()
            ->route('perusahaan.applicants.index')
            ->with('success', $flashMessage);
    }
}
