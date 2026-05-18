<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, ArcElement, Filler
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    stats: Array,
    pipeline: Array,
    recentUsers: Array,
    recentApplications: Array,
    platformModules: Array,
    userDistribution: Array,
    monthlyRegistrations: Array,
    monthlyApplications: Array,
});

// --- STATE MANAGEMENT ---
const globalDateRange = ref('30_days');
const isExporting = ref(false);

const olapTimeframe = ref('monthly');
const olapRegChartType = ref('bar');
const olapAppChartType = ref('line');

// --- MOCK DATA FOR NEW FEATURES ---
const quickActions = [
    { label: 'Manajemen Pengguna', icon: 'users', color: 'blue' },
    { label: 'Verifikasi Perusahaan', icon: 'building', color: 'emerald' },
    { label: 'Moderasi Lowongan', icon: 'briefcase', color: 'purple' },
    { label: 'Pantau LMS', icon: 'book', color: 'orange' },
];

const pendingTasks = ref([
    { id: 1, title: 'Verifikasi PT Jaya Abadi', desc: 'Dokumen legal SIUP & NPWP telah diunggah.', type: 'urgent', time: '1 jam lalu' },
    { id: 2, title: 'Laporan Lowongan Palsu', desc: '3 mahasiswa melaporkan lowongan "Data Entry".', type: 'danger', time: '3 jam lalu' },
    { id: 3, title: 'Persetujuan Modul LMS', desc: 'Modul "Karya Tulis Ilmiah" menunggu review.', type: 'warning', time: 'Kemarin' },
]);

const systemHealth = ref({ cpu: 24, ram: 42, uptime: '99.98%', online: 156, responseTime: '124ms' });

const activityLogs = ref([
    { id: 1, name: 'Andi Saputra', role: 'Mahasiswa', action: 'Mengunggah tugas LMS', time: '2 menit lalu', color: 'blue' },
    { id: 2, name: 'PT Telkom', role: 'Perusahaan', action: 'Membuka 5 lowongan baru', time: '15 menit lalu', color: 'emerald' },
    { id: 3, name: 'Sistem', role: 'Auto', action: 'Backup database harian selesai', time: '1 jam lalu', color: 'slate' },
    { id: 4, name: 'Siti Rahma', role: 'Mahasiswa', action: 'Mendaftar akun baru', time: '2 jam lalu', color: 'blue' },
    { id: 5, name: 'Admin Rina', role: 'Admin', action: 'Memverifikasi PT Inovasi', time: '3 jam lalu', color: 'purple' },
]);

