<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $role = $request->input('role', 'all');
        $category = $request->input('category', 'all');
        $search = $request->input('search', '');

        $logs = ActivityLog::with('user:id,name,email')
            ->byRole($role)
            ->byCategory($category)
            ->search($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'role' => $role,
                'category' => $category,
                'search' => $search,
            ]
        ]);
    }
}
