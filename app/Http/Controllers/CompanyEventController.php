<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyEventController extends Controller
{
    public function index()
    {
        $events = Event::where('company_id', auth()->id())
            ->latest()
            ->get();
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'type' => 'required|in:online,offline',
            'status' => 'required|in:draft,published,completed',
            'max_participants' => 'required|integer|min:1',
        ], [
            'title.required' => 'Judul wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'date.required' => 'Tanggal acara wajib diisi.',
            'date.after_or_equal' => 'Tanggal acara tidak boleh kurang dari hari ini.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'location.required' => 'Lokasi wajib diisi.',
            'type.required' => 'Tipe acara wajib diisi.',
            'status.required' => 'Status wajib diisi.',
            'max_participants.required' => 'Maksimal peserta wajib diisi.',
            'max_participants.min' => 'Maksimal peserta harus lebih dari 0.',
        ]);

        $validated['company_id'] = auth()->id();
        Event::create($validated);

        return redirect()->route('perusahaan.events.index')->with('success', 'Event Berhasil Ditambahkan');
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'type' => 'required|in:online,offline',
            'status' => 'required|in:draft,published,completed',
            'max_participants' => 'required|integer|min:1',
        ], [
            'title.required' => 'Judul wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'date.required' => 'Tanggal acara wajib diisi.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'location.required' => 'Lokasi wajib diisi.',
            'type.required' => 'Tipe acara wajib diisi.',
            'status.required' => 'Status wajib diisi.',
            'max_participants.required' => 'Maksimal peserta wajib diisi.',
            'max_participants.min' => 'Maksimal peserta harus lebih dari 0.',
        ]);

        $event->update($validated);

        return redirect()->route('perusahaan.events.index')->with('success', 'Event Berhasil Diperbarui');
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
            return redirect()->route('perusahaan.events.index')->with('error', 'Event tidak bisa dihapus karena sudah memiliki peserta. Status diubah menjadi Selesai.');
        }

        $event->delete();

        return redirect()->route('perusahaan.events.index')->with('success', 'Event Berhasil Dihapus');
    }
}
