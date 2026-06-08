<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InternshipModerationController extends Controller
{
    /**
     * Tampilkan daftar lowongan untuk moderasi dengan filter status.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'all');
        $search       = $request->query('search', '');

        $query = Internship::with(['company:id,name,email', 'moderator:id,name'])
            ->latest();

        // Filter status
        if (in_array($statusFilter, ['pending', 'approved', 'rejected', 'closed'])) {
            $query->where('moderation_status', $statusFilter);
        }

        // Search judul atau nama perusahaan
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $internships = $query->paginate(15)->withQueryString();

        // Count per status untuk badge
        $counts = [
            'pending'  => Internship::where('moderation_status', 'pending')->count(),
            'approved' => Internship::where('moderation_status', 'approved')->count(),
            'rejected' => Internship::where('moderation_status', 'rejected')->count(),
            'closed'   => Internship::where('moderation_status', 'closed')->count(),
            'all'      => Internship::count(),
        ];

        return Inertia::render('Admin/Internships/Index', [
            'internships'  => $internships,
            'statusFilter' => $statusFilter,
            'search'       => $search,
            'counts'       => $counts,
        ]);
    }

    /**
     * Tampilkan detail satu lowongan untuk review mendalam.
     */
    public function show(Internship $internship): Response
    {
        $internship->load(['company:id,name,email', 'moderator:id,name', 'applications']);

        return Inertia::render('Admin/Internships/Show', [
            'internship' => $internship,
        ]);
    }

    /**
     * Setujui lowongan — status: approved, is_published: true.
     */
    public function approve(Request $request, Internship $internship)
    {
        $internship->update([
            'moderation_status' => 'approved',
            'is_published'      => true,
            'rejection_reason'  => null,
            'moderated_by'      => auth()->id(),
            'moderated_at'      => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'Menyetujui Lowongan',
            "Menyetujui lowongan \"{$internship->title}\" dari {$internship->company_name}",
            'moderasi'
        );

        return back()->with('success', "Job listing \"{$internship->title}\" has been successfully approved and is now live.");
    }

    /**
     * Tolak lowongan — wajib isi alasan penolakan.
     */
    public function reject(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ], [
            'rejection_reason.required' => 'Rejection reason is required.',
            'rejection_reason.min'      => 'Rejection reason must be at least 10 characters.',
            'rejection_reason.max'      => 'Rejection reason cannot exceed 1000 characters.',
        ]);

        $internship->update([
            'moderation_status' => 'rejected',
            'is_published'      => false,
            'rejection_reason'  => $validated['rejection_reason'],
            'moderated_by'      => auth()->id(),
            'moderated_at'      => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'Menolak Lowongan',
            "Menolak lowongan \"{$internship->title}\" dari {$internship->company_name}: {$validated['rejection_reason']}",
            'moderasi'
        );

        return back()->with('success', "Job listing \"{$internship->title}\" has been successfully rejected.");
    }

    /**
     * Takedown lowongan yang sedang tayang.
     * Status diubah menjadi 'closed' — perusahaan TIDAK bisa edit/resubmit.
     * Hanya bisa melihat alasan takedown.
     */
    public function takedown(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ], [
            'rejection_reason.required' => 'Takedown reason is required.',
            'rejection_reason.min'      => 'Takedown reason must be at least 10 characters.',
        ]);

        $internship->update([
            'moderation_status' => 'closed',   // beda dari 'rejected'
            'is_published'      => false,
            'rejection_reason'  => $validated['rejection_reason'],
            'moderated_by'      => auth()->id(),
            'moderated_at'      => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'Mencabut Lowongan',
            "Mencabut (takedown) lowongan \"{$internship->title}\" dari {$internship->company_name}: {$validated['rejection_reason']}",
            'moderasi'
        );

        return back()->with('success', "Job listing \"{$internship->title}\" has been successfully taken down. Status changed to Closed.");
    }

    /**
     * Tampilkan kalender aktivitas lowongan.
     */
    public function calendar(Request $request): Response
    {
        $internships = Internship::select([
            'id',
            'title',
            'company_id',
            'company_name',
            'company_logo',
            'location',
            'work_type',
            'moderation_status',
            'is_published',
            'created_at',
            'moderated_at',
            'deadline_at'
        ])->latest()->get();

        return Inertia::render('Admin/Internships/Calendar', [
            'internships' => $internships
        ]);
    }
}
