<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\MahasiswaProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Register a new user and return an API token.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|in:mahasiswa,perusahaan',
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'phone' => 'nullable|string|max:50',
                'nim' => 'nullable|required_if:role,mahasiswa|string|max:50|unique:mahasiswa_profiles,nim',
                'university' => 'nullable|required_if:role,mahasiswa|string|max:255',
                'study_program' => 'nullable|required_if:role,mahasiswa|string|max:255',
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

            // Generate token for the new user
            $tokenResult = $user->createToken('External API Token');

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'access_token' => $tokenResult->plainTextToken,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server saat registrasi.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Authenticate user and generate Sanctum API Token.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau kata sandi tidak valid.',
                ], 401);
            }

            // Revoke all existing tokens for the user to ensure single-device login policy (optional, but good for security)
            $user->tokens()->delete();

            // Generate new token
            // Token will automatically expire in 30 minutes due to sanctum.php config
            $tokenResult = $user->createToken('External API Token');

            return response()->json([
                'success' => true,
                'message' => 'Autentikasi berhasil.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                    'access_token' => $tokenResult->plainTextToken,
                    'token_type' => 'Bearer',
                    'expires_in_minutes' => config('sanctum.expiration', 30)
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Get the authenticated User's profile.
     */
    public function me(Request $request)
    {
        try {
            $user = clone $request->user();
            
            // Optionally load relations based on role
            if ($user->isMahasiswa()) {
                $user->load('mahasiswaProfile');
            } elseif ($user->isPerusahaan()) {
                $user->load('perusahaanProfile');
            }

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diambil.',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profil.',
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request)
    {
        try {
            // Delete the current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout. Token telah dicabut.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan logout.',
            ], 500);
        }
    }
}
