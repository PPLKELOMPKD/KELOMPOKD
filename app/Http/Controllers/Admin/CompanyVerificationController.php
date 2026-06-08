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

class CompanyVerificationController extends Controller
{
    /**
     * Tampilkan daftar perusahaan yang perlu diverifikasi.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', 'all'); // pending, verified, rejected, all
        $perPage = 15;

        $query = User::with('perusahaanProfile')
            ->where('role', 'perusahaan')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'pending') {
            $query->where('status', 'inactive');
        } elseif ($statusFilter === 'verified') {
            $query->where('status', 'active');
        } elseif ($statusFilter === 'rejected') {
            $query->where('status', 'banned');
        }

        $companies = $query->paginate($perPage)->withQueryString()->through(function($company) {
            $profile = $company->perusahaanProfile;
            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'status' => $company->status,
                'created_at' => optional($company->created_at)->format('d M Y'),
                'profile' => $profile ? [
                    'industry' => $profile->industry,
                    'location' => $profile->location,
                    'website' => $profile->website,
                    'logo_path' => $profile->logo_path,
                ] : null,
                'internships_count' => $company->internships()->count(),
            ];
        });

        $stats = [
            'total' => User::where('role', 'perusahaan')->count(),
            'pending' => User::where('role', 'perusahaan')->where('status', 'inactive')->count(),
            'verified' => User::where('role', 'perusahaan')->where('status', 'active')->count(),
            'rejected' => User::where('role', 'perusahaan')->where('status', 'banned')->count(),
        ];

        return Inertia::render('Admin/Verifications/Index', [
            'companies' => $companies,
            'stats' => $stats,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Tampilkan detail dokumen/profil perusahaan untuk ditinjau.
     */
    public function show(User $user): Response
    {
        if ($user->role !== 'perusahaan') {
            abort(404, 'Data not found.');
        }

        $user->load('perusahaanProfile');

        $profile = $user->perusahaanProfile;
        
        $companyData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'created_at' => optional($user->created_at)->format('d M Y H:i'),
            'last_login_at' => optional($user->last_login_at)->diffForHumans() ?? 'Belum pernah',
        ];

        $profileData = $profile ? [
            'industry' => $profile->industry,
            'location' => $profile->location,
            'website' => $profile->website,
            'description' => $profile->description,
            'vision' => $profile->vision,
            'mission' => $profile->mission,
            'founded_year' => $profile->founded_year,
            'employee_count' => $profile->employee_count,
            'specializations' => $profile->specializations,
            'office_address' => $profile->office_address,
            'logo_path' => $profile->logo_path,
            'cover_path' => $profile->cover_path,
            'legal_document_path' => $profile->legal_document_path,
        ] : null;

        $stats = [
            'total_internships' => $user->internships()->count(),
            'active_internships' => $user->internships()->where('is_published', true)->count(),
            'total_events' => $user->events()->count(),
        ];

        $recentInternships = $user->internships()
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'is_published', 'moderation_status', 'created_at'])
            ->map(fn($i) => [
                'id' => $i->id,
                'title' => $i->title,
                'is_published' => $i->is_published,
                'moderation_status' => $i->moderation_status,
                'created_at' => optional($i->created_at)->format('d M Y'),
            ]);

        return Inertia::render('Admin/Verifications/Show', [
            'company' => $companyData,
            'profile' => $profileData,
            'stats' => $stats,
            'recentInternships' => $recentInternships,
        ]);
    }

    /**
     * Memperbarui status verifikasi perusahaan (active, inactive, banned).
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'perusahaan') {
            return back()->with('error', 'Only company accounts can be verified through this module.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,banned'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'New status is required.',
            'status.in'       => 'Invalid status.',
        ]);

        $oldStatus = $user->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Company status remains unchanged.');
        }

        DB::transaction(function () use ($user, $newStatus, $oldStatus, $validated) {
            // Update status user
            $user->update(['status' => $newStatus]);

            // Cascading: unpublish lowongan jika inactive atau banned
            if (in_array($newStatus, ['banned', 'inactive'])) {
                $unpublishedCount = Internship::where('company_id', $user->id)
                    ->where('is_published', true)
                    ->count();

                Internship::where('company_id', $user->id)
                    ->update(['is_published' => false]);

                if ($unpublishedCount > 0) {
                    ActivityLogger::log(
                        'Unpublish Lowongan Otomatis',
                        "Sebanyak {$unpublishedCount} lowongan dari perusahaan \"{$user->name}\" di-unpublish otomatis akibat pencabutan verifikasi/blokir akun.",
                        'admin'
                    );
                }
            }

            $actionLabel = match ($newStatus) {
                'active'   => 'Verifikasi Perusahaan',
                'inactive' => 'Pencabutan Verifikasi (Inactive)',
                'banned'   => 'Pemblokiran Perusahaan (Banned)',
            };

            $reasonNote = !empty($validated['reason']) ? " — Alasan: {$validated['reason']}" : '';
            ActivityLogger::log(
                $actionLabel,
                "Mengubah status perusahaan \"{$user->name}\" dari {$oldStatus} menjadi {$newStatus}{$reasonNote}.",
                'admin'
            );
        });

        $emailSent = false;
        if ($newStatus === 'active') {
            $emailSent = app(\App\Services\AutomatedMailService::class)->sendCompanyVerified($user);
        } elseif ($newStatus === 'banned') {
            $emailSent = app(\App\Services\AutomatedMailService::class)->sendCompanyRejected($user, $validated['reason'] ?? null);
        }

        $messages = [
            'active'   => "Company \"{$user->name}\" has been successfully verified and activated.",
            'inactive' => "Verification for company \"{$user->name}\" has been successfully revoked.",
            'banned'   => "Company \"{$user->name}\" has been successfully banned.",
        ];

        $flashMessage = $messages[$newStatus];
        if ($newStatus !== 'inactive') {
            if ($emailSent) {
                $flashMessage .= ' Notification email sent successfully.';
            } else {
                $flashMessage .= ' (Notification email failed to send)';
            }
        }

        return back()->with('success', $flashMessage);
    }
}
