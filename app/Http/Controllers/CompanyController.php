<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = User::where('role', 'perusahaan')
            ->with(['perusahaanProfile', 'internships'])
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'industry' => $company->perusahaanProfile?->industry ?? 'Perusahaan',
                    'rating' => 4.8,
                    'active_jobs' => $company->internships->where('is_published', true)->count(),
                ];
            });

        return Inertia::render('Features/CompanyList', [
            'companies' => $companies,
        ]);
    }

    public function show($id)
    {
        $company = User::where('role', 'perusahaan')
            ->with(['perusahaanProfile', 'internships' => function ($q) {
                $q->where('is_published', true);
            }])
            ->findOrFail($id);

        return Inertia::render('Company/Profile', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'profile' => $company->perusahaanProfile,
                'internships' => $company->internships,
            ]
        ]);
    }
}
