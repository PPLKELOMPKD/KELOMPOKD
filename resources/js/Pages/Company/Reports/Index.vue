<script setup>
import { ref, computed, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, ArcElement, Filler
} from 'chart.js';
import { Bar, Pie } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    filters: Object,
    stats: Object,
    internships_data: Array,
});

const selectedMonth = ref(props.filters.month || 'all');

const monthsList = [
    { value: 'all', label: 'Semua Bulan' },
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' }
];

watch(selectedMonth, (newMonth) => {
    router.get(route('perusahaan.reports.index'), { month: newMonth }, {
        preserveState: true,
        replace: true
    });
});

const hasApplicants = computed(() => props.stats.total_applicants > 0);

// --- Colors for Pie Chart ---
const pieChartColors = [
    '#6366F1', // Indigo
    '#10B981', // Emerald
    '#F59E0B', // Amber
    '#EF4444', // Red
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#06B6D4', // Cyan
    '#14B8A6', // Teal
    '#F97316', // Orange
    '#3B82F6', // Blue
];

// --- Bar Chart Config ---
const barChartData = computed(() => {
    const labels = props.internships_data.map(item => item.title);
    const data = props.internships_data.map(item => item.applicants_count);
    
    return {
        labels: labels,
        datasets: [
            {
                label: 'Jumlah Pelamar',
                backgroundColor: '#6366F1',
                borderColor: '#6366F1',
                borderWidth: 0,
                borderRadius: 6,
                data: data,
            }
        ]
    };
});

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#F1F5F9',
                drawBorder: false,
            },
            ticks: {
                font: {
                    size: 11,
                    weight: '500',
                },
                color: '#64748B',
                stepSize: 1,
            }
        },
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                font: {
                    size: 11,
                    weight: '500',
                },
                color: '#64748B',
            }
        }
    }
};

// --- Pie Chart Config ---
const pieChartData = computed(() => {
    const labels = props.internships_data.map(item => item.title);
    const data = props.internships_data.map(item => item.applicants_count);
    
    return {
        labels: labels,
        datasets: [
            {
                data: data,
                backgroundColor: pieChartColors.slice(0, labels.length),
                hoverOffset: 4,
            }
        ]
    };
});

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right',
            labels: {
                font: {
                    size: 11,
                    weight: '500',
                },
                color: '#475467',
                usePointStyle: true,
                padding: 15,
            }
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 12,
            cornerRadius: 8,
        }
    }
};

const getProgressBarColor = (percentage) => {
    if (percentage < 50) return 'bg-[#6366F1]'; // Indigo
    if (percentage < 100) return 'bg-[#3B82F6]'; // Blue
    return 'bg-[#10B981]'; // Emerald
};

const exportReport = (format) => {
    alert(`Fitur Export ke ${format} sedang dipersiapkan untuk data rekrutmen periode ${monthsList.find(m => m.value === selectedMonth.value)?.label}.`);
};
</script>

