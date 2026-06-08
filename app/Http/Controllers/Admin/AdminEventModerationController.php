<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminEventModerationController extends Controller
{
    /**
     * Tampilkan daftar event untuk moderasi.
     */
    public function index(Request $request): Response
    {
        $statusFilter = $request->query('status', 'pending');
        $search       = $request->query('search', '');

        $query = Event::with(['company:id,name,email'])
            ->latest();

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $query->where('moderation_status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $events = $query->paginate(15)->withQueryString()->through(function ($event) {
            $registrationCount = $event->registrations()->whereIn('status', ['registered', 'attended'])->count();
            return [
                'id'                => $event->id,
                'title'             => $event->title,
                'category'          => $event->category,
                'date'              => $event->date,
                'start_time'        => $event->start_time,
                'end_time'          => $event->end_time,
                'location'          => $event->location,
                'type'              => $event->type,
                'status'            => $event->status,
                'max_participants'  => $event->max_participants,
                'moderation_status' => $event->moderation_status,
                'rejection_reason'  => $event->rejection_reason,
                'moderated_at'      => optional($event->moderated_at)->format('d M Y H:i'),
                'created_at'        => optional($event->created_at)->format('d M Y'),
                'registrations_count' => $registrationCount,
                'company'           => $event->company ? [
                    'id'    => $event->company->id,
                    'name'  => $event->company->name,
                    'email' => $event->company->email,
                ] : null,
            ];
        });

        $counts = [
            'pending'  => Event::where('moderation_status', 'pending')->count(),
            'approved' => Event::where('moderation_status', 'approved')->count(),
            'rejected' => Event::where('moderation_status', 'rejected')->count(),
            'all'      => Event::count(),
        ];

        return Inertia::render('Admin/Events/Index', [
            'events'       => $events,
            'statusFilter' => $statusFilter,
            'search'       => $search,
            'counts'       => $counts,
        ]);
    }

    /**
     * Setujui event — ubah status: approved, status: published.
     */
    public function approve(Request $request, Event $event)
    {
        $event->update([
            'moderation_status' => 'approved',
            'status'            => 'published',
            'rejection_reason'  => null,
            'moderated_by'      => auth()->id(),
            'moderated_at'      => now(),
        ]);

        ActivityLogger::log(
            'Menyetujui Event',
            "Menyetujui event \"{$event->title}\" dari " . ($event->company->name ?? 'Perusahaan'),
            'event'
        );

        return back()->with('success', "Event \"{$event->title}\" has been successfully approved and is now live.");
    }

    /**
     * Tolak / Hapus event — wajib isi alasan.
     */
    public function reject(Request $request, Event $event)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ], [
            'rejection_reason.required' => 'Rejection reason is required.',
            'rejection_reason.min'      => 'Rejection reason must be at least 10 characters.',
            'rejection_reason.max'      => 'Rejection reason cannot exceed 1000 characters.',
        ]);

        $event->update([
            'moderation_status' => 'rejected',
            'status'            => 'draft',
            'rejection_reason'  => $validated['rejection_reason'],
            'moderated_by'      => auth()->id(),
            'moderated_at'      => now(),
        ]);

        ActivityLogger::log(
            'Menolak Event',
            "Menolak event \"{$event->title}\" dari " . ($event->company->name ?? 'Perusahaan') . ": {$validated['rejection_reason']}",
            'event'
        );

        return back()->with('success', "Event \"{$event->title}\" has been successfully rejected.");
    }

    /**
     * Hapus permanen event yang ditolak (opsional, hanya jika tidak ada peserta).
     */
    public function destroy(Event $event)
    {
        if ($event->registrations()->exists()) {
            return back()->with('error', 'Event cannot be deleted because it already has registered participants.');
        }

        $title = $event->title;
        $event->delete();

        ActivityLogger::log('Menghapus Event', "Menghapus event \"{$title}\" dari panel admin", 'event');

        return back()->with('success', "Event \"{$title}\" has been successfully deleted.");
    }
}