// --- COLORS & HELPERS ---
const colorMap = {
    blue:    { bg: 'bg-blue-50',    text: 'text-blue-600',    pill: 'bg-blue-100 text-blue-700',    bar: 'bg-blue-500' },
    indigo:  { bg: 'bg-indigo-50',  text: 'text-indigo-600',  pill: 'bg-indigo-100 text-indigo-700',  bar: 'bg-indigo-500' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', pill: 'bg-emerald-100 text-emerald-700', bar: 'bg-emerald-500' },
    purple:  { bg: 'bg-purple-50',  text: 'text-purple-600',  pill: 'bg-purple-100 text-purple-700',  bar: 'bg-purple-500' },
    orange:  { bg: 'bg-orange-50',  text: 'text-orange-600',  pill: 'bg-orange-100 text-orange-700',  bar: 'bg-orange-500' },
    pink:    { bg: 'bg-pink-50',    text: 'text-pink-600',    pill: 'bg-pink-100 text-pink-700',    bar: 'bg-pink-500' },
    teal:    { bg: 'bg-teal-50',    text: 'text-teal-600',    pill: 'bg-teal-100 text-teal-700',    bar: 'bg-teal-500' },
    cyan:    { bg: 'bg-cyan-50',    text: 'text-cyan-600',    pill: 'bg-cyan-100 text-cyan-700',    bar: 'bg-cyan-500' },
    red:     { bg: 'bg-red-50',     text: 'text-red-600',     pill: 'bg-red-100 text-red-700',     bar: 'bg-red-500' },
    slate:   { bg: 'bg-slate-50',   text: 'text-slate-600',   pill: 'bg-slate-100 text-slate-700',   bar: 'bg-slate-500' },
};
const c = (color, type) => colorMap[color]?.[type] ?? colorMap.blue[type];

const roleStyle = { mahasiswa: 'bg-blue-100 text-blue-700', perusahaan: 'bg-emerald-100 text-emerald-700', admin: 'bg-purple-100 text-purple-700' };
const statusStyle = { active: 'bg-emerald-100 text-emerald-700', inactive: 'bg-slate-100 text-slate-500', banned: 'bg-red-100 text-red-600' };
const roleLabel = { mahasiswa: 'Mahasiswa', perusahaan: 'Perusahaan', admin: 'Admin' };
const statusLabel = { active: 'Aktif', inactive: 'Inaktif', banned: 'Banned' };

const today = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

const handleExport = () => {
    isExporting.value = true;
    setTimeout(() => { isExporting.value = false; alert('Laporan berhasil diekspor ke PDF!'); }, 1500);
};

// --- OLAP LOGIC ---
const getQuarter = (monthStr) => {
    const m = monthStr.toLowerCase();
    if (m.includes('jan') || m.includes('feb') || m.includes('mar')) return 'Q1';
    if (m.includes('apr') || m.includes('mei') || m.includes('may') || m.includes('jun')) return 'Q2';
    if (m.includes('jul') || m.includes('agu') || m.includes('aug') || m.includes('sep')) return 'Q3';
    if (m.includes('okt') || m.includes('oct') || m.includes('nov') || m.includes('des') || m.includes('dec')) return 'Q4';
    return monthStr;
};

const aggregateData = (data, timeframe) => {
    if (!data) return [];
    const sourceData = [...data].reverse();
    if (timeframe === 'monthly') return sourceData;
    
    const grouped = {};
    sourceData.forEach(item => {
        const parts = item.month.split(' ');
        const year = parts.length > 1 ? ` ${parts[1]}` : '';
        const q = getQuarter(parts[0]) + year;
        if (!grouped[q]) grouped[q] = 0;
        grouped[q] += item.count;
    });
    return Object.keys(grouped).map(k => ({ month: k, count: grouped[k] }));
};

// --- CHART DATA ---
const userDistData = computed(() => {
    if(!props.userDistribution) return { labels: [], datasets: [] };
    return {
        labels: props.userDistribution.map(d => d.label),
        datasets: [{
            backgroundColor: props.userDistribution.map(d => d.color),
            data: props.userDistribution.map(d => d.value),
            borderWidth: 0, hoverOffset: 4,
        }]
    }
});

const doughnutOptions = {
    responsive: true, maintainAspectRatio: false, cutout: '75%',
    plugins: {
        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { family: "'Inter', sans-serif", size: 11 } } },
        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8, displayColors: false }
    }
};

