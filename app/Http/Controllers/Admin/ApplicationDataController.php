<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDataController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = Application::with([
            'user.mahasiswaProfile',
            'internship',
        ])->latest();

        // ── Filter Status ──────────────────────────────────────────────
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // ── Pencarian Global ───────────────────────────────────────────
        // Cari berdasarkan: nama mahasiswa, email, judul posisi, nama perusahaan
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('internship', function ($iq) use ($search) {
                    $iq->where('title', 'like', "%{$search}%")
                       ->orWhere('company_name', 'like', "%{$search}%");
                });
            });
        }

        $paginated = $query->paginate(20)->withQueryString();

        // ── Hitung total per status (global, tanpa filter) ─────────────
        $counts = [
            'all'             => Application::count(),
            'submitted'       => Application::where('status', 'submitted')->count(),
            'menunggu ulasan' => Application::where('status', 'menunggu ulasan')->count(),
            'wawancara'       => Application::where('status', 'wawancara')->count(),
            'lolos'           => Application::where('status', 'lolos')->count(),
            'tidak lolos'     => Application::where('status', 'tidak lolos')->count(),
        ];

        // ── Transformasi data untuk frontend ──────────────────────────
        $applications = $paginated->getCollection()->map(function ($app) {
            $student = $app->user;
            $profile = $student?->mahasiswaProfile;
            $name    = $student?->name ?? 'Pelamar';

            return [
                'id'           => $app->id,
                'student_name' => $name,
                'student_email'=> $student?->email ?? '-',
                'university'   => $profile?->university ?? '-',
                'study_program'=> $profile?->study_program ?? ($profile?->department ?? '-'),
                'position'     => $app->internship?->title ?? '-',
                'company_name' => $app->internship?->company_name ?? '-',
                'applied_at'   => optional($app->created_at)->format('d M Y'),
                'applied_at_iso' => optional($app->created_at)->toIso8601String(),
                'status'       => $app->status ?? 'submitted',
            ];
        });

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $paginated->setCollection($applications),
            'counts'       => $counts,
            'filters'      => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Ekspor data lamaran sebagai file CSV.
     * Filter search & status dari query string ikut diterapkan.
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = Application::with([
            'user.mahasiswaProfile',
            'internship',
        ])->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('internship', function ($iq) use ($search) {
                    $iq->where('title', 'like', "%{$search}%")
                       ->orWhere('company_name', 'like', "%{$search}%");
                });
            });
        }

        $applications = $query->get();

        // ── Build CSV ─────────────────────────────────────────────────
        $headers = [
            'Nama Mahasiswa',
            'Email',
            'Universitas',
            'Program Studi',
            'Posisi / Jabatan',
            'Mitra Perusahaan',
            'Tanggal Apply',
            'Status',
        ];

        $rows = $applications->map(function ($app) {
            $student = $app->user;
            $profile = $student?->mahasiswaProfile;

            return [
                $student?->name ?? '-',
                $student?->email ?? '-',
                $profile?->university ?? '-',
                $profile?->study_program ?? ($profile?->department ?? '-'),
                $app->internship?->title ?? '-',
                $app->internship?->company_name ?? '-',
                optional($app->created_at)->format('d/m/Y'),
                ucwords($app->status ?? 'submitted'),
            ];
        });

        $filename = 'data_lamaran_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM untuk Excel agar karakter UTF-8 terbaca dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
