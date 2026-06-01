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
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|string|max:255',
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
            'title.required' => 'Posisi wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'requirements.required' => 'Kualifikasi wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'work_type.required' => 'Tipe pekerjaan wajib diisi.',
            'deadline_at.required' => 'Batas waktu lamaran wajib diisi.',
            'deadline_at.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota harus berupa angka positif.'
        ]);

        // Lowongan baru masuk ke antrian moderasi (pending), bukan langsung tayang
        $validated['is_published']      = false;
        $validated['moderation_status'] = 'pending';
        $validated['company_id']        = auth()->id();
        $internship = Internship::create($validated);

        \App\Services\ActivityLogger::log('Membuat Lowongan', "Membuat lowongan baru berjudul: {$internship->title} (menunggu moderasi)", 'lowongan');

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Berhasil Dikirim! Sedang menunggu persetujuan admin sebelum tayang.');
    }

    public function edit(Internship $internship)
    {
        return Inertia::render('Perusahaan/Internships/Form', [
            'internship' => $internship
        ]);
    }

    public function update(Request $request, Internship $internship)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|string|max:255',
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
            'title.required' => 'Posisi wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'requirements.required' => 'Kualifikasi wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'work_type.required' => 'Tipe pekerjaan wajib diisi.',
            'deadline_at.required' => 'Batas waktu lamaran wajib diisi.',
            'deadline_at.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota harus berupa angka positif.'
        ]);

        $internship->update($validated);

        \App\Services\ActivityLogger::log('Memperbarui Lowongan', "Memperbarui lowongan berjudul: {$internship->title}", 'lowongan');

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Magang Berhasil Diperbarui');
    }

    public function destroy(Internship $internship)
    {
        if ($internship->applications()->exists()) {
            $internship->update(['is_published' => false]);
            \App\Services\ActivityLogger::log('Menutup Lowongan', "Lowongan \"{$internship->title}\" ditutup (memiliki pelamar)", 'lowongan');
            return redirect()->route('perusahaan.internships.index')->with('error', 'Lowongan tidak bisa dihapus penuh karena sudah memiliki pelamar. Status lowongan diubah menjadi Ditutup.');
        }

        \App\Services\ActivityLogger::log('Menghapus Lowongan', "Menghapus lowongan berjudul: {$internship->title}", 'lowongan');
        $internship->delete();

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Magang Berhasil Dihapus');
    }
}
