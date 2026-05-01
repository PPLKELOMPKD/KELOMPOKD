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

        $user = $request->user();

        $hasApplied = Application::where('user_id', $user->id)
            ->where('internship_id', $request->internship_id)
            ->exists();

        if ($hasApplied) {
            return back()->with('error', 'Anda sudah melamar posisi ini.');
        }

        $user->applications()->create([
            'users_id' => $user->id,
            'internship_id' => $data['internship_id'],
            'status' => 'submitted'
        ]);

        Notification::query()->create([
            'user_id' => $request->user()->id,
            'title' => 'Lamaran berhasil dikirim',
            'message' => 'Lamaran Anda telah tersimpan dengan status submitted.',
            'type' => 'application',
        ]);

        return redirect()->route('internships.index')->with('success', 'Lamaran berhasil dikirim!');
    }
}
