@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Lowongan Magang</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi peluang magang untuk mahasiswa SIKARA.</p>
    </div>
    <a href="{{ route('lowongan.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 group">
        <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-100 group-hover:text-white transition-colors" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Tambah Lowongan Baru
    </a>
</div>

<!-- List View -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if($lowongans->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Posisi & Detail</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Lokasi / Tipe</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Deadline</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Status</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                            <span class="sr-only">Aksi</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($lowongans as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold text-lg">
                                    {{ substr($item->posisi, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-slate-900">{{ $item->posisi }}</div>
                                    <div class="text-sm text-slate-500">Kuota: {{ $item->kuota }} Mahasiswa</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            <div class="text-sm text-slate-900 flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $item->lokasi }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            @php
                                $isExpired = \Carbon\Carbon::parse($item->deadline)->isPast() && !\Carbon\Carbon::parse($item->deadline)->isToday();
                            @endphp
                            <div class="text-sm {{ $isExpired ? 'text-red-500 font-medium' : 'text-slate-600' }}">
                                {{ \Carbon\Carbon::parse($item->deadline)->format('d M Y') }}
                                @if($isExpired)
                                <span class="text-xs ml-1 bg-red-100 text-red-700 px-1.5 rounded">Lewat</span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            @if($item->status == 'Aktif' && !$isExpired)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/20">Ditutup</span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('lowongan.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900 flex items-center gap-1 group">
                                    <svg class="h-4 w-4 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('lowongan.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lowongan ini? Data pengajuan yang terkait mungkin akan mencegah proses hapus ini.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 flex items-center gap-1 group">
                                        <svg class="h-4 w-4 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-16 px-4">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <h3 class="mt-4 text-sm font-semibold text-slate-900">Tidak ada lowongan</h3>
            <p class="mt-1 text-sm text-slate-500">Mulai dengan menambahkan lowongan kerja baru untuk mahasiswa.</p>
            <div class="mt-6">
                <a href="{{ route('lowongan.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Lowongan
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
