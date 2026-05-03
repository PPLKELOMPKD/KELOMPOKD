<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyInternshipController extends Controller
{
    public function index()
    {
        // Menampilkan daftar lowongan magang
        $internships = Internship::latest()->get();
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
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'required|string|max:255',
            'deadline_at' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1',
        ], [
            'title.required' => 'Posisi wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'requirements.required' => 'Kualifikasi wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'deadline_at.required' => 'Batas waktu lamaran wajib diisi.',
            'deadline_at.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota harus berupa angka positif.'
        ]);

        $validated['is_published'] = true;
        Internship::create($validated);

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Magang Berhasil Ditambahkan');
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
            'description' => 'required|string',
            'requirements' => 'required|string',
            'location' => 'required|string|max:255',
            'deadline_at' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1',
        ], [
            'title.required' => 'Posisi wajib diisi.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'requirements.required' => 'Kualifikasi wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'deadline_at.required' => 'Batas waktu lamaran wajib diisi.',
            'deadline_at.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota harus berupa angka positif.'
        ]);

        $internship->update($validated);

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Magang Berhasil Diperbarui');
    }

    public function destroy(Internship $internship)
    {
        if ($internship->applications()->exists()) {
            $internship->update(['is_published' => false]);
            return redirect()->route('perusahaan.internships.index')->with('error', 'Lowongan tidak bisa dihapus penuh karena sudah memiliki pelamar. Status lowongan diubah menjadi Ditutup.');
        }

        $internship->delete();

        return redirect()->route('perusahaan.internships.index')->with('success', 'Lowongan Magang Berhasil Dihapus');
    }
}
