<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    company: Object,
    profile: Object,
    stats: Object,
    recentInternships: Array,
});

const isModalOpen = ref(false);
const actionType = ref(''); // 'active', 'inactive', 'banned'

const form = useForm({
    status: '',
    reason: '',
});

const openModal = (type) => {
    actionType.value = type;
    form.status = type;
    form.reason = '';
    isModalOpen.value = true;
};

const submitAction = () => {
    form.patch(route('admin.verifications.updateStatus', props.company.id), {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const getStatusBadge = (status) => {
    const map = {
        'active': { label: 'Terverifikasi (Active)', class: 'bg-emerald-100 text-emerald-800 ring-emerald-600/20' },
        'inactive': { label: 'Pending (Inactive)', class: 'bg-amber-100 text-amber-800 ring-amber-600/20' },
        'banned': { label: 'Banned', class: 'bg-red-100 text-red-800 ring-red-600/20' },
    };
    return map[status] || { label: status, class: 'bg-gray-100 text-gray-800 ring-gray-600/20' };
};
</script>

<template>
    <AdminLayout :title="`Verifikasi: ${company.name}`">
        <!-- Flash Message -->
        <div v-if="$page.props.flash.success" class="mb-6 rounded-xl bg-emerald-50 p-4 shadow-sm ring-1 ring-emerald-500/20">
            <div class="flex">
                <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-800">{{ $page.props.flash.success }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6 flex items-center gap-4">
            <Link :href="route('admin.verifications.index')" class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-slate-50 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </Link>
            <div>
                <h1 class="text-2xl font-black text-slate-900">Detail Mitra Perusahaan</h1>
                <p class="text-sm font-medium text-slate-500">Tinjau profil dan validasi legalitas mitra</p>
            </div>
            
            <div class="ml-auto flex items-center gap-3">
                <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-bold ring-1 ring-inset" :class="getStatusBadge(company.status).class">
                    Status saat ini: {{ getStatusBadge(company.status).label }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Kolom Kiri: Info Profil & Aksi -->
            <div class="space-y-6 lg:col-span-1">
                <!-- Card Profil Singkat -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/50">
                    <div class="h-32 bg-slate-800 bg-cover bg-center" :style="profile?.cover_path ? `background-image: url('/storage/${profile.cover_path}')` : ''"></div>
                    <div class="px-6 pb-6 text-center">
                        <div class="-mt-12 flex justify-center relative z-10">
                            <div class="rounded-2xl border-4 border-white bg-white p-1 shadow-md">
                                <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                    <img v-if="profile?.logo_path" :src="`/storage/${profile.logo_path}`" class="h-full w-full object-cover" />
                                    <span v-else class="text-2xl font-black text-slate-400">{{ company.name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-xl font-extrabold text-slate-900">{{ company.name }}</h2>
                            <p class="text-sm font-semibold text-blue-600 mt-1" v-if="profile?.industry">{{ profile.industry }}</p>
                            
                            <div class="mt-5 flex flex-col gap-3 text-sm text-slate-600 text-left rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200/50">
                                <div class="flex items-start gap-3">
                                    <svg class="h-5 w-5 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span class="break-all font-medium text-slate-700">{{ company.email }}</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <svg class="h-5 w-5 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span class="font-medium text-slate-700">{{ profile?.location || 'Belum diatur' }}</span>
                                </div>
                                <div class="flex items-start gap-3" v-if="profile?.website">
                                    <svg class="h-5 w-5 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <a :href="profile.website" target="_blank" class="font-medium text-blue-600 hover:text-blue-700 hover:underline break-all">{{ profile.website.replace(/^https?:\/\//, '') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aksi Keputusan Admin -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
                    <h3 class="font-bold text-slate-900 mb-4">Keputusan Verifikasi</h3>
                    <div class="flex flex-col gap-3">
                        <button 
                            v-if="company.status !== 'active'"
                            @click="openModal('active')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Verifikasi & Setujui Mitra
                        </button>

                        <button 
                            v-if="company.status === 'active'"
                            @click="openModal('inactive')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-amber-600 hover:shadow"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Cabut Verifikasi (Pending)
                        </button>

                        <button 
                            v-if="company.status !== 'banned'"
                            @click="openModal('banned')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-600 hover:shadow"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Tolak / Blokir Akun (Banned)
                        </button>
                    </div>
                    <p class="mt-4 text-xs text-slate-500 leading-relaxed text-center">
                        Pastikan seluruh data dan dokumen profil sesuai dengan persyaratan legalitas sebelum memberikan persetujuan.
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan: Detail & Data Legalitas -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Info Legalitas & Operasional -->
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/50">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-bold text-slate-900">Data Legalitas & Operasional</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-slate-500">Tahun Berdiri</dt>
                                <dd class="mt-1 text-sm font-bold text-slate-900">{{ profile?.founded_year || 'Belum diisi' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-slate-500">Jumlah Karyawan</dt>
                                <dd class="mt-1 text-sm font-bold text-slate-900">{{ profile?.employee_count || 'Belum diisi' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold text-slate-500">Alamat Kantor (Legal)</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-900 leading-relaxed bg-slate-50 p-3 rounded-lg ring-1 ring-slate-200/50">
                                    {{ profile?.office_address || 'Belum diisi' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold text-slate-500">Deskripsi Perusahaan</dt>
                                <dd class="mt-1 text-sm text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg ring-1 ring-slate-200/50">
                                    {{ profile?.description || 'Belum ada deskripsi.' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2 mt-2">
                                <dt class="text-xs font-semibold text-slate-500 mb-2">Dokumen Legalitas (Surat Izin Usaha)</dt>
                                <dd>
                                    <a v-if="profile?.legal_document_path" :href="`/storage/${profile.legal_document_path}`" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-100 hover:border-blue-300 transition-all shadow-sm">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                                        Lihat Dokumen PDF
                                    </a>
                                    <div v-else class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-500">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        Tidak ada dokumen legalitas yang diunggah.
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Aktivitas di Platform -->
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/50">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="font-bold text-slate-900">Riwayat Lowongan ({{ stats.total_internships }})</h3>
                    </div>
                    <div class="p-0">
                        <ul role="list" class="divide-y divide-slate-100">
                            <li v-for="internship in recentInternships" :key="internship.id" class="px-6 py-4 transition-colors hover:bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ internship.title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Dibuat pada {{ internship.created_at }}</p>
                                    </div>
                                    <div>
                                        <span v-if="internship.is_published" class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Published</span>
                                        <span v-else class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-500/20">Unpublished</span>
                                    </div>
                                </div>
                            </li>
                            <li v-if="recentInternships.length === 0" class="px-6 py-8 text-center text-sm text-slate-500">
                                Belum ada lowongan yang dibuat.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Aksi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="isModalOpen = false"></div>
            
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-8 text-left shadow-2xl transition-all">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full" 
                        :class="[
                            actionType === 'active' ? 'bg-emerald-100 text-emerald-600' : '',
                            actionType === 'inactive' ? 'bg-amber-100 text-amber-600' : '',
                            actionType === 'banned' ? 'bg-red-100 text-red-600' : ''
                        ]"
                    >
                        <svg v-if="actionType === 'active'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <svg v-if="actionType === 'inactive'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <svg v-if="actionType === 'banned'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">
                            {{ actionType === 'active' ? 'Verifikasi Perusahaan' : (actionType === 'inactive' ? 'Cabut Verifikasi' : 'Blokir Akun') }}
                        </h3>
                        <p class="text-sm text-slate-500 mt-1">Anda yakin ingin mengubah status perusahaan ini?</p>
                    </div>
                </div>
                
                <form @submit.prevent="submitAction" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan/Alasan Internal (Opsional)</label>
                        <textarea
                            v-model="form.reason"
                            rows="3"
                            placeholder="Misal: Dokumen legalitas kurang jelas atau terindikasi fiktif..."
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                        <p class="mt-1.5 text-xs text-slate-500" v-if="actionType !== 'active'">Catatan ini akan disimpan di Log Aktivitas.</p>
                    </div>
                    
                    <div v-if="actionType !== 'active'" class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-500/20">
                        <div class="flex">
                            <svg class="h-5 w-5 text-amber-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="ml-3 text-xs font-semibold text-amber-800">Perhatian: Semua lowongan aktif perusahaan ini akan disembunyikan (unpublished) secara otomatis.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="isModalOpen = false"
                            class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                            :class="[
                                actionType === 'active' ? 'bg-emerald-600 hover:bg-emerald-700 focus-visible:outline-emerald-600' : '',
                                actionType === 'inactive' ? 'bg-amber-500 hover:bg-amber-600 focus-visible:outline-amber-500' : '',
                                actionType === 'banned' ? 'bg-red-600 hover:bg-red-700 focus-visible:outline-red-600' : '',
                                form.processing ? 'opacity-75 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ form.processing ? 'Memproses...' : 'Konfirmasi & Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
