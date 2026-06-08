<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyEventController extends Controller
{
    public function index()
    {
        $events = Event::where('company_id', auth()->id())
            ->latest()
            ->get(['id', 'title', 'category', 'date', 'start_time', 'end_time', 'location', 'type', 'status', 'max_participants', 'moderation_status', 'rejection_reason', 'created_at']);
        return Inertia::render('Company/Events/Index', [
            'events' => $events
        ]);
    }

    public function create()
    {
        return Inertia::render('Company/Events/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'category'         => 'required|in:webinar,workshop,seminar',
            'description'      => 'required|string',
            'date'             => 'required|date|after_or_equal:today',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'location'         => 'required|string|max:255',
            'type'             => 'required|in:online,offline',
            'max_participants' => 'required|integer|min:1',
        ], [
            'title.required'            => 'Title is required.',
            'description.required'      => 'Description is required.',
            'date.required'             => 'Event date is required.',
            'date.after_or_equal'       => 'Event date cannot be earlier than today.',
            'start_time.required'       => 'Start time is required.',
            'end_time.required'         => 'End time is required.',
            'end_time.after'            => 'End time must be after start time.',
            'location.required'         => 'Location is required.',
            'type.required'             => 'Event type is required.',
            'max_participants.required' => 'Maximum participants is required.',
            'max_participants.min'      => 'Maximum participants must be greater than 0.',
        ]);

        // Extra check: if the event is scheduled for today, times must not have already passed.
        $today = Carbon::today()->toDateString();
        if ($validated['date'] === $today) {
            $now = Carbon::now()->format('H:i');

            if ($validated['start_time'] <= $now) {
                return back()->withErrors([
                    'start_time' => 'Start time cannot be in the past. Please select a future time.',
                ])->withInput();
            }

            if ($validated['end_time'] <= $now) {
                return back()->withErrors([
                    'end_time' => 'End time cannot be in the past.',
                ])->withInput();
            }
        }

        $validated['company_id']        = auth()->id();
        $validated['status']            = 'draft';    // akan diubah published setelah admin approve
        $validated['moderation_status'] = 'pending';  // menunggu persetujuan admin

        $event = Event::create($validated);

        \App\Services\ActivityLogger::log('Membuat Event', "Membuat event baru: {$event->title} (menunggu persetujuan admin)", 'event');

        return redirect()->route('perusahaan.events.index')->with('success', 'Event submitted successfully and is waiting for Admin approval.');
    }

    public function edit(Event $event)
    {
        // Ensure the company owns the event
        if ($event->company_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Company/Events/Form', [
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->company_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'category'         => 'required|in:webinar,workshop,seminar',
            'description'      => 'required|string',
            'date'             => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'location'         => 'required|string|max:255',
            'type'             => 'required|in:online,offline',
            'max_participants' => 'required|integer|min:1',
        ], [
            'title.required'            => 'Title is required.',
            'description.required'      => 'Description is required.',
            'date.required'             => 'Event date is required.',
            'start_time.required'       => 'Start time is required.',
            'end_time.required'         => 'End time is required.',
            'end_time.after'            => 'End time must be after start time.',
            'location.required'         => 'Location is required.',
            'type.required'             => 'Event type is required.',
            'max_participants.required' => 'Maximum participants is required.',
            'max_participants.min'      => 'Maximum participants must be greater than 0.',
        ]);

        // Jika event sudah disetujui dan ada perubahan konten, kembalikan ke pending
        if ($event->moderation_status === 'approved') {
            $validated['moderation_status'] = 'pending';
            $validated['status']            = 'draft';
            $validated['moderated_by']      = null;
            $validated['moderated_at']      = null;
            $validated['rejection_reason']  = null;
        }

        $event->update($validated);

        \App\Services\ActivityLogger::log('Memperbarui Event', "Memperbarui event: {$event->title}", 'event');

        $msg = isset($validated['moderation_status'])
            ? 'Event updated successfully and returned to pending Admin approval.'
            : 'Event updated successfully';

        return redirect()->route('perusahaan.events.index')->with('success', $msg);
    }

    public function destroy(Event $event)
    {
        if ($event->company_id !== auth()->id()) {
            abort(403);
        }

        // if there are participants, maybe we can't delete it or change status to cancelled.
        // For now, let's just delete it, or as we did for internships:
        if ($event->registrations()->exists()) {
            $event->update(['status' => 'completed']); // or draft/cancelled, the schema has draft, published, completed
            \App\Services\ActivityLogger::log('Menutup Event', "Menutup event: {$event->title} (memiliki peserta)", 'event');
            return redirect()->route('perusahaan.events.index')->with('error', 'Event cannot be deleted because it already has participants. Status changed to Completed.');
        }

        \App\Services\ActivityLogger::log('Menghapus Event', "Menghapus event: {$event->title}", 'event');
        $event->delete();

        return redirect()->route('perusahaan.events.index')->with('success', 'Event deleted successfully');
    }
}
