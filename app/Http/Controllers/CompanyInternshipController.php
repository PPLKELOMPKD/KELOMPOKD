<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyInternshipController extends Controller
{
    public function index()
    {
        // Menampilkan daftar lowongan magang milik perusahaan yang login
        $internships = Internship::where('company_id', auth()->id())
            ->latest()
            ->get();
        return Inertia::render('Perusahaan/Internships/Index', [
            'internships' => $internships
        ]);
    }

    public function create()
    {
        return Inertia::render('Perusahaan/Internships/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'required|string|max:255',
            'education_level' => 'nullable|string|in:D3,D4,S1,S2,S3',
            'work_type' => 'required|string|max:255',
            'duration' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'salary' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'deadline_at' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1',
        ], [
            'title.required' => 'Position is required.',
            'description.required' => 'Description is required.',
            'requirements.required' => 'Requirements are required.',
            'location.required' => 'Location is required.',
            'work_type.required' => 'Work type is required.',
            'deadline_at.required' => 'Application deadline is required.',
            'deadline_at.after_or_equal' => 'Application deadline cannot be earlier than today.',
            'quota.required' => 'Quota is required.',
            'quota.min' => 'Quota must be a positive number.'
        ]);

        $user = auth()->user();
        $profile = $user->perusahaanProfile;

        // Lowongan baru masuk ke antrian moderasi (pending), bukan langsung tayang
        $validated['company_name']      = $user->name;
        $validated['company_logo']      = $profile?->logo_path;
        $validated['is_published']      = false;
        $validated['moderation_status'] = 'pending';
        $validated['company_id']        = $user->id;
        $internship = Internship::create($validated);

        \App\Services\ActivityLogger::log('Membuat Lowongan', "Membuat lowongan baru berjudul: {$internship->title} (menunggu moderasi)", 'lowongan');

        return redirect()->route('perusahaan.internships.index')->with('success', 'Job listing sent successfully! Waiting for admin approval before going live.');
    }

    public function edit(Internship $internship)
    {
        // Pastikan hanya pemilik yang bisa akses
        abort_unless($internship->company_id === auth()->id(), 403);

        // Lowongan yang ditakedown (closed) tidak bisa diedit
        if ($internship->moderation_status === 'closed') {
            return redirect()
                ->route('perusahaan.internships.index')
                ->with('error', 'Taken down job listings cannot be edited. Please create a new listing.');
        }

        return Inertia::render('Perusahaan/Internships/Form', [
            'internship' => $internship
        ]);
    }

    public function update(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'required|string|max:255',
            'education_level' => 'nullable|string|in:D3,D4,S1,S2,S3',
            'work_type' => 'required|string|max:255',
            'duration' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'salary' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'deadline_at' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1',
        ], [
            'title.required' => 'Position is required.',
            'description.required' => 'Description is required.',
            'requirements.required' => 'Requirements are required.',
            'location.required' => 'Location is required.',
            'work_type.required' => 'Work type is required.',
            'deadline_at.required' => 'Application deadline is required.',
            'deadline_at.after_or_equal' => 'Application deadline cannot be earlier than today.',
            'quota.required' => 'Quota is required.',
            'quota.min' => 'Quota must be a positive number.'
        ]);

        $user = auth()->user();
        $profile = $user->perusahaanProfile;

        $validated['company_name'] = $user->name;
        $validated['company_logo'] = $profile?->logo_path;

        // Jika lowongan sebelumnya 'rejected', resubmit → kembali ke 'pending'
        // Jika 'approved' atau 'pending', update biasa (tidak ubah status moderasi)
        $moderationUpdate = [];
        if ($internship->moderation_status === 'rejected') {
            $moderationUpdate = [
                'moderation_status' => 'pending',
                'is_published'      => false,
                'rejection_reason'  => null,
                'moderated_by'      => null,
                'moderated_at'      => null,
            ];
        }

        $internship->update(array_merge($validated, $moderationUpdate));

        $message = $internship->moderation_status === 'pending' && !empty($moderationUpdate)
            ? 'Job listing updated successfully and resubmitted for admin review.'
            : 'Job listing updated successfully';

        \App\Services\ActivityLogger::log('Memperbarui Lowongan', "Memperbarui lowongan berjudul: {$internship->title}", 'lowongan');

        return redirect()->route('perusahaan.internships.index')->with('success', $message);
    }

    public function destroy(Internship $internship)
    {
        if ($internship->applications()->exists()) {
            $internship->update(['is_published' => false]);
            \App\Services\ActivityLogger::log('Menutup Lowongan', "Lowongan \"{$internship->title}\" ditutup (memiliki pelamar)", 'lowongan');
            return redirect()->route('perusahaan.internships.index')->with('error', 'The job listing cannot be fully deleted because it already has applicants. The status has been changed to Closed.');
        }

        \App\Services\ActivityLogger::log('Menghapus Lowongan', "Menghapus lowongan berjudul: {$internship->title}", 'lowongan');
        $internship->delete();

        return redirect()->route('perusahaan.internships.index')->with('success', 'Job listing deleted successfully');
    }
}
