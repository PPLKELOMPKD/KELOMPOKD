<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    internships: {
        type: Array,
        default: () => []
    }
});

// --- State Kalender ---
const today = new Date();
const currentMonth = ref(today.getMonth()); // 0-11
const currentYear = ref(today.getFullYear());

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// --- State Filter ---
const filters = ref({
    created: true,
    approved: true,
    rejected: true,
    deadline: true
});

// --- State Drawer/Side Panel ---
const isDrawerOpen = ref(false);
const selectedDate = ref('');
const selectedEvents = ref([]);
const activeDetailInternship = ref(null); // Untuk menampilkan mini-timeline lowongan di drawer

// --- Helper Formatting ---
const toLocalDateString = (dateInput) => {
    if (!dateInput) return null;
    const d = new Date(dateInput);
    if (isNaN(d.getTime())) return null;
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const date = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${date}`;
};

const formatDatePretty = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatTimePretty = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
};

// --- Pengelompokkan Event Berdasarkan Tanggal ---
const eventsByDate = computed(() => {
    const grouped = {};
    
    props.internships.forEach(internship => {
        // 1. Event Dibuat (Biru)
        const createdDate = toLocalDateString(internship.created_at);
        if (createdDate) {
            if (!grouped[createdDate]) grouped[createdDate] = [];
            grouped[createdDate].push({
                id: `${internship.id}-created`,
                type: 'created',
                label: 'Lowongan Dibuat',
                colorClass: 'bg-blue-500',
                textClass: 'text-blue-700',
                bgClass: 'bg-blue-50',
                borderClass: 'border-blue-200',
                icon: 'plus',
                internship
            });
        }
        
        // 2. Event Dimoderasi (Hijau / Merah)
        if (internship.moderated_at) {
            const moderatedDate = toLocalDateString(internship.moderated_at);
            if (moderatedDate) {
                if (!grouped[moderatedDate]) grouped[moderatedDate] = [];
                if (internship.moderation_status === 'approved') {
                    grouped[moderatedDate].push({
                        id: `${internship.id}-approved`,
                        type: 'approved',
                        label: 'Lowongan Disetujui',
                        colorClass: 'bg-emerald-500',
                        textClass: 'text-emerald-700',
                        bgClass: 'bg-emerald-50',
                        borderClass: 'border-emerald-200',
                        icon: 'check',
                        internship
                    });
                } else if (internship.moderation_status === 'rejected') {
                    grouped[moderatedDate].push({
                        id: `${internship.id}-rejected`,
                        type: 'rejected',
                        label: 'Lowongan Ditolak / Takedown',
                        colorClass: 'bg-red-500',
                        textClass: 'text-red-700',
                        bgClass: 'bg-red-50',
                        borderClass: 'border-red-200',
                        icon: 'x',
                        internship
                    });
                }
            }
        }
        
        // 3. Event Batas Waktu / Deadline (Abu-abu)
        if (internship.deadline_at) {
            const deadlineDate = toLocalDateString(internship.deadline_at);
            if (deadlineDate) {
                if (!grouped[deadlineDate]) grouped[deadlineDate] = [];
                grouped[deadlineDate].push({
                    id: `${internship.id}-deadline`,
                    type: 'deadline',
                    label: 'Batas Pendaftaran (Deadline)',
                    colorClass: 'bg-slate-500',
                    textClass: 'text-slate-700',
                    bgClass: 'bg-slate-50',
                    borderClass: 'border-slate-200',
                    icon: 'clock',
                    internship
                });
            }
        }
    });
    
    return grouped;
});

// --- Logika Navigasi Kalender ---
const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const goToday = () => {
    currentMonth.value = today.getMonth();
    currentYear.value = today.getFullYear();
};

// --- Logika Render Grid Kalender ---
const calendarCells = computed(() => {
    const cells = [];
    const totalDays = new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
    const firstDayIndex = new Date(currentYear.value, currentMonth.value, 1).getDay(); // 0 = Sun

    // Empty cells before the 1st
    for (let i = 0; i < firstDayIndex; i++) {
        cells.push({ date: null, isCurrentMonth: false, dateString: '' });
    }

    // Days in current month
    for (let day = 1; day <= totalDays; day++) {
        const dateString = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        cells.push({
            date: day,
            dateString,
            isCurrentMonth: true
        });
    }
    return cells;
});

const getEventsForDate = (dateString) => {
    if (!dateString) return [];
    const allEvents = eventsByDate.value[dateString] || [];
    return allEvents.filter(e => filters.value[e.type]);
};

// --- Logika Interaksi Tanggal ---
const selectDate = (cell) => {
    if (!cell.dateString) return;
    
    const events = getEventsForDate(cell.dateString);
    selectedDate.value = formatDatePretty(cell.dateString);
    selectedEvents.value = events;
    activeDetailInternship.value = null; // reset detail lowongan
    isDrawerOpen.value = true;
};

const viewInternshipTimeline = (internship) => {
    activeDetailInternship.value = internship;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    activeDetailInternship.value = null;
};
</script>

<template>
    <Head title="Kalender Aktivitas Lowongan" />
    <AdminLayout title="Kalender Lowongan">

        <!-- Header Banner -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] p-6 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-6 -top-6 h-40 w-40 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/3 -bottom-8 h-32 w-32 rounded-full bg-purple-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 ring-1 ring-blue-400/30">
                        <svg class="h-6 w-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Kalender & Timeline Lowongan</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Pantau siklus pembuatan, persetujuan, penolakan, dan batas waktu pendaftaran lowongan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 animate-fade-in-up delay-100">
            
            <!-- ══ KALENDER GRID (3 Kolom di XL) ════════════════════════════ -->
            <div class="xl:col-span-3 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                <!-- Kalender Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-800">{{ months[currentMonth] }} {{ currentYear }}</h2>
                        <span class="text-xs text-slate-400 font-bold px-2 py-0.5 bg-slate-50 border border-slate-200 rounded-md">Bulanan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="prevMonth"
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 transition"
                            title="Bulan Sebelumnya"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button 
                            @click="goToday"
                            class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                        >
                            Hari Ini
                        </button>
                        <button 
                            @click="nextMonth"
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 transition"
                            title="Bulan Berikutnya"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Grid Kalender -->
                <div class="flex-1 grid grid-cols-7 gap-px bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                    <!-- Hari dalam seminggu -->
                    <div 
                        v-for="day in daysOfWeek" 
                        :key="day" 
                        class="bg-slate-50 py-3 text-center text-xs font-black text-slate-500 border-b border-slate-200 uppercase tracking-wider"
                    >
                        {{ day }}
                    </div>

                    <!-- Tanggal-tanggal -->
                    <div 
                        v-for="(cell, index) in calendarCells" 
                        :key="index"
                        @click="selectDate(cell)"
                        class="bg-white min-h-[100px] p-2 flex flex-col justify-between transition group cursor-pointer"
                        :class="[
                            cell.isCurrentMonth ? 'hover:bg-blue-50/20' : 'bg-slate-50/40 text-slate-300 pointer-events-none'
                        ]"
                    >
                        <!-- Angka Tanggal -->
                        <div class="flex justify-between items-center">
                            <span 
                                class="text-xs font-bold px-2 py-0.5 rounded-lg transition"
                                :class="[
                                    cell.isCurrentMonth ? 'text-slate-700 group-hover:text-blue-700' : 'text-slate-300',
                                    cell.dateString === toLocalDateString(today) ? 'bg-blue-600 text-white font-extrabold shadow-sm shadow-blue-300 group-hover:text-white' : ''
                                ]"
                            >
                                {{ cell.date }}
                            </span>
                            <span v-if="getEventsForDate(cell.dateString).length > 0" class="text-[9px] font-extrabold text-slate-400 bg-slate-100 px-1 rounded">
                                {{ getEventsForDate(cell.dateString).length }} Akt.
                            </span>
                        </div>

                        <!-- Indikator Dots Event -->
                        <div class="mt-2 space-y-1">
                            <div 
                                v-for="event in getEventsForDate(cell.dateString).slice(0, 3)" 
                                :key="event.id"
                                class="flex items-center gap-1.5 rounded px-1.5 py-0.5 text-[9px] font-bold truncate transition-all duration-150"
                                :class="[event.bgClass, event.textClass, event.borderClass, 'border']"
                            >
                                <span class="h-1.5 w-1.5 rounded-full shrink-0" :class="event.colorClass"></span>
                                <span class="truncate">{{ event.internship.title }}</span>
                            </div>
                            <div 
                                v-if="getEventsForDate(cell.dateString).length > 3" 
                                class="text-[8px] font-black text-center text-slate-400 bg-slate-50 border border-slate-100 rounded py-0.5"
                            >
                                +{{ getEventsForDate(cell.dateString).length - 3 }} lainnya
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ SIDEBAR FILTER (1 Kolom di XL) ══════════════════════════ -->
            <div class="space-y-6">
                <!-- Panel Filter Kategori -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 border-b border-slate-50 pb-2">Filter Legenda</h3>
                    <div class="space-y-3.5">
                        
                        <!-- Dibuat -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                v-model="filters.created" 
                                class="rounded text-blue-600 focus:ring-blue-500 border-slate-300 h-4 w-4"
                            />
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-blue-500 shadow-sm shadow-blue-200"></span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Lowongan Dibuat</span>
                            </div>
                        </label>

                        <!-- Disetujui -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                v-model="filters.approved" 
                                class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 h-4 w-4"
                            />
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Lowongan Disetujui</span>
                            </div>
                        </label>

                        <!-- Ditolak -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                v-model="filters.rejected" 
                                class="rounded text-red-600 focus:ring-red-500 border-slate-300 h-4 w-4"
                            />
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-500 shadow-sm shadow-red-200"></span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Ditolak / Takedown</span>
                            </div>
                        </label>

                        <!-- Deadline -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input 
                                type="checkbox" 
                                v-model="filters.deadline" 
                                class="rounded text-slate-600 focus:ring-slate-500 border-slate-300 h-4 w-4"
                            />
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-slate-500 shadow-sm shadow-slate-200"></span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Batas Waktu (Deadline)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Informasi Petunjuk Kalender -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-5">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div>
                            <h4 class="text-xs font-bold text-blue-800">Petunjuk Navigasi</h4>
                            <p class="text-[11px] text-blue-700/80 mt-1.5 leading-relaxed">
                                Klik pada salah satu kotak tanggal di kalender yang memiliki penanda untuk membuka panel laci samping (*drawer*) detail riwayat aktivitas lowongan pada hari tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ LACI SAMPING (DRAWER POPUP) ═══════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isDrawerOpen" class="fixed inset-0 z-50 flex justify-end" @click.self="closeDrawer">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs"></div>

                    <!-- Panel Box -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out transform"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transition duration-200 ease-in transform"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div class="relative z-10 w-full max-w-lg bg-white h-screen flex flex-col shadow-2xl overflow-hidden">
                            <!-- Drawer Header -->
                            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between shrink-0">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800">Riwayat Aktivitas</h3>
                                    <p class="text-[11px] text-slate-400 font-semibold mt-0.5">{{ selectedDate }}</p>
                                </div>
                                <button 
                                    @click="closeDrawer"
                                    class="p-2 text-slate-400 hover:bg-slate-100 rounded-xl hover:text-slate-600 transition"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>

                            <!-- Drawer Body -->
                            <div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar">
                                
                                <!-- Mode 1: Tampilkan Daftar Aktivitas pada Tanggal Tersebut -->
                                <template v-if="!activeDetailInternship">
                                    <div v-if="selectedEvents.length === 0" class="text-center py-20">
                                        <svg class="h-10 w-10 text-slate-300 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
                                        <p class="text-xs font-bold text-slate-500">Tidak ada aktivitas terfilter</p>
                                    </div>
                                    <div v-else class="space-y-4">
                                        <div 
                                            v-for="event in selectedEvents" 
                                            :key="event.id"
                                            class="border rounded-xl p-4 transition-all duration-200 bg-white"
                                            :class="[event.borderClass]"
                                        >
                                            <div class="flex justify-between items-start mb-2.5">
                                                <span 
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black"
                                                    :class="[event.bgClass, event.textClass]"
                                                >
                                                    <span class="h-1.5 w-1.5 rounded-full" :class="event.colorClass"></span>
                                                    {{ event.label }}
                                                </span>
                                                <button
                                                    @click="viewInternshipTimeline(event.internship)"
                                                    class="text-[10px] font-bold text-blue-600 hover:underline flex items-center gap-1"
                                                >
                                                    Timeline Alur
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                                </button>
                                            </div>

                                            <h4 class="text-sm font-black text-slate-800 leading-snug">{{ event.internship.title }}</h4>
                                            <p class="text-xs text-slate-500 mt-1 font-semibold">{{ event.internship.company_name }}</p>

                                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                                <span class="text-slate-400 font-medium">{{ event.internship.location }} · {{ event.internship.work_type }}</span>
                                                <Link 
                                                    :href="route('admin.internships.show', event.internship.id)"
                                                    class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 text-white font-bold px-3 py-1.5 rounded-lg text-[10px] transition-colors"
                                                >
                                                    Review
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Mode 2: Tampilkan Detail Garis Waktu Alur (Timeline) Spesifik Lowongan -->
                                <template v-else>
                                    <div class="mb-4">
                                        <button 
                                            @click="activeDetailInternship = null"
                                            class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1.5 mb-4"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                            Kembali ke daftar aktivitas
                                        </button>
                                        
                                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Informasi Lowongan</h4>
                                            <h3 class="text-sm font-black text-slate-800 mt-2">{{ activeDetailInternship.title }}</h3>
                                            <p class="text-xs text-slate-500 mt-0.5 font-semibold">{{ activeDetailInternship.company_name }}</p>
                                        </div>

                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-5 border-b border-slate-50 pb-2">Timeline Alur Lowongan</h4>

                                        <!-- Vertikal Timeline Component -->
                                        <div class="relative border-l-2 border-slate-200 ml-4 space-y-8 py-2">
                                            
                                            <!-- Titik 1: Lowongan Dibuat -->
                                            <div class="relative pl-6">
                                                <div class="absolute -left-2 top-0.5 h-3.5 w-3.5 rounded-full bg-blue-500 border-2 border-white shadow-sm"></div>
                                                <div>
                                                    <h5 class="text-xs font-bold text-slate-800">Lowongan Dibuat oleh Mitra</h5>
                                                    <p class="text-[10px] text-slate-400 mt-0.5">Oleh Mitra Perusahaan</p>
                                                    <span class="inline-block text-[9px] font-semibold text-slate-500 mt-1.5 bg-slate-100 px-2 py-0.5 rounded">
                                                        {{ formatDatePretty(activeDetailInternship.created_at) }} · {{ formatTimePretty(activeDetailInternship.created_at) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Titik 2: Status Moderasi (Jika sudah diproses) -->
                                            <div class="relative pl-6" v-if="activeDetailInternship.moderated_at">
                                                <!-- Jika Approved -->
                                                <template v-if="activeDetailInternship.moderation_status === 'approved'">
                                                    <div class="absolute -left-2 top-0.5 h-3.5 w-3.5 rounded-full bg-emerald-500 border-2 border-white shadow-sm"></div>
                                                    <div>
                                                        <h5 class="text-xs font-bold text-emerald-800">Disetujui & Diterbitkan</h5>
                                                        <p class="text-[10px] text-slate-400 mt-0.5">Lowongan lolos moderasi dan aktif ditayangkan</p>
                                                        <span class="inline-block text-[9px] font-semibold text-emerald-700 mt-1.5 bg-emerald-50 px-2 py-0.5 rounded">
                                                            {{ formatDatePretty(activeDetailInternship.moderated_at) }} · {{ formatTimePretty(activeDetailInternship.moderated_at) }}
                                                        </span>
                                                    </div>
                                                </template>
                                                <!-- Jika Rejected -->
                                                <template v-else-if="activeDetailInternship.moderation_status === 'rejected'">
                                                    <div class="absolute -left-2 top-0.5 h-3.5 w-3.5 rounded-full bg-red-500 border-2 border-white shadow-sm"></div>
                                                    <div>
                                                        <h5 class="text-xs font-bold text-red-800">Ditolak / Dicabut dari Penayangan</h5>
                                                        <p class="text-[10px] text-slate-400 mt-0.5">Status ditolak oleh admin</p>
                                                        <span class="inline-block text-[9px] font-semibold text-red-700 mt-1.5 bg-red-50 px-2 py-0.5 rounded">
                                                            {{ formatDatePretty(activeDetailInternship.moderated_at) }} · {{ formatTimePretty(activeDetailInternship.moderated_at) }}
                                                        </span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Titik 2b: Status Pending (Jika belum dimoderasi) -->
                                            <div class="relative pl-6" v-else>
                                                <div class="absolute -left-2 top-0.5 h-3.5 w-3.5 rounded-full bg-amber-500 border-2 border-white shadow-sm"></div>
                                                <div>
                                                    <h5 class="text-xs font-bold text-amber-800">Menunggu Tinjauan Admin</h5>
                                                    <p class="text-[10px] text-slate-400 mt-0.5">Masuk ke antrean moderasi lowongan</p>
                                                    <span class="inline-block text-[9px] font-semibold text-amber-700 mt-1.5 bg-amber-50 px-2 py-0.5 rounded">
                                                        Dalam Proses Antrean
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Titik 3: Batas Waktu / Deadline -->
                                            <div class="relative pl-6">
                                                <div 
                                                    class="absolute -left-2 top-0.5 h-3.5 w-3.5 rounded-full border-2 border-white shadow-sm"
                                                    :class="new Date(activeDetailInternship.deadline_at) < today ? 'bg-red-400' : 'bg-slate-400'"
                                                ></div>
                                                <div>
                                                    <h5 class="text-xs font-bold text-slate-800">Batas Waktu Pelamaran (Deadline)</h5>
                                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                                        <span v-if="new Date(activeDetailInternship.deadline_at) < today" class="text-red-600 font-bold">Masa pendaftaran berakhir</span>
                                                        <span v-else>Pendaftaran akan ditutup pada tanggal ini</span>
                                                    </p>
                                                    <span 
                                                        class="inline-block text-[9px] font-semibold mt-1.5 px-2 py-0.5 rounded"
                                                        :class="new Date(activeDetailInternship.deadline_at) < today ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600'"
                                                    >
                                                        {{ formatDatePretty(activeDetailInternship.deadline_at) }}
                                                    </span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #E2E8F0;
    border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
}
</style>