const monthlyRegData = computed(() => {
    const data = aggregateData(props.monthlyRegistrations, olapTimeframe.value);
    return {
        labels: data.map(d => d.month),
        datasets: [{
            label: 'Pengguna Baru',
            backgroundColor: olapRegChartType.value === 'bar' ? '#3B82F6' : 'rgba(59, 130, 246, 0.1)',
            borderColor: '#3B82F6', borderWidth: olapRegChartType.value === 'line' ? 2 : 0, borderRadius: olapRegChartType.value === 'bar' ? 6 : 0,
            fill: olapRegChartType.value === 'line', tension: 0.4, pointBackgroundColor: '#ffffff', pointBorderColor: '#3B82F6', pointRadius: 4,
            data: data.map(d => d.count),
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
    const data = aggregateData(props.monthlyApplications, olapTimeframe.value);
    return {
        labels: data.map(d => d.month),
        datasets: [{
            label: 'Total Lamaran',
            backgroundColor: olapAppChartType.value === 'bar' ? '#10B981' : 'rgba(16, 185, 129, 0.1)',
            borderColor: '#10B981', borderWidth: olapAppChartType.value === 'line' ? 2 : 0, borderRadius: olapAppChartType.value === 'bar' ? 6 : 0,
            fill: olapAppChartType.value === 'line', tension: 0.4, pointBackgroundColor: '#ffffff', pointBorderColor: '#10B981', pointRadius: 4,
            data: data.map(d => d.count),
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
            
            <div class="relative z-10 flex flex-wrap items-center gap-3 bg-white/5 p-2 rounded-2xl border border-white/10">
                <div class="flex items-center gap-2 px-2">
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <select v-model="globalDateRange" class="bg-transparent border-none text-white text-xs font-semibold py-1.5 pl-0 pr-6 focus:ring-0 cursor-pointer [&>option]:text-slate-900 appearance-none">
                        <option value="today">Hari Ini</option>
                        <option value="7_days">7 Hari Terakhir</option>
                        <option value="30_days">30 Hari Terakhir</option>
                        <option value="this_year">Tahun Ini</option>
                    </select>
                </div>
                <div class="w-px h-6 bg-white/20"></div>
                <button @click="handleExport" :disabled="isExporting" class="flex items-center gap-2 rounded-xl bg-blue-500/20 hover:bg-blue-500/40 border border-blue-500/30 px-4 py-1.5 text-xs font-bold text-blue-300 transition-all disabled:opacity-50">
                    <svg v-if="!isExporting" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    {{ isExporting ? 'Proses...' : 'Ekspor' }}
                </button>
            </div>
        </div>

        <!-- Core Stats (Full Width) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6 animate-fade-in-up delay-100">
            <div v-for="stat in stats" :key="stat.label" class="group relative overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl" :class="c(stat.color, 'bar')"></div>
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl transition-transform group-hover:scale-105" :class="[c(stat.color, 'bg'), c(stat.color, 'text')]">
                        <svg v-if="stat.icon==='users'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <svg v-if="stat.icon==='graduation'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <svg v-if="stat.icon==='building'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                        <svg v-if="stat.icon==='briefcase'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <svg v-if="stat.icon==='inbox'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        <svg v-if="stat.icon==='calendar'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <svg v-if="stat.icon==='book'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <svg v-if="stat.icon==='chart'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-2xl font-bold text-[#0F172A]">{{ stat.value }}</p>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wider text-[#64748B]">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- MAIN TWO-COLUMN SPLIT LAYOUT -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 animate-fade-in-up delay-200">
            
            <!-- LEFT COLUMN (Data & Charts) -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- OLAP Controls -->
                <div class="flex items-center justify-between bg-white p-3 px-5 rounded-2xl border border-[#E2E8F0] shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <h3 class="text-sm font-bold text-[#0F172A]">Kendali Analitik OLAP</h3>
                    </div>
                    <select v-model="olapTimeframe" class="bg-slate-50 border-slate-200 text-xs font-semibold text-[#0F172A] rounded-lg py-1.5 pl-3 pr-8 focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer">
                        <option value="monthly">Drill Down (Bulanan)</option>
                        <option value="quarterly">Roll Up (Kuartalan)</option>
                    </select>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Doughnut -->
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm flex flex-col">
                        <div class="mb-3">
                            <h3 class="text-sm font-bold text-[#0F172A]">Distribusi Pengguna</h3>
                            <p class="text-[11px] text-[#64748B]">Proporsi berdasarkan role</p>
                        </div>
                        <div class="flex-1 relative min-h-[180px]"><Doughnut v-if="userDistribution" :data="userDistData" :options="doughnutOptions" /></div>
                    </div>
                    <!-- Registrations -->
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm flex flex-col group relative">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-[#0F172A]">Registrasi Baru</h3>
                                <p class="text-[11px] text-[#64748B]">Tren pendaftaran akun</p>
                            </div>
                            <select v-model="olapRegChartType" class="opacity-0 group-hover:opacity-100 absolute top-4 right-14 bg-white border-slate-200 text-[10px] font-bold rounded py-1 pl-2 pr-6 shadow-sm cursor-pointer transition-opacity z-10">
                                <option value="bar">Bar</option><option value="line">Line</option>
                            </select>
                            <div class="flex h-7 w-7 items-center justify-center rounded bg-blue-50 text-blue-600 relative z-0">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                        </div>
                        <div class="flex-1 relative min-h-[180px]"><component :is="olapRegChartType === 'bar' ? Bar : Line" v-if="monthlyRegistrations" :data="monthlyRegData" :options="barOptions" /></div>
                    </div>
                </div>

                <!-- Charts Row 2 (Full width within left col) -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm flex flex-col group relative">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-[#0F172A]">Volume Lamaran Magang</h3>
                            <p class="text-[11px] text-[#64748B]">Tren pengiriman lamaran ke platform</p>
                        </div>
                        <select v-model="olapAppChartType" class="opacity-0 group-hover:opacity-100 absolute top-4 right-14 bg-white border-slate-200 text-[10px] font-bold rounded py-1 pl-2 pr-6 shadow-sm cursor-pointer transition-opacity z-10">
                            <option value="line">Line</option><option value="bar">Bar</option>
                        </select>
                        <div class="flex h-7 w-7 items-center justify-center rounded bg-emerald-50 text-emerald-600 relative z-0">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                    </div>
                    <div class="flex-1 relative min-h-[220px]"><component :is="olapAppChartType === 'line' ? Line : Bar" v-if="monthlyApplications" :data="monthlyAppData" :options="lineOptions" /></div>
                </div>

                <!-- Application Pipeline -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-[#0F172A]">Pipeline Lamaran Seluruh Platform</h3>
                    </div>
                    <div class="relative flex items-start justify-between px-2">
                        <div class="absolute left-10 right-10 top-5 h-0.5 bg-slate-100 z-0"></div>
                        <div v-for="item in pipeline" :key="item.label" class="relative z-10 flex flex-col items-center flex-1">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full ring-4 ring-white shadow-sm" :class="[c(item.color, 'bg'), c(item.color, 'text')]">
                                <span class="font-black text-sm">{{ item.value }}</span>
                            </div>
                            <p class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">{{ item.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-[#F1F5F9] px-5 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-[#0F172A]">Registrasi Pengguna Terbaru</h3>
                        <a href="#" class="text-[10px] font-bold text-blue-600 hover:underline">Lihat Semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-5 py-2.5">Pengguna</th>
                                    <th class="px-5 py-2.5">Role</th>
                                    <th class="px-5 py-2.5">Status</th>
                                    <th class="px-5 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold text-white">{{ user.name.charAt(0) }}</div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800">{{ user.name }}</p>
                                                <p class="text-[10px] text-slate-500">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold" :class="roleStyle[user.role]">{{ roleLabel[user.role] }}</span></td>
                                    <td class="px-5 py-3"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold" :class="statusStyle[user.status]"><span class="h-1.5 w-1.5 rounded-full" :class="user.status==='active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>{{ statusLabel[user.status] }}</span></td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button class="rounded border border-slate-200 p-1 text-slate-400 hover:bg-white hover:text-blue-600 transition-colors shadow-sm" title="Lihat"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                            <button class="rounded border border-slate-200 p-1 text-slate-400 hover:bg-white hover:text-red-600 transition-colors shadow-sm" title="Aksi Lainnya"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            
            <!-- RIGHT COLUMN (Sidebar Widgets) -->
            <div class="space-y-6">
                
                <!-- Quick Actions -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-bold text-[#0F172A] mb-3">Tindakan Cepat</h3>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button v-for="action in quickActions" :key="action.label" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-white hover:border-slate-300 hover:shadow-sm transition-all group">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm border border-slate-100 group-hover:scale-110 transition-transform mb-2" :class="c(action.color, 'text')">
                                <svg v-if="action.icon==='users'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <svg v-if="action.icon==='building'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                                <svg v-if="action.icon==='briefcase'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <svg v-if="action.icon==='book'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                            <span class="text-[10px] font-bold text-slate-600 text-center leading-tight">{{ action.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-[#0F172A]">Tugas Prioritas</h3>
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-[10px] font-bold text-red-600">{{ pendingTasks.length }}</span>
                    </div>
                    <div class="space-y-2.5">
                        <div v-for="task in pendingTasks" :key="task.id" class="flex gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition-colors shadow-sm">
                            <div class="mt-0.5 flex h-2 w-2 shrink-0 rounded-full" :class="task.type==='urgent' ? 'bg-red-500' : task.type==='warning' ? 'bg-amber-500' : 'bg-blue-500'"></div>
                            <div>
                                <p class="text-xs font-bold text-[#0F172A]">{{ task.title }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500 leading-tight">{{ task.desc }}</p>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ task.time }}</p>
                                    <button class="text-[9px] font-bold text-blue-600 hover:underline">Tindak Lanjuti</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="rounded-2xl bg-[#0F172A] p-5 text-white shadow-sm relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl"></div>
                    <div class="flex items-center justify-between mb-4 relative z-10">
                        <h3 class="text-sm font-bold">Kesehatan Sistem</h3>
                        <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center justify-between bg-white/5 p-2 px-3 rounded-lg border border-white/10">
                            <span class="text-xs font-medium text-slate-300">Pengguna Aktif</span>
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-2">
                                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                                {{ systemHealth.online }} Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-1">
                            <span class="text-[11px] text-slate-400">Uptime Server</span>
                            <span class="text-[11px] font-bold text-white">{{ systemHealth.uptime }}</span>
                        </div>
                        <div class="flex items-center justify-between px-1">
                            <span class="text-[11px] text-slate-400">Response Latency</span>
                            <span class="text-[11px] font-bold text-white">{{ systemHealth.responseTime }}</span>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <div class="flex justify-between text-[10px] mb-1.5 font-bold text-slate-300">
                                <span>Load CPU ({{ systemHealth.cpu }}%)</span>
                                <span>Memori ({{ systemHealth.ram }}%)</span>
                            </div>
                            <div class="flex gap-2">
                                <div class="h-1.5 w-1/2 bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-blue-500 rounded-full" :style="{width: systemHealth.cpu + '%'}"></div></div>
                                <div class="h-1.5 w-1/2 bg-slate-700 rounded-full overflow-hidden"><div class="h-full bg-purple-500 rounded-full" :style="{width: systemHealth.ram + '%'}"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-[#0F172A]">Log Aktivitas Live</h3>
                        <a href="#" class="text-[10px] font-bold text-blue-600 hover:underline">Semua Log</a>
                    </div>
                    <div class="relative pl-2">
                        <div class="absolute left-[11px] top-2 bottom-2 w-px bg-slate-100 z-0"></div>
                        <div class="space-y-4">
                            <div v-for="log in activityLogs" :key="log.id" class="relative z-10 flex gap-3 items-start group cursor-default">
                                <div class="flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full ring-4 ring-white transition-transform group-hover:scale-110" :class="c(log.color, 'bg')">
                                    <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-[11px] font-bold text-[#0F172A] leading-tight">
                                        {{ log.name }} <span class="font-medium text-slate-500">{{ log.action }}</span>
                                    </p>
                                    <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ log.time }} • {{ log.role }}</p>
                                </div>
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
</style>
