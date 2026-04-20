<?php

namespace App\Http\Controllers;

use App\Models\LowonganKerja;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LowonganKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Simple mock of getting data, in a real scenario we might filter by user_id
        $lowongans = LowonganKerja::orderBy('created_at', 'desc')->get();
        return view('lowongan.index', compact('lowongans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lowongan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'posisi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kualifikasi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'kuota' => 'required|integer|min:1',
            'deadline' => 'required|date|after_or_equal:today',
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'deadline.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
        ]);

        // MOCK: using user_id 1 dynamically since we don't have auth middleware applied yet
        $validated['user_id'] = 1; 

        LowonganKerja::create($validated);

        return redirect()->route('lowongan.index')
                         ->with('success', 'Lowongan Magang Berhasil Ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LowonganKerja $lowongan)
    {
        return view('lowongan.edit', compact('lowongan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LowonganKerja $lowongan)
    {
        $validated = $request->validate([
            'posisi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kualifikasi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'kuota' => 'required|integer|min:1',
            'deadline' => 'required|date|after_or_equal:today',
            'status' => 'required|in:Aktif,Ditutup',
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'deadline.after_or_equal' => 'Batas waktu lamaran tidak boleh kurang dari tanggal hari ini.',
        ]);

        $lowongan->update($validated);

        return redirect()->route('lowongan.index')
                         ->with('success', 'Lowongan Magang Berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LowonganKerja $lowongan)
    {
        // Alternative Process A3: Menghapus Lowongan yang Memiliki Pelamar
        if ($lowongan->pelamars()->exists()) {
            return redirect()->route('lowongan.index')
                             ->with('error', 'Gagal menghapus! Lowongan ini sudah memiliki data pendaftaran (pelamar masuk). Disarankan untuk mengubah status menjadi "Ditutup".');
        }

        $lowongan->delete();

        return redirect()->route('lowongan.index')
                         ->with('success', 'Lowongan Magang berhasil dihapus.');
    }
}
