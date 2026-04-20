@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('lowongan.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-900/5 hover:bg-slate-50 transition-all">
            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Lowongan Baru</h1>
            <p class="text-sm text-slate-500">Isi formulir di bawah ini untuk menambahkan kesempatan magang.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('lowongan.store') }}" method="POST" class="p-6 sm:p-8">
            @csrf
            
            <div class="space-y-6">
                <!-- Posisi -->
                <div>
                    <label for="posisi" class="block text-sm font-medium leading-6 text-slate-900">Posisi Magang <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <input type="text" name="posisi" id="posisi" value="{{ old('posisi') }}" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('posisi') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}" placeholder="Contoh: UI/UX Designer Intern">
                    </div>
                    @error('posisi')
                        <p class="mt-2 text-sm text-red-600 font-medium" id="posisi-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium leading-6 text-slate-900">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="deskripsi" name="deskripsi" rows="4" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('deskripsi') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}" placeholder="Tuliskan deskripsi lengkap pekerjaan...">{{ old('deskripsi') }}</textarea>
                    </div>
                    @error('deskripsi')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kualifikasi -->
                <div>
                    <label for="kualifikasi" class="block text-sm font-medium leading-6 text-slate-900">Kualifikasi (Skill) <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="kualifikasi" name="kualifikasi" rows="4" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('kualifikasi') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}" placeholder="Sebutkan skill yang dibutuhkan...">{{ old('kualifikasi') }}</textarea>
                    </div>
                    @error('kualifikasi')
                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lokasi & Kuota -->
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                    <div>
                        <label for="lokasi" class="block text-sm font-medium leading-6 text-slate-900">Lokasi <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('lokasi') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}" placeholder="Contoh: Jakarta Pusat, WFO">
                        </div>
                        @error('lokasi')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kuota" class="block text-sm font-medium leading-6 text-slate-900">Kuota Penerimaan <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="number" name="kuota" id="kuota" min="1" value="{{ old('kuota') }}" class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('kuota') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}" placeholder="Contoh: 5">
                        </div>
                        @error('kuota')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deadline -->
                <div>
                    <label for="deadline" class="block text-sm font-medium leading-6 text-slate-900">Batas Waktu Pelamaran (Deadline) <span class="text-red-500">*</span></label>
                    <div class="mt-2 text-sm text-slate-500 mb-2">Pastikan batas waktu tidak kurang dari hari ini.</div>
                    <div class="mt-2 relative">
                        <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}" class="block w-full max-w-sm rounded-md border-0 py-2 pl-3 pr-10 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 {{ $errors->has('deadline') ? 'ring-red-300 focus:ring-red-500 bg-red-50' : 'ring-slate-300 focus:ring-indigo-600' }}">
                        @if($errors->has('deadline'))
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 max-w-sm" style="right: auto; left: 21rem;">
                                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    @error('deadline')
                        <p class="mt-2 text-sm text-red-600 font-medium font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-end gap-x-6">
                <a href="{{ route('lowongan.index') }}" class="text-sm font-semibold leading-6 text-slate-900 hover:text-slate-700 transition">Batal</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                    Simpan Lowongan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
