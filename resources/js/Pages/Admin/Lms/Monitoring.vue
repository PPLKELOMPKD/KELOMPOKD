<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    stats: Object,
    logs: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const role = ref(props.filters?.role || 'all');
const category = ref(props.filters?.category || 'all');

watch([search, role, category], debounce(function ([searchVal, roleVal, catVal]) {
    router.get(
        route('admin.lms-activity.index'),
        { search: searchVal, role: roleVal, category: catVal },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 300));

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options) + ' WIB';
};

const getInitials = (name) => {
    if (!name) return 'SYS';
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const roleColors = {
    mahasiswa: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/10',
    perusahaan: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10',
    admin: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/10',
    system: 'bg-slate-50 text-slate-700 ring-1 ring-slate-600/10',
};

const categoryColors = {
    course: 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/10',
    lesson: 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-600/10',
    quiz: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/10',
    assignment: 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/10',
    enrollment: 'bg-teal-50 text-teal-700 ring-1 ring-teal-600/10',
    moderasi: 'bg-red-50 text-red-700 ring-1 ring-red-600/10',
};

const showModal = ref(false);
const selectedLog = ref(null);

const openDetailModal = (log) => {
    selectedLog.value = log;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedLog.value = null;
};

const triggerExport = (format) => {
    const queryParams = new URLSearchParams({
        format: format,
        search: search.value,
        role: role.value,
        category: category.value
    });
    window.open(route('admin.lms-activity.export') + '?' + queryParams.toString(), '_blank');
};
</script>

<template>
    <AdminLayout title="Monitoring Aktivitas LMS">
        <Head title="Monitoring Aktivitas LMS" />

        <div class="mx-auto max-w-6xl space-y-6">
            
            <!-- Hero Section / Header Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] p-6 text-white shadow-xl">
                <div class="absolute -right-6 -top-6 h-40 w-40 rounded-full bg-purple-500/15 blur-3xl pointer-events-none"></div>
                <div class="absolute left-1/2 -bottom-8 h-32 w-32 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Title -->
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 ring-1 ring-blue-400/30">
                            <svg class="h-6 w-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">Monitoring Aktivitas LMS</h1>
                            <p class="text-sm text-slate-400 mt-0.5">Pantau seluruh aktivitas pembelajaran dan pengelolaan LMS.</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex flex-col items-center px-4 py-2 bg-blue-500/20 rounded-xl ring-1 ring-blue-400/30 min-w-[100px]">
                            <span class="text-lg font-black text-blue-300">{{ stats.total_activities_today }}</span>
                            <span class="text-[9px] font-bold text-blue-400/85 uppercase tracking-wider">Aktivitas Hari Ini</span>
                        </div>
                        <div class="flex flex-col items-center px-4 py-2 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30 min-w-[100px]">
                            <span class="text-lg font-black text-emerald-300">{{ stats.courses_created }}</span>
                            <span class="text-[9px] font-bold text-emerald-400/85 uppercase tracking-wider">Course Dibuat</span>
                        </div>
                        <div class="flex flex-col items-center px-4 py-2 bg-amber-500/20 rounded-xl ring-1 ring-amber-400/30 min-w-[100px]">
                            <span class="text-lg font-black text-amber-300">{{ stats.new_enrollments }}</span>
                            <span class="text-[9px] font-bold text-amber-400/85 uppercase tracking-wider">Enrollment Baru</span>
                        </div>
                        <div class="flex flex-col items-center px-4 py-2 bg-purple-500/20 rounded-xl ring-1 ring-purple-400/30 min-w-[100px]">
                            <span class="text-lg font-black text-purple-300">{{ stats.courses_completed }}</span>
                            <span class="text-[9px] font-bold text-purple-400/85 uppercase tracking-wider">Course Selesai</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Aktivitas & Export Log Section -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
                        <!-- Search -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Cari Aktivitas</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                                </svg>
                                <input 
                                    v-model="search" 
                                    type="text" 
                                    placeholder="Cari kata kunci..." 
                                    class="w-full rounded-xl border-slate-200 bg-white pl-9 text-xs focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Filter Peran / Role -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Filter Peran</label>
                            <select v-model="role" class="w-full rounded-xl border-slate-200 bg-white text-xs focus:border-blue-500 focus:ring-blue-500 cursor-pointer">
                                <option value="all">Semua Peran</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="perusahaan">Perusahaan</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <!-- Filter Kategori -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Kategori Aktivitas</label>
                            <select v-model="category" class="w-full rounded-xl border-slate-200 bg-white text-xs focus:border-blue-500 focus:ring-blue-500 cursor-pointer">
                                <option value="all">Semua Kategori</option>
                                <option value="course">Course</option>
                                <option value="lesson">Lesson</option>
                                <option value="quiz">Quiz</option>
                                <option value="assignment">Assignment</option>
                                <option value="enrollment">Enrollment</option>
                                <option value="moderasi">Moderasi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    <div class="flex items-end gap-2 self-end md:self-auto">
                        <button 
                            @click="triggerExport('csv')" 
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm"
                        >
                            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Export CSV
                        </button>
                        <button 
                            @click="triggerExport('excel')" 
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition shadow-sm"
                        >
                            <svg class="h-4 w-4 text-emerald-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Log Table Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-black tracking-wider text-slate-400 border-b border-slate-100">
                            <tr>
                                <th scope="col" class="px-6 py-4">Waktu</th>
                                <th scope="col" class="px-6 py-4">Pengguna</th>
                                <th scope="col" class="px-6 py-4">Role</th>
                                <th scope="col" class="px-6 py-4">Aktivitas</th>
                                <th scope="col" class="px-6 py-4">Target (Kategori)</th>
                                <th scope="col" class="px-6 py-4 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="h-10 w-10 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        <p class="text-sm font-bold text-slate-500">Tidak ada log aktivitas ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="log in logs.data" 
                                :key="log.id"
                                class="transition-colors hover:bg-slate-50/50"
                            >
                                <td class="whitespace-nowrap px-6 py-4 text-xs font-medium text-slate-700">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                                            {{ log.user ? getInitials(log.user.name) : 'SYS' }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 truncate text-xs">{{ log.user ? log.user.name : 'Sistem Otomatis' }}</div>
                                            <div class="text-[10px] text-slate-400 truncate">{{ log.user ? log.user.email : 'system@sikara.ac.id' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-bold ring-1 ring-inset uppercase" 
                                          :class="roleColors[log.role?.toLowerCase()] || roleColors.system">
                                        {{ log.role || 'System' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-xs text-slate-800">
                                    {{ log.action }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[9px] font-bold ring-1 ring-inset uppercase" 
                                          :class="categoryColors[log.category?.toLowerCase()] || 'bg-slate-50 text-slate-700 ring-slate-600/10'">
                                        {{ log.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="openDetailModal(log)" 
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50 transition"
                                    >
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links.length > 3" class="border-t border-slate-100 bg-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">
                            Menampilkan <span class="font-bold text-slate-800">{{ logs.from || 0 }}</span> sampai <span class="font-bold text-slate-800">{{ logs.to || 0 }}</span> dari <span class="font-bold text-slate-800">{{ logs.total }}</span> log
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, index) in logs.links" :key="index">
                            <div v-if="link.url === null" 
                                 class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-300 border border-transparent"
                                 v-html="link.label">
                            </div>
                            <Link v-else 
                                  :href="link.url"
                                  class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                                  :class="[
                                      link.active ? 'bg-slate-900 text-white' : 'text-slate-500 border border-slate-200 hover:bg-slate-50 hover:text-slate-800'
                                  ]"
                                  v-html="link.label">
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal Dialog -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showModal" class="relative z-10 w-full max-w-xl rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-100">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 font-bold">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-800">Detail Aktivitas LMS</h3>
                                </div>
                                <button @click="closeModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 py-5 space-y-4 text-xs">
                                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl">
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Waktu</p>
                                        <p class="font-semibold text-slate-700 mt-0.5">{{ formatDate(selectedLog.created_at) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">IP Address</p>
                                        <p class="font-semibold text-slate-700 mt-0.5">{{ selectedLog.ip_address || '127.0.0.1' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Nama Pengguna</p>
                                        <p class="font-semibold text-slate-700 mt-0.5">{{ selectedLog.user ? selectedLog.user.name : 'Sistem Otomatis' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Email</p>
                                        <p class="font-semibold text-slate-700 mt-0.5 truncate">{{ selectedLog.user ? selectedLog.user.email : 'system@sikara.ac.id' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Peran / Role</p>
                                        <span class="mt-1 inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-bold ring-1 ring-inset uppercase" 
                                              :class="roleColors[selectedLog.role?.toLowerCase()] || roleColors.system">
                                            {{ selectedLog.role || 'System' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Kategori (Target Data)</p>
                                        <span class="mt-1 inline-flex items-center rounded-md px-2 py-0.5 text-[9px] font-bold ring-1 ring-inset uppercase" 
                                              :class="categoryColors[selectedLog.category?.toLowerCase()] || 'bg-slate-50 text-slate-700 ring-slate-600/10'">
                                            {{ selectedLog.category }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px] mb-1">Aktivitas</p>
                                    <p class="font-bold text-slate-800 text-sm">{{ selectedLog.action }}</p>
                                </div>

                                <div>
                                    <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px] mb-1">Deskripsi Lengkap</p>
                                    <p class="leading-relaxed text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100 whitespace-pre-wrap">{{ selectedLog.description || '-' }}</p>
                                </div>

                                <div v-if="selectedLog.user_agent">
                                    <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px] mb-1">User Agent</p>
                                    <p class="text-[10px] text-slate-500 font-mono truncate bg-slate-50 p-2 rounded border border-slate-100" :title="selectedLog.user_agent">{{ selectedLog.user_agent }}</p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex justify-end border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="closeModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
