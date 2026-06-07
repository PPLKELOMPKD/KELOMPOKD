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
                ->with('error', 'Silakan login terlebih dahulu untuk mendaftar.');
        }

        // Role check: hanya mahasiswa
        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat mendaftar ke event.');
        }

        // TC-07: Event harus berstatus published DAN sudah disetujui admin
        if ($event->status !== 'published' || $event->moderation_status !== 'approved') {
            return back()->with('error', 'Event tidak tersedia untuk pendaftaran.');
        }

        // Precondition: tanggal event belum terlewati
        if (Carbon::parse($event->date)->startOfDay()->lt(Carbon::today())) {
            return back()->with('error', 'Event sudah berakhir dan tidak dapat didaftari.');
        }

        // TC-05: Cek kuota (jumlah registrasi aktif = max_participants)
        $activeCount = $event->registrations()
            ->whereIn('status', ['registered', 'attended'])
            ->count();

        if ($event->max_participants !== null && $activeCount >= $event->max_participants) {
            return back()->with('error', 'Maaf, kuota event sudah penuh.');
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

                return back()->with('success', 'Pendaftaran berhasil! Kamu sekarang terdaftar di event ini.');
            }

            return back()->with('error', 'Anda sudah terdaftar di event ini.');
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

        return back()->with('success', 'Pendaftaran berhasil! Kamu sekarang terdaftar di event ini.');
    }

    /**
     * A4: Batalkan pendaftaran event.
     */
    public function destroy(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'mahasiswa') {
            return back()->with('error', 'Aksi tidak diizinkan.');
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', 'registered')
            ->first();

        if (!$registration) {
            return back()->with('error', 'Pendaftaran tidak ditemukan atau sudah dibatalkan.');
        }

        $registration->update(['status' => 'cancelled']);

        \App\Services\ActivityLogger::log(
            'Batalkan Event',
            "Membatalkan pendaftaran dari event: {$event->title}",
            'event'
        );

        return back()->with('success', 'Pendaftaran berhasil dibatalkan.');
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
                    $isCompleted = $event->status === 'completed' ||
                        Carbon::parse($event->date)->startOfDay()->lt(Carbon::today());

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
