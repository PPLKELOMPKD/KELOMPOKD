<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRating;
use App\Models\EventRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventRatingController extends Controller
{
    /**
     * TC-03 / TC-04: Mahasiswa submit rating untuk event yang sudah selesai.
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        // Guard: hanya mahasiswa
        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Penyelenggara event tidak dapat memberikan rating pada event sendiri.');
        }

        // TC-07 / A3: Event harus sudah selesai (tanggal + end_time sudah terlewati)
        $eventEndDateTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
        $isCompleted = $event->status === 'completed' || Carbon::now()->gte($eventEndDateTime);
        if (!$isCompleted) {
            abort(403, 'Rating hanya dapat diberikan setelah event selesai dilangsungkan.');
        }

        // TC-08: Mahasiswa harus terdaftar sebagai peserta
        $registered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'attended'])
            ->exists();

        if (!$registered) {
            abort(403, 'Anda tidak terdaftar sebagai peserta event ini.');
        }

        // TC-09: Cek duplikat
        $existing = EventRating::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            abort(422, 'Anda sudah pernah memberikan rating untuk event ini.');
        }

        // Validasi input
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating bintang wajib dipilih (1–5).',
            'rating.min'      => 'Rating bintang wajib dipilih (1–5).',
            'rating.max'      => 'Rating bintang wajib dipilih (1–5).',
            'comment.max'     => 'Komentar tidak boleh melebihi 500 karakter.',
        ]);

        EventRating::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'rating'   => $validated['rating'],
            'comment'  => $validated['comment'] ?? null,
        ]);

        \App\Services\ActivityLogger::log(
            'Memberi Rating Event',
            "Memberikan rating {$validated['rating']} bintang pada event: {$event->title}",
            'event'
        );

        return back()->with('success', 'Rating berhasil dikirim! Terima kasih atas umpan balik Anda.');
    }

    /**
     * TC-10 / A1: Mahasiswa edit rating yang sudah pernah diberikan.
     */
    public function update(Request $request, Event $event)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Aksi tidak diizinkan.');
        }

        $rating = EventRating::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating bintang wajib dipilih (1–5).',
            'rating.min'      => 'Rating bintang wajib dipilih (1–5).',
            'rating.max'      => 'Rating bintang wajib dipilih (1–5).',
            'comment.max'     => 'Komentar tidak boleh melebihi 500 karakter.',
        ]);

        $rating->update([
            'rating'  => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        \App\Services\ActivityLogger::log(
            'Memperbarui Rating Event',
            "Memperbarui rating menjadi {$validated['rating']} bintang pada event: {$event->title}",
            'event'
        );

        return back()->with('success', 'Rating berhasil diperbarui.');
    }
}
