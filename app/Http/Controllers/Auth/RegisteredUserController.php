<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $request->validate([
            'role' => 'required|in:mahasiswa,perusahaan',
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:50',
            'nim' => 'nullable|required_if:role,mahasiswa|string|max:50|unique:mahasiswa_profiles,nim',
            'university' => 'nullable|required_if:role,mahasiswa|string|max:255',
            'study_program' => 'nullable|required_if:role,mahasiswa|string|max:255',
            'terms' => 'accepted',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
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
        }

        event(new Registered($user));

        return redirect(route('login'))->with('status', 'Registrasi berhasil! Silakan masuk dengan akun Anda.');
    }
}
