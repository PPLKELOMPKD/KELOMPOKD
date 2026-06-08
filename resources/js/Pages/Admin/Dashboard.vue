<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, ArcElement, Filler
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    stats: Array,
    pipeline: Array,
    lmsPipeline: Array,
    recentUsers: Array,
    pendingActions: Object,
    systemHealth: Object,
    monthlyRegistrations: Array,
    monthlyApplications: Array,
    activityLogs: Array,
    todayRegistrations: Number,
    todayApplications: Number,
});

// --- COLORS & HELPERS ---
const colorMap = {
    blue:    { bg: 'bg-blue-50',    text: 'text-blue-600',    bar: 'bg-blue-500' },
    indigo:  { bg: 'bg-indigo-50',  text: 'text-indigo-600',  bar: 'bg-indigo-500' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', bar: 'bg-emerald-500' },
    purple:  { bg: 'bg-purple-50',  text: 'text-purple-600',  bar: 'bg-purple-500' },
    orange:  { bg: 'bg-orange-50',  text: 'text-orange-600',  bar: 'bg-orange-500' },
    pink:    { bg: 'bg-pink-50',    text: 'text-pink-600',    bar: 'bg-pink-500' },
    teal:    { bg: 'bg-teal-50',    text: 'text-teal-600',    bar: 'bg-teal-500' },
    cyan:    { bg: 'bg-cyan-50',    text: 'text-cyan-600',    bar: 'bg-cyan-500' },
    red:     { bg: 'bg-red-50',     text: 'text-red-600',     bar: 'bg-red-500' },
    slate:   { bg: 'bg-slate-50',   text: 'text-slate-600',   bar: 'bg-slate-500' },
};
const c = (color, type) => colorMap[color]?.[type] ?? colorMap.blue[type];

const roleStyle = { mahasiswa: 'bg-blue-100 text-blue-700', perusahaan: 'bg-emerald-100 text-emerald-700', admin: 'bg-purple-100 text-purple-700' };
const statusStyle = { active: 'bg-emerald-100 text-emerald-700', inactive: 'bg-slate-100 text-slate-500', banned: 'bg-red-100 text-red-600' };
const roleLabel = { mahasiswa: 'Mahasiswa', perusahaan: 'Perusahaan', admin: 'Admin' };
const statusLabel = { active: 'Aktif', inactive: 'Inaktif', banned: 'Banned' };

const today = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

// --- CHART DATA ---

const monthlyRegData = computed(() => {
    return {
        labels: (props.monthlyRegistrations || []).map(d => d.month),
        datasets: [{
            label: 'Pengguna Baru',
            backgroundColor: '#3B82F6',
            borderColor: '#3B82F6', 
            borderWidth: 0, 
            borderRadius: 6,
            data: (props.monthlyRegistrations || []).map(d => d.count),
        }]
    }
});

const barOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8, displayColors: false } },
    scales: {
        y: { beginAtZero: true, grid: { color: '#F1F5F9', drawBorder: false }, ticks: { font: { size: 10 }, color: '#64748B', stepSize: 1 } },
        x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 10 }, color: '#64748B' } }
    }
};

