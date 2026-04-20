<?php

namespace App\Http\Controllers;

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

        $application = $request->user()->applications()->firstOrCreate(
            ['internship_id' => $data['internship_id']],
            ['status' => 'submitted']
        );

        Notification::query()->create([
            'user_id' => $request->user()->id,
            'title' => 'Lamaran berhasil dikirim',
            'message' => 'Lamaran Anda telah tersimpan dengan status submitted.',
            'type' => 'application',
        ]);

        if (! $application->wasRecentlyCreated) {
            $application->update(['status' => 'submitted']);
        }

        return redirect()->route('internships.index');
    }
}
