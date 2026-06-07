<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLmsActivityController extends Controller
{
    /**
     * Display the LMS Activity Monitoring dashboard.
     */
    public function index(Request $request): InertiaResponse
    {
        // 1. Auto-seeding of LMS mock data & activity logs if empty
        if (LmsCourse::where('slug', 'fundamental-ui-ux-design')->doesntExist()) {
            try {
                $seeder = new \Database\Seeders\LmsMonitorSeeder();
                $seeder->run();
            } catch (\Exception $e) {
                // Silently catch seeder errors to avoid breaking the page
            }
        }

        if (ActivityLog::whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi'])->doesntExist()) {
            try {
                $seeder2 = new \Database\Seeders\LmsActivityLogSeeder();
                $seeder2->run();
            } catch (\Exception $e) {
                // Silently catch seeder errors
            }
        }

        // 2. Calculate Stats
        $totalActivitiesToday = ActivityLog::whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi'])
            ->whereDate('created_at', today())
            ->count();
        $coursesCreated = LmsCourse::count();
        $newEnrollments = LmsEnrollment::count();
        $coursesCompleted = LmsEnrollment::where('is_graduated', true)->count();

        $stats = [
            'total_activities_today' => $totalActivitiesToday,
            'courses_created' => $coursesCreated,
            'new_enrollments' => $newEnrollments,
            'courses_completed' => $coursesCompleted,
        ];

        // 3. Fetch Filters
        $search = $request->query('search', '');
        $role = $request->query('role', 'all');
        $category = $request->query('category', 'all');

        // 4. Query Logs
        $query = ActivityLog::with('user:id,name,email')
            ->whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi']);

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Lms/Monitoring', [
            'stats' => $stats,
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'category' => $category,
            ]
        ]);
    }

    /**
     * Export the filtered LMS activity logs to CSV or Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->query('search', '');
        $role = $request->query('role', 'all');
        $category = $request->query('category', 'all');
        $format = strtolower($request->query('format', 'csv'));

        $query = ActivityLog::with('user:id,name,email')
            ->whereIn('category', ['course', 'lesson', 'quiz', 'assignment', 'enrollment', 'moderasi']);

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->latest()->get();

        $headers = [
            'Tanggal & Waktu',
            'Nama Pengguna',
            'Email',
            'Role',
            'Kategori',
            'Aktivitas',
            'Detail Deskripsi',
            'IP Address',
            'User Agent'
        ];

        $rows = $logs->map(function ($log) {
            return [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->name : 'System',
                $log->user ? $log->user->email : 'system@sikara.ac.id',
                ucfirst($log->role),
                ucfirst($log->category),
                $log->action,
                $log->description ?? '-',
                $log->ip_address ?? '127.0.0.1',
                $log->user_agent ?? '-'
            ];
        });

        $extension = ($format === 'excel') ? 'csv' : 'csv'; // Both output as CSV but handled by excel using BOM
        $filename = 'lms_activity_log_' . now()->format('Ymd_His') . '.' . $extension;

        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }
}
