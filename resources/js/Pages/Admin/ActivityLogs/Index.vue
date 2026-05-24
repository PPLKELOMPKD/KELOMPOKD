<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const role = ref(props.filters?.role || 'all');
const category = ref(props.filters?.category || 'all');

watch([search, role, category], debounce(function ([search, role, category]) {
    router.get(
        route('admin.activity-logs.index'),
        { search, role, category },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 300));

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const roleColors = {
    mahasiswa: 'bg-blue-100 text-blue-700 ring-blue-600/20',
    perusahaan: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    admin: 'bg-purple-100 text-purple-700 ring-purple-600/20',
    system: 'bg-slate-100 text-slate-700 ring-slate-600/20',
};

const catColors = {
    auth: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    lowongan: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    event: 'bg-orange-50 text-orange-700 ring-orange-600/20',
    lms: 'bg-purple-50 text-purple-700 ring-purple-600/20',
    lamaran: 'bg-pink-50 text-pink-700 ring-pink-600/20',
    admin: 'bg-slate-50 text-slate-700 ring-slate-600/20',
    profile: 'bg-teal-50 text-teal-700 ring-teal-600/20',
};

</script>

<template>
    <AdminLayout title="Log Aktivitas">
        <Head title="Log Aktivitas" />

        <div class="mx-auto max-w-6xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Log Aktivitas Sistem Terintegrasi</h1>
                <p class="mt-1 text-sm text-[#64748B]">
                    Pantau seluruh aktivitas real-time dari Mahasiswa, Perusahaan, Admin, dan Sistem.
                </p>
            </div>

            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <input v-model="search" type="text" placeholder="Cari nama, aksi, atau keterangan..." 
                           class="w-full rounded-xl border-slate-200 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Peran</label>
                    <select v-model="role" class="w-full rounded-xl border-slate-200 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer">
                        <option value="all">Semua Peran</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="perusahaan">Perusahaan</option>
                        <option value="admin">Admin</option>
                        <option value="system">Sistem Otomatis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter Kategori</label>
                    <select v-model="category" class="w-full rounded-xl border-slate-200 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer">
                        <option value="all">Semua Kategori</option>
                        <option value="auth">Otentikasi & Akun</option>
                        <option value="lowongan">Manajemen Lowongan</option>
                        <option value="lamaran">Proses Lamaran</option>
                        <option value="event">Manajemen Event</option>
                        <option value="lms">Aktivitas LMS</option>
                        <option value="profile">Update Profil</option>
                        <option value="admin">Konfigurasi Admin</option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
                <!-- Table Header -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#64748B]">
                        <thead class="bg-[#F8FAFC] text-xs uppercase text-[#94A3B8] border-b border-[#E2E8F0]">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Waktu</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Pengguna & Peran</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Kategori / Aksi</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Keterangan Detail</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Network Info</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9]">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="h-10 w-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        <p class="text-sm font-medium text-slate-500">Tidak ada log aktivitas ditemukan berdasarkan filter saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="log in logs.data" 
                                :key="log.id"
                                class="transition-colors hover:bg-[#F8FAFC]/50"
                            >
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-[13px] text-[#475569]">
                                    {{ formatDate(log.created_at) }}
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">{{ new Date(log.created_at).diffForHumans ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white shadow-sm ring-2 ring-white">
                                            {{ log.user ? getInitials(log.user.name) : 'AI' }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-[#0F172A]">{{ log.user ? log.user.name : 'Sistem Otomatis' }}</div>
                                            <div class="text-[11px] text-slate-400">{{ log.user ? log.user.email : 'system@sikara.ac.id' }}</div>
                                            <span class="mt-1.5 inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold ring-1 ring-inset uppercase" 
                                                  :class="roleColors[log.role?.toLowerCase()] || roleColors.system">
                                                {{ log.role || 'System' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1.5 items-start">
                                        <span v-if="log.category" class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset uppercase" 
                                              :class="catColors[log.category?.toLowerCase()] || catColors.admin">
                                            {{ log.category }}
                                        </span>
                                        <span class="font-bold text-slate-700 text-xs">{{ log.action }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[13px] leading-relaxed text-[#475569] whitespace-normal">
                                        {{ log.description || '-' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 font-mono text-[11px] text-[#94A3B8]">
                                    <div>{{ log.ip_address || '127.0.0.1' }}</div>
                                    <div class="truncate w-32 mt-1 text-[10px]" :title="log.user_agent">{{ log.user_agent || 'Unknown' }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links.length > 3" class="border-t border-[#E2E8F0] bg-white px-6 py-4 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-[#64748B]">
                            Menampilkan <span class="font-medium text-[#0F172A]">{{ logs.from || 0 }}</span> sampai <span class="font-medium text-[#0F172A]">{{ logs.to || 0 }}</span> dari <span class="font-medium text-[#0F172A]">{{ logs.total }}</span> hasil
                        </p>
                    </div>
                    <div class="mt-4 sm:mt-0">
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <template v-for="(link, index) in logs.links" :key="index">
                                <div v-if="link.url === null" 
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#94A3B8] ring-1 ring-inset ring-[#E2E8F0]"
                                    :class="{'rounded-l-md': index === 0, 'rounded-r-md': index === logs.links.length - 1}"
                                    v-html="link.label">
                                </div>
                                <Link v-else 
                                    :href="link.url"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 transition-colors"
                                    :class="[
                                        link.active ? 'z-10 bg-[#2563EB] text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB]' : 'text-[#0F172A] ring-1 ring-inset ring-[#E2E8F0] hover:bg-[#F8FAFC]',
                                        {'rounded-l-md': index === 0, 'rounded-r-md': index === logs.links.length - 1}
                                    ]"
                                    v-html="link.label">
                                </Link>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
