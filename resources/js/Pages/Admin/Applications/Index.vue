<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    applications: Object,  // Laravel paginator object
    counts:       Object,
    filters:      Object,
});

// ─── Status Config ──────────────────────────────────────────────────
const statusConfig = {
    'submitted':       { label: 'Submitted',       bg: 'bg-blue-100',    text: 'text-blue-700',    dot: 'bg-blue-500' },
    'menunggu ulasan': { label: 'Menunggu Ulasan',  bg: 'bg-sky-100',     text: 'text-sky-700',     dot: 'bg-sky-500' },
    'wawancara':       { label: 'Wawancara',        bg: 'bg-purple-100',  text: 'text-purple-700',  dot: 'bg-purple-500' },
    'lolos':           { label: 'Lolos',            bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    'tidak lolos':     { label: 'Tidak Lolos',      bg: 'bg-red-100',     text: 'text-red-700',     dot: 'bg-red-500' },
};

const getStatus = (s) => statusConfig[s?.toLowerCase()] ?? { label: s ?? '-', bg: 'bg-slate-100', text: 'text-slate-600', dot: 'bg-slate-400' };

// ─── Filter Tabs ────────────────────────────────────────────────────
const tabs = [
    { key: 'all',             label: 'Semua',          countKey: 'all' },
    { key: 'submitted',       label: 'Submitted',       countKey: 'submitted' },
    { key: 'menunggu ulasan', label: 'Menunggu Ulasan', countKey: 'menunggu ulasan' },
    { key: 'wawancara',       label: 'Wawancara',       countKey: 'wawancara' },
    { key: 'lolos',           label: 'Lolos',           countKey: 'lolos' },
    { key: 'tidak lolos',     label: 'Tidak Lolos',     countKey: 'tidak lolos' },
];

const tabColors = {
    'all':             'border-slate-900 text-slate-900 bg-slate-50',
    'submitted':       'border-blue-500 text-blue-700 bg-blue-50',
    'menunggu ulasan': 'border-sky-500 text-sky-700 bg-sky-50',
    'wawancara':       'border-purple-500 text-purple-700 bg-purple-50',
    'lolos':           'border-emerald-500 text-emerald-700 bg-emerald-50',
    'tidak lolos':     'border-red-500 text-red-700 bg-red-50',
};
const badgeColors = {
    'all':             'bg-slate-200 text-slate-700',
    'submitted':       'bg-blue-200 text-blue-800',
    'menunggu ulasan': 'bg-sky-200 text-sky-800',
    'wawancara':       'bg-purple-200 text-purple-800',
    'lolos':           'bg-emerald-200 text-emerald-800',
    'tidak lolos':     'bg-red-200 text-red-800',
};

const activeTab = computed(() => props.filters?.status || 'all');

const changeFilter = (key) => {
    router.get(route('admin.applications.index'), {
        status: key,
        search: searchQuery.value,
    }, { preserveState: false });
};

// ─── Search ─────────────────────────────────────────────────────────
const searchQuery = ref(props.filters?.search || '');
let debounceTimer = null;

watch(searchQuery, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('admin.applications.index'), {
            status: activeTab.value,
            search: val,
        }, { preserveState: true, replace: true });
    }, 400);
});

// ─── CSV Export ──────────────────────────────────────────────────────
const totalCount = computed(() => props.applications?.total ?? 0);

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (activeTab.value && activeTab.value !== 'all') params.set('status', activeTab.value);
    if (searchQuery.value) params.set('search', searchQuery.value);
    const qs = params.toString();
    return route('admin.applications.export') + (qs ? '?' + qs : '');
});

// ─── Helpers ─────────────────────────────────────────────────────────
const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const initials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(' ');
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase() || '?';
};
</script>

