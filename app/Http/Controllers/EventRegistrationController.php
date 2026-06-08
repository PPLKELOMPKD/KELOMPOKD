<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventRegistrationController extends Controller
{
    /**
     * Daftar ke sebuah event (mahasiswa only).
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        // A1: Pastikan user sudah login (dicek via middleware, tapi double-check)
        if (!$user) {
            return redirect()->route('login', ['role' => 'mahasiswa'])
                ->with('error', 'Please log in first to register.');
        }

        // Role check: hanya mahasiswa
        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Only students can register for events.');
        }

        // TC-07: Event harus berstatus published DAN sudah disetujui admin
        if ($event->status !== 'published' || $event->moderation_status !== 'approved') {
            return back()->with('error', 'This event is not available for registration.');
        }

        // Precondition: event belum dimulai (tanggal + start_time belum terlewati)
        $eventStartDateTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->start_time);
        $eventEndDateTime   = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
        $now = Carbon::now();

        if ($now->gte($eventEndDateTime)) {
            return back()->with('error', 'The event has ended and cannot be registered.');
        }

        if ($now->gte($eventStartDateTime)) {
            return back()->with('error', 'The event has started and cannot be registered.');
        }

        // TC-05: Cek kuota (jumlah registrasi aktif = max_participants)
        $activeCount = $event->registrations()
            ->whereIn('status', ['registered', 'attended'])
            ->count();

        if ($event->max_participants !== null && $activeCount >= $event->max_participants) {
            return back()->with('error', 'Sorry, the event quota is full.');
        }

        // TC-06: Cek duplikat pendaftaran
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'cancelled') {
                // Boleh daftar ulang jika sebelumnya dibatalkan
                $existing->update([
                    'status'        => 'registered',
                    'registered_at' => now(),
                ]);

                \App\Services\ActivityLogger::log(
                    'Daftar Ulang Event',
                    "Mendaftar ulang ke event: {$event->title}",
                    'event'
                );

                return back()->with('success', 'Registration successful! You are now registered for this event.');
            }

            return back()->with('error', 'You are already registered for this event.');
        }

        // TC-03: Buat record baru
        EventRegistration::create([
            'event_id'      => $event->id,
            'user_id'       => $user->id,
            'status'        => 'registered',
            'registered_at' => now(),
        ]);

        \App\Services\ActivityLogger::log(
            'Daftar Event',
            "Mendaftar ke event: {$event->title}",
            'event'
        );

        return back()->with('success', 'Registration successful! You are now registered for this event.');
    }

    /**
     * A4: Batalkan pendaftaran event.
     */
    public function destroy(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'mahasiswa') {
            return back()->with('error', 'Action not authorized.');
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', 'registered')
            ->first();

        if (!$registration) {
            return back()->with('error', 'Registration not found or has been cancelled.');
        }

        $registration->update(['status' => 'cancelled']);

        \App\Services\ActivityLogger::log(
            'Batalkan Event',
            "Membatalkan pendaftaran dari event: {$event->title}",
            'event'
        );

        return back()->with('success', 'Registration cancelled successfully.');
    }

    public function myEvents(Request $request)
    {
        $user = $request->user();

        $registrations = EventRegistration::with(['event', 'event.company', 'event.company.perusahaanProfile'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($reg) use ($user) {
                $event = $reg->event;
                $activeCount = 0;
                if ($event) {
                    $activeCount = $event->registrations()
                        ->whereIn('status', ['registered', 'attended'])
                        ->count();
                }

                // Rating data
                $avgRating   = null;
                $ratingCount = 0;
                $userRating  = null;
                $isCompleted = false;

                if ($event) {
                    $avgRating   = round($event->ratings()->avg('rating'), 2) ?: null;
                    $ratingCount = $event->ratings()->count();
                    $eventEndDateTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
                    $isCompleted = $event->status === 'completed' || Carbon::now()->gte($eventEndDateTime);

                    $ur = $event->ratings()->where('user_id', $user->id)->first();
                    if ($ur) {
                        $userRating = ['rating' => $ur->rating, 'comment' => $ur->comment];
                    }
                }

                return [
                    'id'                   => $reg->id,
                    'status'               => $reg->status,
                    'registered_at'        => $reg->registered_at,
                    'event'                => $event ? [
                        'id'               => $event->id,
                        'title'            => $event->title,
                        'category'         => $event->category,
                        'description'      => $event->description,
                        'date'             => $event->date,
                        'start_time'       => $event->start_time,
                        'end_time'         => $event->end_time,
                        'location'         => $event->location,
                        'type'             => $event->type,
                        'status'           => $event->status,
                        'max_participants'  => $event->max_participants,
                        'active_count'     => $activeCount,
                        'is_completed'     => $isCompleted,
                        'avg_rating'       => $avgRating,
                        'rating_count'     => $ratingCount,
                        'user_rating'      => $userRating,
                        'company'          => $event->company ? [
                            'id'   => $event->company->id,
                            'name' => $event->company->name,
                        ] : null,
                    ] : null,
                ];
            });

        return Inertia::render('Features/MyEvents', [
            'registrations' => $registrations,
        ]);
    }
}
