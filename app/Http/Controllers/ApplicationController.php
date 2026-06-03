<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'internship_id' => ['required', 'exists:internships,id'],
        ]);

        // Validasi deadline lowongan — tolak jika sudah kedaluwarsa
        $internship = \App\Models\Internship::findOrFail($data['internship_id']);
        if ($internship->deadline_at && $internship->deadline_at->isPast()) {
            return back()->with('error', 'Batas waktu pendaftaran lowongan ini telah berakhir. Anda tidak dapat melamar lagi.');
        }

        $user = $request->user();

        $hasApplied = Application::where('user_id', $user->id)
            ->where('internship_id', $request->internship_id)
            ->exists();

        if ($hasApplied) {
            return back()->with('error', 'Anda sudah melamar posisi ini.');
        }

        $application = $user->applications()->create([
            'users_id' => $user->id,
            'internship_id' => $data['internship_id'],
            'status' => 'submitted'
        ]);

        \App\Services\ActivityLogger::log('Melamar Lowongan', "Melamar pada lowongan magang ID: {$data['internship_id']}", 'lamaran');

        // Notifikasi untuk Mahasiswa
        Notification::query()->create([
            'user_id' => $request->user()->id,
            'title' => 'Lamaran berhasil dikirim',
            'message' => 'Lamaran Anda telah tersimpan dengan status submitted.',
            'type' => 'application',
        ]);

        // Notifikasi untuk Mitra Perusahaan
        Notification::query()->create([
            'user_id' => $internship->company_id,
            'title' => 'Lamaran Baru Masuk',
            'message' => "{$user->name} telah melamar untuk posisi {$internship->title}.",
            'type' => 'application',
            'link' => route('perusahaan.applicants.show', $application->id),
        ]);

        return back()->with('success', 'Lamaran berhasil dikirim!');
    }
}
