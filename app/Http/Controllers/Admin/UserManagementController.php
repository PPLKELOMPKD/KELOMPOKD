<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Tampilkan daftar semua pengguna dengan filter, search, dan pagination.
     */
    public function index(Request $request): Response
    {
        $search     = $request->query('search', '');
        $roleFilter = $request->query('role', 'all');
        $statusFilter = $request->query('status', 'all');
        $perPage    = 15;

        $query = User::with(['mahasiswaProfile', 'perusahaanProfile'])
            ->where('role', '!=', 'admin')
            ->latest();

        // Filter pencarian (nama atau email)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter role
        if (in_array($roleFilter, ['mahasiswa', 'perusahaan'])) {
            $query->where('role', $roleFilter);
        }

        // Filter status
        if (in_array($statusFilter, ['active', 'inactive', 'banned'])) {
            $query->where('status', $statusFilter);
        }

        $users = $query->paginate($perPage)->withQueryString()->through(function ($user) {
            $profile = null;

            if ($user->role === 'mahasiswa' && $user->mahasiswaProfile) {
                $p = $user->mahasiswaProfile;
                $profile = [
                    'nim'          => $p->nim,
                    'department'   => $p->department,
                    'study_program'=> $p->study_program,
                    'university'   => $p->university,
                    'gpa'          => $p->gpa,
                    'phone'        => $p->phone,
                ];
            } elseif ($user->role === 'perusahaan' && $user->perusahaanProfile) {
                $p = $user->perusahaanProfile;
                $profile = [
                    'industry'      => $p->industry,
                    'location'      => $p->location,
                    'website'       => $p->website,
                    'employee_count'=> $p->employee_count,
                    'founded_year'  => $p->founded_year,
                    'logo_path'     => $p->logo_path,
                ];
            }

            // Hitung data tambahan untuk perusahaan
            $extraStats = [];
            if ($user->role === 'perusahaan') {
                $extraStats['total_internships']  = $user->internships()->count();
                $extraStats['active_internships'] = $user->internships()->where('is_published', true)->count();
                $extraStats['total_events']       = $user->events()->count();
            } elseif ($user->role === 'mahasiswa') {
                $extraStats['total_applications'] = $user->applications()->count();
            }

            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'status'       => $user->status,
                'created_at'   => optional($user->created_at)->format('d M Y'),
                'last_login_at'=> optional($user->last_login_at)->diffForHumans() ?? 'Belum pernah',
                'profile'      => $profile,
                'extra_stats'  => $extraStats,
            ];
        });

        // Statistik ringkasan
        $stats = [
            'total'      => User::where('role', '!=', 'admin')->count(),
            'mahasiswa'  => User::where('role', 'mahasiswa')->count(),
            'perusahaan' => User::where('role', 'perusahaan')->count(),
            'active'     => User::where('role', '!=', 'admin')->where('status', 'active')->count(),
            'inactive'   => User::where('role', '!=', 'admin')->where('status', 'inactive')->count(),
            'banned'     => User::where('role', '!=', 'admin')->where('status', 'banned')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users'        => $users,
            'stats'        => $stats,
            'search'       => $search,
            'roleFilter'   => $roleFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Update status akun pengguna dengan rollback protection.
     * Jika perusahaan di-banned/non-aktifkan, seluruh lowongan aktifnya di-unpublish.
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        // Guard: jangan izinkan admin mengubah akun admin lain
        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak diizinkan mengubah status akun admin.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,banned'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'Status baru wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        $oldStatus = $user->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status pengguna tidak berubah.');
        }

        DB::transaction(function () use ($user, $newStatus, $oldStatus, $validated) {
            // Update status user
            $user->update(['status' => $newStatus]);

            // Cascading: jika perusahaan di-banned atau inactive → unpublish semua lowongannya
            if ($user->role === 'perusahaan' && in_array($newStatus, ['banned', 'inactive'])) {
                $unpublishedCount = Internship::where('company_id', $user->id)
                    ->where('is_published', true)
                    ->count();

                Internship::where('company_id', $user->id)
                    ->update(['is_published' => false]);

                if ($unpublishedCount > 0) {
                    ActivityLogger::log(
                        'Unpublish Lowongan Otomatis',
                        "Sebanyak {$unpublishedCount} lowongan dari perusahaan \"{$user->name}\" di-unpublish akibat perubahan status akun menjadi {$newStatus}.",
                        'admin'
                    );
                }
            }

            // Jika perusahaan di-aktifkan kembali dari banned/inactive → tidak auto-publish, admin harus moderasi ulang

            // Log aktivitas admin
            $actionLabel = match ($newStatus) {
                'active'   => 'Mengaktifkan Akun',
                'inactive' => 'Menonaktifkan Akun',
                'banned'   => 'Memblokir Akun (Banned)',
            };

            $reasonNote = !empty($validated['reason']) ? " — Alasan: {$validated['reason']}" : '';
            ActivityLogger::log(
                $actionLabel,
                "Mengubah status akun \"{$user->name}\" ({$user->email}) dari {$oldStatus} menjadi {$newStatus}{$reasonNote}.",
                'admin'
            );
        });

        $messages = [
            'active'   => "Akun \"{$user->name}\" berhasil diaktifkan.",
            'inactive' => "Akun \"{$user->name}\" berhasil dinonaktifkan.",
            'banned'   => "Akun \"{$user->name}\" berhasil diblokir (banned).",
        ];

        return back()->with('success', $messages[$newStatus]);
    }

    /**
     * Tampilkan halaman detail pengguna lengkap.
     */
    public function show(User $user): Response
    {
        if ($user->role === 'admin') {
            abort(403, 'Tidak dapat melihat detail akun admin.');
        }

        $user->load(['mahasiswaProfile', 'perusahaanProfile', 'skills']);

        $profile = null;
        $recentActivity = [];

        if ($user->role === 'mahasiswa') {
            $p = $user->mahasiswaProfile;
            $profile = $p ? [
                'nim'          => $p->nim,
                'department'   => $p->department,
                'study_program'=> $p->study_program,
                'university'   => $p->university,
                'gpa'          => $p->gpa,
                'phone'        => $p->phone,
                'location'     => $p->location,
                'bio'          => $p->bio,
                'photo_path'   => $p->photo_path,
            ] : null;

            $applications = $user->applications()
                ->with('internship:id,title,company_name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn($a) => [
                    'id'            => $a->id,
                    'internship'    => $a->internship?->title,
                    'company_name'  => $a->internship?->company_name,
                    'status'        => $a->status,
                    'created_at'    => optional($a->created_at)->format('d M Y'),
                ]);

            $recentActivity = $applications->toArray();

        } elseif ($user->role === 'perusahaan') {
            $p = $user->perusahaanProfile;
            $profile = $p ? [
                'industry'      => $p->industry,
                'location'      => $p->location,
                'website'       => $p->website,
                'description'   => $p->description,
                'employee_count'=> $p->employee_count,
                'founded_year'  => $p->founded_year,
                'office_address'=> $p->office_address,
                'logo_path'     => $p->logo_path,
            ] : null;

            $internships = $user->internships()
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'is_published', 'moderation_status', 'created_at'])
                ->map(fn($i) => [
                    'id'                => $i->id,
                    'title'             => $i->title,
                    'is_published'      => $i->is_published,
                    'moderation_status' => $i->moderation_status,
                    'created_at'        => optional($i->created_at)->format('d M Y'),
                ]);

            $recentActivity = $internships->toArray();
        }

        $skills = $user->skills->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'level' => $s->level ?? null]);

        $stats = [];
        if ($user->role === 'mahasiswa') {
            $byStatus = $user->applications()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $stats = [
                'total_applications' => $user->applications()->count(),
                'lolos'              => $byStatus['lolos'] ?? 0,
                'wawancara'          => $byStatus['wawancara'] ?? 0,
                'menunggu_ulasan'    => $byStatus['menunggu ulasan'] ?? 0,
            ];
        } elseif ($user->role === 'perusahaan') {
            $stats = [
                'total_internships'  => $user->internships()->count(),
                'active_internships' => $user->internships()->where('is_published', true)->count(),
                'total_applications' => \App\Models\Application::whereHas('internship', fn($q) => $q->where('company_id', $user->id))->count(),
                'total_events'       => $user->events()->count(),
            ];
        }

        return Inertia::render('Admin/Users/Show', [
            'user'           => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'status'       => $user->status,
                'created_at'   => optional($user->created_at)->format('d M Y H:i'),
                'last_login_at'=> optional($user->last_login_at)->format('d M Y H:i') ?? 'Belum pernah login',
            ],
            'profile'        => $profile,
            'skills'         => $skills,
            'stats'          => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
