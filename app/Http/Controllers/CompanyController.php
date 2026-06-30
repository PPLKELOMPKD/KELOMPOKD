<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        return $this->renderProfile($company);
    }

    public function ownerProfile(Request $request)
    {
        $company = $request->user()
            ->load(['perusahaanProfile', 'internships' => function ($q) {
                $q->where('is_published', true);
            }]);

        return $this->renderProfile($company, true);
    }

    public function updateOwnerProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'vision' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:' . now()->year],
            'employee_count' => ['nullable', 'string', 'max:255'],
            'specializations' => ['nullable', 'string', 'max:1000'],
            'office_address' => ['required', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'deleted_gallery_photos' => ['nullable', 'array'],
            'deleted_gallery_photos.*' => ['string'],
        ]);

        $company = $request->user();
        $profile = $company->perusahaanProfile()->firstOrNew();

        $company->update([
            'name' => $validated['name'],
        ]);

        if ($request->hasFile('logo')) {
            $this->deleteLocalProfileImage($profile->logo_path);
            $validated['logo_path'] = '/storage/' . $request->file('logo')->store('company-profiles/logos', 'public');
        }

        if ($request->hasFile('cover')) {
            $this->deleteLocalProfileImage($profile->cover_path);
            $validated['cover_path'] = '/storage/' . $request->file('cover')->store('company-profiles/covers', 'public');
        }

        // Handle gallery deletions and additions
        $gallery = $profile->gallery_photos ?? [];

        if ($request->has('deleted_gallery_photos')) {
            $toDelete = $request->input('deleted_gallery_photos');
            foreach ($toDelete as $photoPath) {
                $this->deleteLocalProfileImage($photoPath);
                $gallery = array_values(array_diff($gallery, [$photoPath]));
            }
        }

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $path = '/storage/' . $file->store('company-profiles/gallery', 'public');
                $gallery[] = $path;
            }
        }

        $profile->fill([
            'industry' => $validated['industry'] ?? null,
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'],
            'vision' => $validated['vision'] ?? null,
            'mission' => $validated['mission'] ?? null,
            'founded_year' => $validated['founded_year'] ?? null,
            'employee_count' => $validated['employee_count'] ?? null,
            'specializations' => $this->parseSpecializations($validated['specializations'] ?? null),
            'office_address' => $validated['office_address'],
            'logo_path' => $validated['logo_path'] ?? $profile->logo_path,
            'cover_path' => $validated['cover_path'] ?? $profile->cover_path,
            'contact_email' => $validated['contact_email'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
            'linkedin' => $validated['linkedin'] ?? null,
            'gallery_photos' => $gallery,
        ]);

        $company->perusahaanProfile()->save($profile);

        return back()->with('success', 'Company profile updated successfully.');
    }

    private function renderProfile(User $company, bool $isOwner = false)
    {
        return Inertia::render('Company/Profile', [
            'isOwner' => $isOwner,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'profile' => $company->perusahaanProfile,
                'internships' => $company->internships,
            ],
        ]);
    }

    private function parseSpecializations(?string $specializations): ?array
    {
        if (!$specializations) {
            return null;
        }

        return collect(explode(',', $specializations))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function deleteLocalProfileImage(?string $path): void
    {
        if (!$path || !str_starts_with($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(str_replace('/storage/', '', $path));
    }
}
