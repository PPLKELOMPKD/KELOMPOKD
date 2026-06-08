<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    profile: Object,
    skills: Array,
    stats: Object,
    recentActivity: Array,
});

// Helpers
const roleStyle  = { mahasiswa: 'bg-blue-100 text-blue-700', perusahaan: 'bg-emerald-100 text-emerald-700' };
const roleLabel  = { mahasiswa: 'Mahasiswa', perusahaan: 'Perusahaan' };
const statusCfg  = {
    active:   { cls: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500', label: 'Aktif' },
    inactive: { cls: 'bg-slate-100 text-slate-500',    dot: 'bg-slate-400',   label: 'Nonaktif' },
    banned:   { cls: 'bg-red-100 text-red-600',         dot: 'bg-red-500',     label: 'Banned' },
};
</script>

<template>
    <Head :title="`Detail Pengguna: ${user.name}`" />
    <AdminLayout title="Detail Pengguna">

        <!-- Back & Title -->
        <div class="mb-6 flex items-center gap-4 animate-fade-in-up">
            <Link :href="route('admin.users.index')" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
            </Link>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Profil Pengguna</h1>
                <p class="text-sm text-slate-500">Informasi detail mengenai akun {{ roleLabel[user.role] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-up delay-100">
            <!-- Kolom Kiri: Profil Card -->
            <div class="lg:col-span-1 space-y-6">
                <!-- User Basic Info -->
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden group relative">
                    <!-- Cover/Header area -->
                    <div class="h-32 relative w-full overflow-hidden" 
                        :class="user.role === 'mahasiswa' ? 'bg-gradient-to-r from-blue-600 to-indigo-500' : 'bg-gradient-to-r from-emerald-600 to-teal-500'"
                        :style="profile?.cover_path ? `background-image: url('/storage/${profile.cover_path}'); background-size: cover; background-position: center;` : ''"
                    >
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    
                    <div class="px-8 pb-8 relative z-10">
                        <div class="flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                            <!-- Avatar -->
                            <div class="h-24 w-24 shrink-0 rounded-2xl bg-white p-1.5 shadow-lg ring-1 ring-slate-900/5 -mt-12 relative z-20">
                                <img v-if="profile?.logo_path || profile?.photo_path" :src="'/storage/' + (profile.logo_path || profile.photo_path)" :alt="user.name" class="h-full w-full rounded-xl object-cover" />
                                <div v-else class="h-full w-full rounded-xl flex items-center justify-center text-4xl font-black text-white shadow-inner" :class="user.role === 'mahasiswa' ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600'">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <div class="flex-1 text-center sm:text-left pt-2 mb-1">
                                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ user.name }}</h2>
                                <p class="text-sm font-medium text-slate-500">{{ user.email }}</p>
                                
                                <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold" :class="roleStyle[user.role]">
                                        {{ roleLabel[user.role] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold" :class="statusCfg[user.status]?.cls">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusCfg[user.status]?.dot"></span>
                                        {{ statusCfg[user.status]?.label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center sm:items-start">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Terdaftar</span>
                                <span class="font-bold text-slate-800">{{ user.created_at }}</span>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col items-center sm:items-start">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mb-1">Login Terakhir</span>
                                <span class="font-bold text-slate-800">{{ user.last_login_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Additional Info (Mahasiswa) -->
                <div v-if="user.role === 'mahasiswa'" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        Informasi Akademik
                    </h3>
                    
                    <div class="space-y-4 text-sm" v-if="profile">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Universitas</p>
                            <p class="font-medium text-slate-800">{{ profile.university ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">NIM</p>
                                <p class="font-medium text-slate-800">{{ profile.nim ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">IPK</p>
                                <p class="font-medium text-slate-800">{{ profile.gpa ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Fakultas / Jurusan</p>
                            <p class="font-medium text-slate-800">{{ profile.department ?? '-' }} / {{ profile.study_program ?? '-' }}</p>
                        </div>
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kontak</p>
                            <p class="font-medium text-slate-800 flex items-center gap-2"><svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> {{ profile.phone ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Lokasi</p>
                            <p class="font-medium text-slate-800 flex items-start gap-2"><svg class="h-3.5 w-3.5 text-slate-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <span class="flex-1">{{ profile.location ?? '-' }}</span></p>
                        </div>
                    </div>
                    <div v-else class="py-4 text-center text-sm text-slate-500 italic">
                        Profil akademik belum dilengkapi.
                    </div>
                </div>

                <!-- Info Perusahaan -->
                <div v-else-if="user.role === 'perusahaan'" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                            Identitas Perusahaan
                        </h3>
                        <Link :href="route('admin.verifications.show', user.id)" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-600 hover:bg-blue-100 transition-colors ring-1 ring-blue-200">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Lihat Verifikasi
                        </Link>
                    </div>
                    
                    <div class="space-y-4 text-sm" v-if="profile">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Industri</p>
                            <p class="font-medium text-slate-800">{{ profile.industry ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun Berdiri</p>
                                <p class="font-medium text-slate-800">{{ profile.founded_year ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan</p>
                                <p class="font-medium text-slate-800">{{ profile.employee_count ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Website</p>
                            <a v-if="profile.website" :href="profile.website" target="_blank" class="font-medium text-blue-600 hover:underline flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                {{ profile.website }}
                            </a>
                            <p v-else class="font-medium text-slate-800">-</p>
                        </div>
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kantor Pusat</p>
                            <p class="font-medium text-slate-800 flex items-start gap-2"><svg class="h-3.5 w-3.5 text-slate-400 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <span class="flex-1">{{ profile.office_address || profile.location || '-' }}</span></p>
                        </div>
                        <!-- Vision & Mission -->
                        <div v-if="profile.vision" class="pt-3 border-t border-slate-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Visi</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ profile.vision }}</p>
                        </div>
                        <div v-if="profile.mission">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Misi</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ profile.mission }}</p>
                        </div>
                        <!-- Specializations -->
                        <div v-if="profile.specializations?.length" class="pt-3 border-t border-slate-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Spesialisasi</p>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="spec in profile.specializations" :key="spec" class="inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ spec }}
                                </span>
                            </div>
                        </div>
                        <!-- Legal Document -->
                        <div class="pt-3 border-t border-slate-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Dokumen Legalitas</p>
                            <a v-if="profile.legal_document_path" :href="'/storage/' + profile.legal_document_path" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-all">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Lihat Dokumen PDF
                            </a>
                            <span v-else class="text-xs text-slate-500 italic">Belum diunggah</span>
                        </div>
                    </div>
                    <div v-else class="py-4 text-center text-sm text-slate-500 italic">
                        Profil perusahaan belum dilengkapi.
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Stats & Activity -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overview Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <template v-if="user.role === 'mahasiswa'">
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-slate-300">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Lamaran</p>
                            <p class="text-3xl font-black text-slate-900">{{ stats?.total_applications ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500 mb-1">Lolos</p>
                            <p class="text-3xl font-black text-emerald-700">{{ stats?.lolos ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-purple-100 bg-purple-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-purple-200 hover:bg-purple-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-500 mb-1">Wawancara</p>
                            <p class="text-3xl font-black text-purple-700">{{ stats?.wawancara ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-blue-200 hover:bg-blue-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-500 mb-1">Menunggu</p>
                            <p class="text-3xl font-black text-blue-700">{{ stats?.menunggu_ulasan ?? 0 }}</p>
                        </div>
                    </template>
                    <template v-else-if="user.role === 'perusahaan'">
                        <div class="relative overflow-hidden rounded-2xl border border-purple-100 bg-purple-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-purple-200 hover:bg-purple-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-purple-500 mb-1">Lowongan</p>
                            <p class="text-3xl font-black text-purple-700">{{ stats?.total_internships ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500 mb-1">Aktif</p>
                            <p class="text-3xl font-black text-emerald-700">{{ stats?.active_internships ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-blue-200 hover:bg-blue-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-500 mb-1">Pelamar</p>
                            <p class="text-3xl font-black text-blue-700">{{ stats?.total_applications ?? 0 }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl border border-orange-100 bg-orange-50/50 p-6 shadow-sm group hover:shadow-md transition-all hover:-translate-y-1 hover:border-orange-200 hover:bg-orange-50">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-orange-500 mb-1">Event</p>
                            <p class="text-3xl font-black text-orange-700">{{ stats?.total_events ?? 0 }}</p>
                        </div>
                    </template>
                </div>

                <!-- Bio / Deskripsi -->
                <div v-if="profile?.bio || profile?.description" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-3">{{ user.role === 'mahasiswa' ? 'Tentang Mahasiswa' : 'Deskripsi Perusahaan' }}</h3>
                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap">{{ profile.bio || profile.description }}</p>
                </div>

                <!-- Skills (Mahasiswa) -->
                <div v-if="user.role === 'mahasiswa' && skills?.length > 0" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Keahlian & Skills</h3>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="skill in skills" :key="skill.id" class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                            {{ skill.name }}
                        </span>
                    </div>
                </div>

                <!-- Recent Activity Table -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-slate-900">{{ user.role === 'mahasiswa' ? 'Riwayat Lamaran Terbaru' : 'Lowongan Terakhir' }}</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <tr v-if="user.role === 'mahasiswa'">
                                    <th class="px-6 py-3">Posisi & Perusahaan</th>
                                    <th class="px-6 py-3">Tanggal Lamaran</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                                <tr v-else>
                                    <th class="px-6 py-3">Judul Lowongan</th>
                                    <th class="px-6 py-3">Dibuat Pada</th>
                                    <th class="px-6 py-3">Status Moderasi</th>
                                    <th class="px-6 py-3">Visibilitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="recentActivity.length === 0">
                                    <td :colspan="user.role === 'mahasiswa' ? 3 : 4" class="px-6 py-8 text-center text-sm text-slate-500 italic">
                                        Belum ada data aktivitas tersedia.
                                    </td>
                                </tr>
                                <template v-else>
                                    <!-- List untuk Mahasiswa -->
                                    <template v-if="user.role === 'mahasiswa'">
                                        <tr v-for="act in recentActivity" :key="act.id" class="hover:bg-slate-50/50">
                                            <td class="px-6 py-3.5">
                                                <p class="text-sm font-bold text-slate-800">{{ act.internship }}</p>
                                                <p class="text-[11px] text-slate-500">{{ act.company_name }}</p>
                                            </td>
                                            <td class="px-6 py-3.5 text-xs text-slate-600">{{ act.created_at }}</td>
                                            <td class="px-6 py-3.5">
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-700': act.status === 'lolos',
                                                          'bg-blue-100 text-blue-700': act.status === 'menunggu ulasan',
                                                          'bg-purple-100 text-purple-700': act.status === 'wawancara',
                                                          'bg-red-100 text-red-700': act.status === 'tidak lolos' || act.status === 'ditolak',
                                                          'bg-slate-100 text-slate-700': !['lolos','menunggu ulasan','wawancara','tidak lolos','ditolak'].includes(act.status)
                                                      }">
                                                    {{ act.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                    <!-- List untuk Perusahaan -->
                                    <template v-else>
                                        <tr v-for="act in recentActivity" :key="act.id" class="hover:bg-slate-50/50">
                                            <td class="px-6 py-3.5">
                                                <p class="text-sm font-bold text-slate-800">{{ act.title }}</p>
                                            </td>
                                            <td class="px-6 py-3.5 text-xs text-slate-600">{{ act.created_at }}</td>
                                            <td class="px-6 py-3.5">
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-700': act.moderation_status === 'approved',
                                                          'bg-amber-100 text-amber-700': act.moderation_status === 'pending',
                                                          'bg-red-100 text-red-700': act.moderation_status === 'rejected',
                                                      }">
                                                    {{ act.moderation_status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3.5">
                                                <span v-if="act.is_published" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Tayang
                                                </span>
                                                <span v-else class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Tidak Tayang
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
</style>