<template>
    <Head title="Laporan & Analitik" />
    <SikaraLayout title="Laporan & Analitik" subtitle="Evaluasi efektivitas publikasi magang dan analisis kebutuhan sumber daya manusia perusahaan Anda.">
        
        <!-- Filter and Top Actions Row -->
        <div class="mt-8 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#475467]">Periode Laporan</p>
                <div class="mt-1.5 relative inline-block text-left w-full sm:w-64">
                    <select 
                        v-model="selectedMonth" 
                        class="block w-full rounded-xl border border-[#d0d5dd] bg-white py-2.5 pl-3 pr-10 text-sm font-medium text-[#344054] shadow-sm focus:border-[#6366F1] focus:ring-1 focus:ring-[#6366F1]"
                    >
                        <option v-for="m in monthsList" :key="m.value" :value="m.value">
                            {{ m.label }}
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-end gap-3 self-end sm:self-auto">
                <button 
                    @click="exportReport('PDF')"
                    class="inline-flex items-center gap-2 rounded-xl border border-[#d0d5dd] bg-white px-4 py-2.5 text-sm font-semibold text-[#344054] shadow-sm transition hover:bg-slate-50 active:scale-95 cursor-pointer"
                >
                    <svg class="h-4 w-4 text-[#475467]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Export PDF
                </button>
                <button 
                    @click="exportReport('Excel')"
                    class="inline-flex items-center gap-2 rounded-xl border border-[#d0d5dd] bg-white px-4 py-2.5 text-sm font-semibold text-[#344054] shadow-sm transition hover:bg-slate-50 active:scale-95 cursor-pointer"
                >
                    <svg class="h-4 w-4 text-[#475467]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                    Export Excel
                </button>
            </div>
        </div>

        <!-- Summary Stats (3 Premium Cards) -->
        <div class="grid gap-5 sm:grid-cols-3 mb-8">
            <!-- Card 1: Lowongan Aktif -->
            <div class="group relative overflow-hidden rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-1 bg-[#6366F1]"></div>
                <div class="flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-[#6366F1] transition-transform group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">PUBLISHED</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold text-[#101828]">{{ stats.active_internships }}</h3>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-[#667085]">Lowongan Aktif</p>
                </div>
            </div>

            <!-- Card 2: Total Pelamar -->
            <div class="group relative overflow-hidden rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-1 bg-[#3B82F6]"></div>
                <div class="flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-[#3B82F6] transition-transform group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">APPLICANTS</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold text-[#101828]">{{ stats.total_applicants }}</h3>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-[#667085]">Total Pelamar</p>
                </div>
            </div>

            <!-- Card 3: Pelamar Diproses -->
            <div class="group relative overflow-hidden rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-1 bg-[#10B981]"></div>
                <div class="flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-[#10B981] transition-transform group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400">PROCESSED</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold text-[#101828]">{{ stats.processed_applicants }}</h3>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-[#667085]">Pelamar Diproses</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="mb-8">
            <!-- Empty state fallback when no applications are available -->
            <div v-if="!hasApplicants" class="rounded-2xl border border-[#eaecf0] bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-400 mb-4">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#101828]">Grafik Belum Tersedia</h3>
                <p class="mt-1.5 text-sm text-[#667085] max-w-sm mx-auto">
                    Data grafik performa lowongan tidak dapat ditampilkan karena belum ada aktivitas pelamaran pada periode ini.
                </p>
            </div>

            <!-- Visualization Grid -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-stretch">
                <!-- Bar Chart: Perbandingan Pelamar antar Lowongan -->
                <div class="lg:col-span-3 rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm flex flex-col">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-[#101828]">Perbandingan Volume Pelamar</h3>
                        <p class="text-xs text-[#667085]">Jumlah pelamar yang mendaftar di masing-masing lowongan</p>
                    </div>
                    <div class="flex-1 relative min-h-[300px]">
                        <Bar :data="barChartData" :options="barChartOptions" />
                    </div>
                </div>

                <!-- Pie Chart: Distribusi Pelamar -->
                <div class="lg:col-span-2 rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm flex flex-col">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-[#101828]">Distribusi Persentase Pelamar</h3>
                        <p class="text-xs text-[#667085]">Proporsi kontribusi pendaftar pada tiap lowongan</p>
                    </div>
                    <div class="flex-1 relative min-h-[300px] flex items-center justify-center">
                        <div class="w-full h-full max-h-[260px] relative">
                            <Pie :data="pieChartData" :options="pieChartOptions" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quota Fulfillment Table -->
        <div class="rounded-2xl border border-[#eaecf0] bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-[#eaecf0]">
                <h3 class="text-base font-bold text-[#101828]">Keterisian Kuota Lowongan Magang</h3>
                <p class="text-xs text-[#667085]">Status kuota, jumlah pendaftar, dan persentase keterisian kuota pada tiap lowongan magang</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-[#475467]">
                        <tr>
                            <th class="px-6 py-4">Lowongan Magang</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Kuota</th>
                            <th class="px-6 py-4 text-center">Jumlah Pelamar</th>
                            <th class="px-6 py-4">Keterisian Kuota</th>
                            <th class="px-6 py-4 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eaecf0]">
                        <tr v-if="!internships_data || internships_data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-[#667085]">
                                Belum ada lowongan magang yang ditambahkan oleh perusahaan Anda.
                            </td>
                        </tr>
                        <tr 
                            v-else 
                            v-for="item in internships_data" 
                            :key="item.id" 
                            class="hover:bg-slate-50/50 transition-colors"
                        >
                            <td class="px-6 py-4.5">
                                <span class="text-sm font-bold text-[#101828] block">{{ item.title }}</span>
                                <span class="text-xs text-[#667085]">ID Lowongan: #{{ item.id }}</span>
                            </td>
                            <td class="px-6 py-4.5 text-center">
                                <span 
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="item.status === 'Aktif' && item.is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                                >
                                    <span 
                                        class="h-1.5 w-1.5 rounded-full" 
                                        :class="item.status === 'Aktif' && item.is_published ? 'bg-emerald-500' : 'bg-red-500'"
                                    ></span>
                                    {{ item.status === 'Aktif' && item.is_published ? 'Aktif' : 'Expired / Ditutup' }}
                                </span>
                            </td>
                            <td class="px-6 py-4.5 text-center text-sm font-semibold text-[#344054]">{{ item.quota }}</td>
                            <td class="px-6 py-4.5 text-center text-sm font-semibold text-[#344054]">{{ item.applicants_count }}</td>
                            <td class="px-6 py-4.5 min-w-[200px]">
                                <div class="flex items-center gap-3">
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-500" 
                                            :class="getProgressBarColor(item.quota_percentage)"
                                            :style="`width: ${Math.min(100, item.quota_percentage)}%`"
                                        ></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4.5 text-right font-bold text-sm" :class="item.quota_percentage >= 100 ? 'text-[#10B981]' : 'text-[#344054]'">
                                {{ item.quota_percentage }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </SikaraLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.grid, .mt-8, .mb-8, .rounded-2xl {
    animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
