<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'roles' => [
                ['value' => 'mahasiswa', 'label' => 'Mahasiswa'],
                ['value' => 'perusahaan', 'label' => 'Perusahaan'],
            ],
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = \Illuminate\Support\Facades\Cache::get('global_settings', []);
        if (isset($settings['registration_enabled']) && $settings['registration_enabled'] === 'false') {
            abort(403, 'New account registration is currently closed by the Administrator.');
        }

        $request->validate([
            'role' => 'required|in:mahasiswa,perusahaan',
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:50',
            'nim' => 'nullable|required_if:role,mahasiswa|string|max:50|unique:mahasiswa_profiles,nim',
            'university' => 'nullable|required_if:role,mahasiswa|string|max:255',
            'study_program' => 'nullable|required_if:role,mahasiswa|string|max:255',
            'legal_document' => 'nullable|required_if:role,perusahaan|file|mimes:pdf|max:5120',
            'terms' => 'accepted',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = $request->role === 'perusahaan' ? 'inactive' : 'active';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $status,
        ]);

        if ($user->isMahasiswa()) {
            MahasiswaProfile::create([
                'user_id' => $user->id,
                'nim' => $request->string('nim')->toString(),
                'department' => $request->string('study_program')->toString(),
                'study_program' => $request->string('study_program')->toString(),
                'gpa' => 0,
                'phone' => $request->string('phone')->toString() ?: null,
                'university' => $request->string('university')->toString(),
            ]);
        } elseif ($user->isPerusahaan()) {
            $path = null;
            if ($request->hasFile('legal_document')) {
                $path = $request->file('legal_document')->store('legal_documents', 'public');
            }
            \App\Models\PerusahaanProfile::create([
                'user_id' => $user->id,
                'legal_document_path' => $path,
            ]);
        }

        event(new Registered($user));

        \App\Services\ActivityLogger::log(
            'Registrasi Akun',
            "Pengguna baru {$user->name} mendaftar sebagai {$user->role}",
            'auth',
            $user->id,
            $user->role,
        );

        // Perusahaan: auto-login lalu arahkan ke verifikasi email.
        // Verifikasi admin (status active) akan diterapkan setelah email terverifikasi.
        if ($user->isPerusahaan()) {
            Auth::login($user);
            return redirect()->route('verification.notice');
        }

        // Mahasiswa: langsung ke login
        return redirect(route('login'))->with('status', 'Registration successful! Please log in with your account.');
    }
}
