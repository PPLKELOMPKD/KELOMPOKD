<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    companies: Object,
    stats: Object,
    search: String,
    statusFilter: String,
});

const searchTerm = ref(props.search || '');
const currentStatus = ref(props.statusFilter || 'all');

const fetchVerifications = debounce(() => {
    router.get(
        route('admin.verifications.index'),
        { search: searchTerm.value, status: currentStatus.value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 300);

watch([searchTerm, currentStatus], () => {
    fetchVerifications();
});

const getStatusClass = (status) => {
    if (status === 'active') return 'bg-emerald-100 text-emerald-800 ring-emerald-600/20';
    if (status === 'inactive') return 'bg-amber-100 text-amber-800 ring-amber-600/20';
    if (status === 'banned') return 'bg-red-100 text-red-800 ring-red-600/20';
    return 'bg-gray-100 text-gray-800 ring-gray-600/20';
};

const getStatusLabel = (status) => {
    if (status === 'active') return 'Verified';
    if (status === 'inactive') return 'Pending';
    if (status === 'banned') return 'Banned';
    return status;
};
</script>

<template>
    <AdminLayout title="Verifikasi Mitra Perusahaan">
        <div class="space-y-6">
            <!-- Header & Stats -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
                    <p class="text-sm font-semibold text-slate-500">Total Mitra Terdaftar</p>
                    <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
                    <p class="text-sm font-semibold text-emerald-600">Terverifikasi (Active)</p>
                    <p class="mt-2 text-3xl font-black tracking-tight text-emerald-700">{{ stats.verified }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-amber-600">Pending (Inactive)</p>
                        <span v-if="stats.pending > 0" class="flex h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    </div>
                    <p class="mt-2 text-3xl font-black tracking-tight text-amber-700">{{ stats.pending }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
                    <p class="text-sm font-semibold text-red-600">Ditolak / Diblokir</p>
                    <p class="mt-2 text-3xl font-black tracking-tight text-red-700">{{ stats.rejected }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/50">
                <div class="relative max-w-sm flex-1">
                    <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/>
                    </svg>
                    <input 
                        v-model="searchTerm" 
                        type="text" 
                        placeholder="Cari perusahaan atau email..." 
                        class="w-full rounded-xl border-slate-200 pl-10 pr-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-slate-600">Filter Status:</span>
                    <select v-model="currentStatus" class="rounded-xl border-slate-200 py-2.5 pl-4 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Pending (Inactive)</option>
                        <option value="verified">Terverifikasi (Active)</option>
                        <option value="rejected">Ditolak / Banned</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/50">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Perusahaan</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Industri & Lokasi</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kontak</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Tgl Mendaftar</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-6 py-4 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="company in companies.data" :key="company.id" class="transition-colors hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 ring-1 ring-slate-200 overflow-hidden">
                                            <img v-if="company.profile?.logo_path" :src="`/storage/${company.profile.logo_path}`" class="h-full w-full object-cover" />
                                            <span v-else class="font-bold text-slate-400">{{ company.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ company.name }}</p>
                                            <p class="text-xs text-slate-500" v-if="company.profile?.website">
                                                <a :href="company.profile.website" target="_blank" class="hover:text-blue-600 hover:underline">
                                                    {{ company.profile.website.replace(/^https?:\/\//, '') }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-700">{{ company.profile?.industry || '-' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ company.profile?.location || 'Lokasi tidak diatur' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ company.email }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ company.created_at }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-bold ring-1 ring-inset" :class="getStatusClass(company.status)">
                                        {{ getStatusLabel(company.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link 
                                        :href="route('admin.verifications.show', company.id)" 
                                        class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 transition-all hover:bg-slate-50 hover:text-blue-600 hover:ring-blue-500"
                                    >
                                        Tinjau
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="companies.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="mb-3 h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M4 20h16M7 20V6l5-2 5 2v14M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/>
                                        </svg>
                                        <p class="text-base font-semibold">Tidak ada data perusahaan.</p>
                                        <p class="text-sm mt-1">Coba sesuaikan filter pencarian Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t border-slate-200 bg-white px-6 py-4" v-if="companies.links && companies.links.length > 3">
                    <div class="flex items-center justify-center gap-1">
                        <template v-for="(link, index) in companies.links" :key="index">
                            <div 
                                v-if="link.url === null" 
                                class="px-3 py-2 rounded-lg text-sm font-medium text-slate-400 bg-slate-50 cursor-not-allowed" 
                                v-html="link.label">
                            </div>
                            <Link 
                                v-else 
                                :href="link.url" 
                                class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                                v-html="link.label">
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
