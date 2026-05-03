<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StudentProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->load(['mahasiswaProfile', 'skills']);

        return Inertia::render('Profile/Show', [
            'profile' => $user->mahasiswaProfile,
            'skills' => $user->skills,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nim' => ['required', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'gpa' => ['required', 'numeric', 'between:0,4'],
            'phone' => ['nullable', 'string', 'max:50'],
            'university' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['photo']);

        $profile = $request->user()->mahasiswaProfile()->first();

        if ($request->hasFile('photo')) {
            if ($profile?->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('profile-photos', 'public');
        }

        $request->user()->mahasiswaProfile()->updateOrCreate([], $data);

        return redirect()->route('profile.show');
    }
}