<template>
    <Head title="Data Lamaran" />
    <AdminLayout title="Data Lamaran">

        <!-- ── Header Banner ──────────────────────────────────────────── -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1e293b] to-[#0F172A] p-6 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-8 -top-8 h-48 w-48 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/3 -bottom-6 h-32 w-32 rounded-full bg-pink-500/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 ring-1 ring-indigo-400/30">
                        <svg class="h-6 w-6 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Data Lamaran</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Pantau seluruh pelamaran mahasiswa secara terpusat</p>
                    </div>
                </div>

                <!-- Status Badges -->
                <div class="flex flex-wrap gap-2">
                    <div class="flex flex-col items-center px-3 py-1.5 bg-white/5 rounded-xl ring-1 ring-white/10">
                        <span class="text-lg font-black text-white">{{ counts?.all ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                    </div>
                    <div class="flex flex-col items-center px-3 py-1.5 bg-purple-500/20 rounded-xl ring-1 ring-purple-400/30">
                        <span class="text-lg font-black text-purple-300">{{ counts?.wawancara ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-purple-400/80 uppercase tracking-wider">Wawancara</span>
                    </div>
                    <div class="flex flex-col items-center px-3 py-1.5 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30">
                        <span class="text-lg font-black text-emerald-300">{{ counts?.lolos ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-emerald-400/80 uppercase tracking-wider">Lolos</span>
                    </div>
                    <div class="flex flex-col items-center px-3 py-1.5 bg-red-500/20 rounded-xl ring-1 ring-red-400/30">
                        <span class="text-lg font-black text-red-300">{{ counts?.['tidak lolos'] ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-red-400/80 uppercase tracking-wider">Tdk Lolos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Filter Bar ─────────────────────────────────────────────── -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center gap-3 animate-fade-in-up delay-100">

            <!-- Status Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-white border border-slate-200 p-1 shadow-sm overflow-x-auto flex-shrink-0">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="changeFilter(tab.key)"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[11px] font-bold transition-all duration-200 whitespace-nowrap"
                    :class="activeTab === tab.key
                        ? tabColors[tab.key] + ' border'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    {{ tab.label }}
                    <span
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-black"
                        :class="activeTab === tab.key ? badgeColors[tab.key] : 'bg-slate-100 text-slate-500'"
                    >{{ counts?.[tab.countKey] ?? 0 }}</span>
                </button>
            </div>

            <!-- Search -->
            <div class="relative flex-1 min-w-0">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    dusk="search-input"
                    placeholder="Cari nama mahasiswa, email, posisi, atau perusahaan..."
                    class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                />
            </div>

            <!-- Ekspor CSV Button -->
            <a
                :href="exportUrl"
                :class="totalCount === 0
                    ? 'pointer-events-none opacity-40 cursor-not-allowed'
                    : 'hover:bg-emerald-600 hover:shadow-md'"
                class="flex shrink-0 items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200"
                :aria-disabled="totalCount === 0"
                :tabindex="totalCount === 0 ? -1 : 0"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Ekspor CSV
                <span v-if="totalCount > 0" class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] font-black">{{ totalCount }}</span>
            </a>
        </div>

        <!-- ── Tabel Data ──────────────────────────────────────────────── -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">

            <!-- Empty State -->
            <div v-if="applications.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                        <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-600">Tidak ada data lamaran</p>
                <p class="text-xs text-slate-400 mt-1">
                    <span v-if="searchQuery">Tidak ada hasil untuk "{{ searchQuery }}"</span>
                    <span v-else-if="activeTab !== 'all'">Belum ada lamaran dengan status ini</span>
                    <span v-else>Belum ada lamaran yang masuk ke sistem</span>
                </p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Mahasiswa</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Universitas / Prodi</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Posisi</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Mitra Perusahaan</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Apply</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="app in applications.data"
                            :key="app.id"
                            class="group hover:bg-slate-50/60 transition-colors"
                        >
                            <!-- Mahasiswa -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white select-none">
                                        {{ initials(app.student_name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ app.student_name }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ app.student_email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Universitas / Prodi -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-700">{{ app.university }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ app.study_program }}</p>
                            </td>

                            <!-- Posisi -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-700 max-w-[160px] truncate" :title="app.position">{{ app.position }}</p>
                            </td>

                            <!-- Mitra Perusahaan -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-slate-100 text-[9px] font-black text-slate-500">
                                        {{ (app.company_name || '?').charAt(0).toUpperCase() }}
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 max-w-[140px] truncate" :title="app.company_name">{{ app.company_name }}</p>
                                </div>
                            </td>

                            <!-- Tanggal Apply -->
                            <td class="px-5 py-4">
                                <p class="text-sm text-slate-600 font-medium whitespace-nowrap">{{ app.applied_at }}</p>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold"
                                    :class="[getStatus(app.status).bg, getStatus(app.status).text]"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="getStatus(app.status).dot"></span>
                                    {{ getStatus(app.status).label }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="applications.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between flex-wrap gap-3">
                <p class="text-xs text-slate-500">
                    Menampilkan
                    <span class="font-bold text-slate-700">{{ applications.from }}–{{ applications.to }}</span>
                    dari
                    <span class="font-bold text-slate-700">{{ applications.total }}</span>
                    data lamaran
                </p>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="link in applications.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        preserve-scroll
                        class="flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                        :class="link.active
                            ? 'bg-slate-900 text-white'
                            : link.url
                                ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'
                                : 'text-slate-300 cursor-not-allowed pointer-events-none'"
                    />
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
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>