const monthlyAppData = computed(() => {
    return {
        labels: (props.monthlyApplications || []).map(d => d.month),
        datasets: [{
            label: 'Total Lamaran',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderColor: '#10B981', 
            borderWidth: 2, 
            fill: true, 
            tension: 0.4, 
            pointBackgroundColor: '#ffffff', 
            pointBorderColor: '#10B981', 
            pointRadius: 4,
            data: (props.monthlyApplications || []).map(d => d.count),
        }]
    }
});
const lineOptions = { ...barOptions };
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout title="Dashboard Overview">

        <!-- Top Premium Banner & Global Controls -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-[#0F172A] p-6 text-white shadow-xl flex flex-col xl:flex-row xl:items-center justify-between gap-5 animate-fade-in-up">
            <div class="absolute -right-10 -top-10 h-52 w-52 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute right-60 -bottom-8 h-36 w-36 rounded-full bg-purple-500/15 blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10 flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold ring-2 ring-white/20">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Selamat Datang Kembali</p>
                    <h1 class="mt-0.5 text-xl font-bold">{{ $page.props.auth.user.name }}</h1>
                    <p class="text-[11px] text-slate-300 mt-0.5">{{ today }}</p>
                </div>
            </div>
            
            <div class="relative z-10 flex gap-4">
                <div class="flex flex-col justify-center px-4 py-2 bg-white/5 rounded-xl border border-white/10 relative overflow-hidden group cursor-default" title="Kapasitas Server">
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500/10 to-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="h-2 w-2 rounded-full" :class="systemHealth.storage >= 90 ? 'bg-red-500 animate-pulse' : systemHealth.storage >= 75 ? 'bg-yellow-400 animate-pulse' : 'bg-emerald-500'"></div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">System Health</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-20 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                :class="systemHealth.storage >= 90 ? 'bg-red-500' : systemHealth.storage >= 75 ? 'bg-yellow-400' : 'bg-emerald-400'"
                                :style="`width: ${systemHealth.storage}%`"></div>
                        </div>
                        <span class="text-xs font-bold text-white">{{ systemHealth.storage }}%</span>
                    </div>
                    <p class="mt-1 text-[10px] font-semibold" :class="systemHealth.storage >= 90 ? 'text-red-400' : systemHealth.storage >= 75 ? 'text-yellow-400' : 'text-emerald-400'">{{ systemHealth.status }}</p>
                </div>
                <div class="flex flex-col justify-center items-end px-4 py-2 bg-white/5 rounded-xl border border-white/10">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Registrasi Hari Ini</span>
                    <span class="text-xl font-bold text-emerald-400">+{{ todayRegistrations }}</span>
                </div>
                <div class="flex flex-col justify-center items-end px-4 py-2 bg-white/5 rounded-xl border border-white/10">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Lamaran Hari Ini</span>
                    <span class="text-xl font-bold text-blue-400">+{{ todayApplications }}</span>
                </div>
            </div>
        </div>

        <!-- Tindakan Cepat / Quick Actions -->
        <div class="mb-6 animate-fade-in-up delay-100">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Tindakan Cepat</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link :href="route('admin.users.index')" class="group relative overflow-hidden rounded-2xl bg-white p-4 border border-slate-200 shadow-sm transition-all hover:shadow-md hover:border-blue-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 transition-transform group-hover:scale-110">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Manajemen Pengguna</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Kelola akun & hak akses</p>
                    </div>
                </Link>
                
                <Link :href="route('admin.verifications.index')" class="group relative overflow-hidden rounded-2xl bg-white p-4 border border-slate-200 shadow-sm transition-all hover:shadow-md hover:border-emerald-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div v-if="pendingActions.perusahaan > 0" class="absolute top-3 right-3 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white animate-pulse">{{ pendingActions.perusahaan }}</div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 transition-transform group-hover:scale-110">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Verifikasi Perusahaan</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tinjau & setujui mitra</p>
                    </div>
                </Link>

                <Link :href="route('admin.internships.index')" class="group relative overflow-hidden rounded-2xl bg-white p-4 border border-slate-200 shadow-sm transition-all hover:shadow-md hover:border-purple-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div v-if="pendingActions.lowongan > 0" class="absolute top-3 right-3 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white animate-pulse">{{ pendingActions.lowongan }}</div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-50 text-purple-600 transition-transform group-hover:scale-110">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-purple-600 transition-colors">Moderasi Lowongan</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Review post lowongan</p>
                    </div>
                </Link>

                <Link :href="route('admin.events.index')" class="group relative overflow-hidden rounded-2xl bg-white p-4 border border-slate-200 shadow-sm transition-all hover:shadow-md hover:border-orange-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div v-if="pendingActions.events > 0" class="absolute top-3 right-3 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white animate-pulse">{{ pendingActions.events }}</div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-50 text-orange-600 transition-transform group-hover:scale-110">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-orange-600 transition-colors">Manajemen Event</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Setujui atau tolak event</p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Core Stats (8 Cards for perfect 4-col balance) -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6 animate-fade-in-up delay-200">
            <div v-for="stat in stats" :key="stat.label" class="group relative overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl" :class="c(stat.color, 'bar')"></div>
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl transition-transform group-hover:scale-105" :class="[c(stat.color, 'bg'), c(stat.color, 'text')]">
                        <svg v-if="stat.icon==='users'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <svg v-if="stat.icon==='graduation'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <svg v-if="stat.icon==='building'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                        <svg v-if="stat.icon==='briefcase'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <svg v-if="stat.icon==='inbox'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        <svg v-if="stat.icon==='book'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <svg v-if="stat.icon==='activity'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <svg v-if="stat.icon==='calendar'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="text-right">
                        <p class="mt-1 text-[10px] font-bold text-slate-400">{{ stat.sub }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold text-[#0F172A]">{{ stat.value }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#64748B]">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- MIDDLE PERFECT GRID (3 Columns: Chart, Chart, Pipeline) -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5 items-stretch animate-fade-in-up delay-300">
            <!-- COLUMN 1: Registrasi -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col group relative">
                <div class="mb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tren Registrasi</h3>
                        <p class="text-[11px] text-slate-500">6 Bulan Terakhir</p>
                    </div>
                    <div class="flex h-7 w-7 items-center justify-center rounded bg-blue-50 text-blue-600 relative z-0">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                </div>
                <div class="flex-1 relative min-h-[200px]"><Bar v-if="monthlyRegistrations" :data="monthlyRegData" :options="barOptions" /></div>
            </div>
            
            <!-- COLUMN 2: Lamaran -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col group relative">
                <div class="mb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Volume Lamaran</h3>
                        <p class="text-[11px] text-slate-500">6 Bulan Terakhir</p>
                    </div>
                    <div class="flex h-7 w-7 items-center justify-center rounded bg-emerald-50 text-emerald-600 relative z-0">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    </div>
                </div>
                <div class="flex-1 relative min-h-[200px]"><Line v-if="monthlyApplications" :data="monthlyAppData" :options="lineOptions" /></div>
            </div>

            <!-- COLUMN 3: Unified Pipeline -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Status Operasional (Pipeline)</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Distribusi Rekrutmen & Edukasi</p>
                    </div>
                </div>
                <!-- Flex layout side by side for the pipelines -->
                <div class="flex flex-1 p-5 gap-6">
                    <!-- Pipeline Lamaran -->
                    <div class="flex-1 flex flex-col space-y-3 relative">
                        <div class="absolute -right-3 top-0 bottom-0 w-px bg-slate-100"></div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Rekrutmen</h4>
                        <div v-for="item in pipeline" :key="item.label" class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="[c(item.color, 'bg'), c(item.color, 'text')]">
                                <span class="font-bold text-xs">{{ item.value }}</span>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 leading-tight">{{ item.label }}</p>
                        </div>
                    </div>
                    <!-- Pipeline LMS -->
                    <div class="flex-1 flex flex-col space-y-3">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Edukasi / LMS</h4>
                        <div v-for="item in lmsPipeline" :key="item.label" class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="[c(item.color, 'bg'), c(item.color, 'text')]">
                                <span class="font-bold text-xs">{{ item.value }}</span>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 leading-tight">{{ item.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM ROW (Perfectly Aligned Grid) -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-stretch animate-fade-in-up delay-300">
            <!-- LEFT COLUMN (Table) -->
            <div class="xl:col-span-2 flex flex-col">
                <div class="h-full rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">Registrasi Pengguna Terbaru</h3>
                    </div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-5 py-3">Pengguna</th>
                                    <th class="px-5 py-3">Role</th>
                                    <th class="px-5 py-3">Tanggal</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">{{ user.name.charAt(0) }}</div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800">{{ user.name }}</p>
                                                <p class="text-[10px] text-slate-500">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5"><span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold" :class="roleStyle[user.role]">{{ roleLabel[user.role] }}</span></td>
                                    <td class="px-5 py-3.5"><span class="text-xs text-slate-600 font-medium">{{ user.created_at }}</span></td>
                                    <td class="px-5 py-3.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold" :class="statusStyle[user.status]"><span class="h-1.5 w-1.5 rounded-full" :class="user.status==='active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ statusLabel[user.status] }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (Activity Feed) -->
            <div class="flex flex-col">
                <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-sm font-bold text-slate-900">Log Aktivitas Terkini</h3>
                        <Link :href="route('admin.activity-logs.index')" class="text-[10px] font-bold text-blue-600 hover:underline">Semua Log</Link>
                    </div>
                    <div class="relative pl-2 flex-1 flex flex-col justify-between py-1 space-y-4">
                        <div class="absolute left-[11px] top-2 bottom-2 w-px bg-slate-100 z-0"></div>
                        <div v-for="log in activityLogs" :key="log.id" class="relative z-10 flex gap-3 items-start group cursor-default">
                            <div class="flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full ring-4 ring-white transition-transform group-hover:scale-110" :class="c(log.color, 'bg')">
                                <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-[11px] font-bold text-slate-900 leading-tight">
                                    {{ log.name }} <span class="font-medium text-slate-500">{{ log.action }}</span>
                                </p>
                                <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ log.time }} • {{ log.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
</style>
