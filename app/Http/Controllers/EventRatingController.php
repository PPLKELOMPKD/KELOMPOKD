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
            return back()->with('error', 'Event organizers cannot rate their own events.');
        }

        // TC-07 / A3: Event harus sudah selesai (tanggal + end_time sudah terlewati)
        $eventEndDateTime = Carbon::parse($event->date->format('Y-m-d') . ' ' . $event->end_time);
        $isCompleted = $event->status === 'completed' || Carbon::now()->gte($eventEndDateTime);
        if (!$isCompleted) {
            abort(403, 'Ratings can only be given after the event has completed.');
        }

        // TC-08: Mahasiswa harus terdaftar sebagai peserta
        $registered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['registered', 'attended'])
            ->exists();

        if (!$registered) {
            abort(403, 'You are not registered as a participant of this event.');
        }

        // TC-09: Cek duplikat
        $existing = EventRating::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            abort(422, 'You have already rated this event.');
        }

        // Validasi input
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Star rating is required (1-5).',
            'rating.min'      => 'Star rating is required (1-5).',
            'rating.max'      => 'Star rating is required (1-5).',
            'comment.max'     => 'Comment cannot exceed 500 characters.',
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

        return back()->with('success', 'Rating submitted successfully! Thank you for your feedback.');
    }

    /**
     * TC-10 / A1: Mahasiswa edit rating yang sudah pernah diberikan.
     */
    public function update(Request $request, Event $event)
    {
        $user = $request->user();

        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Action not authorized.');
        }

        $rating = EventRating::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Star rating is required (1-5).',
            'rating.min'      => 'Star rating is required (1-5).',
            'rating.max'      => 'Star rating is required (1-5).',
            'comment.max'     => 'Comment cannot exceed 500 characters.',
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

        return back()->with('success', 'Rating updated successfully.');
    }
}
