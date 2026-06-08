<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    completedEvents: { type: Array,   default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
    isMahasiswa:     { type: Boolean, default: false },
});

// ── Filter & Search ──────────────────────────────────────────────────────────
const keyword  = ref('');
const filterKat = ref('');
const filterTipe = ref('');
const sortBy    = ref('latest'); // latest | rating_high | rating_low

const filtered = computed(() => {
    let list = [...props.completedEvents];

    if (keyword.value.trim()) {
        const q = keyword.value.toLowerCase();
        list = list.filter(e =>
            (e.title || '').toLowerCase().includes(q) ||
            (e.company?.name || '').toLowerCase().includes(q) ||
            (e.description || '').toLowerCase().includes(q)
        );
    }

    if (filterKat.value) {
        list = list.filter(e => (e.category || '') === filterKat.value);
    }

    if (filterTipe.value) {
        list = list.filter(e => (e.type || '') === filterTipe.value);
    }

    if (sortBy.value === 'rating_high') {
        list = list.sort((a, b) => (b.avg_rating ?? 0) - (a.avg_rating ?? 0));
    } else if (sortBy.value === 'rating_low') {
        list = list.sort((a, b) => (a.avg_rating ?? 0) - (b.avg_rating ?? 0));
    }
    // 'latest' → default order dari server (sudah latest)

    return list;
});

// ── Detail Modal ─────────────────────────────────────────────────────────────
const selectedEvent   = ref(null);
const showDetailModal = ref(false);

const openDetail = (event) => {
    selectedEvent.value   = event;
    showDetailModal.value = true;
};
const closeDetail = () => {
    showDetailModal.value = false;
    selectedEvent.value   = null;
};

// ── Stats ────────────────────────────────────────────────────────────────────
const totalEvents   = computed(() => props.completedEvents.length);
const ratedEvents   = computed(() => props.completedEvents.filter(e => e.avg_rating).length);
const overallAvg    = computed(() => {
    const rated = props.completedEvents.filter(e => e.avg_rating);
    if (!rated.length) return null;
    const sum = rated.reduce((acc, e) => acc + Number(e.avg_rating), 0);
    return (sum / rated.length).toFixed(1);
});
const totalReviews  = computed(() =>
    props.completedEvents.reduce((acc, e) => acc + (e.rating_count || 0), 0)
);

// ── Helpers ──────────────────────────────────────────────────────────────────
const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
};
const formatTime = (t) => t ? t.substring(0, 5) : '';

const categoryColor = (cat) => {
    if (cat === 'webinar')  return 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]';
    if (cat === 'workshop') return 'bg-[#FFF7ED] text-[#EA580C] border-[#FED7AA]';
    if (cat === 'seminar')  return 'bg-[#F5F3FF] text-[#7C3AED] border-[#DDD6FE]';
    return 'bg-[#F1F5F9] text-[#475569] border-[#E2E8F0]';
};

const starFill = (avg, i) => {
    if (!avg) return '#E2E8F0';
    return i <= Math.round(avg) ? '#F59E0B' : '#E2E8F0';
};

const ratingLabel = (avg) => {
    if (!avg) return { text: 'Belum ada rating', cls: 'text-[#94A3B8]' };
    if (avg >= 4.5) return { text: 'Sangat Baik',  cls: 'text-[#059669]' };
    if (avg >= 3.5) return { text: 'Baik',          cls: 'text-[#2563EB]' };
    if (avg >= 2.5) return { text: 'Cukup',         cls: 'text-[#D97706]' };
    return { text: 'Perlu Peningkatan', cls: 'text-[#DC2626]' };
};

const distPercent = (event, star) => {
    if (!event.rating_count) return 0;
    return Math.round(((event.rating_distribution?.[star] ?? 0) / event.rating_count) * 100);
};
</script>

<template>
    <Head title="Feedback Event — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <!-- ── Detail Modal ──────────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showDetailModal && selectedEvent"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="showDetailModal" class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-br from-[#0F172A] to-[#1E3A5F] text-white p-7 rounded-t-2xl">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span :class="categoryColor(selectedEvent.category)"
                                            class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            {{ selectedEvent.category }}
                                        </span>
                                        <span :class="selectedEvent.type === 'online'
                                            ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]'
                                            : 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]'"
                                            class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider">
                                            {{ selectedEvent.type }}
                                        </span>
                                        <span class="inline-block rounded-full border border-amber-300 bg-amber-400/20 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-amber-200">
                                            Selesai
                                        </span>
                                    </div>
                                    <h2 class="text-xl font-extrabold leading-snug mb-1">{{ selectedEvent.title }}</h2>
                                    <p class="text-white/60 text-sm font-medium">
                                        Oleh {{ selectedEvent.company?.name || 'Perusahaan' }}
                                    </p>
                                </div>
                                <button @click="closeDetail" id="btn-close-feedback-modal"
                                    class="flex-shrink-0 rounded-xl p-2 text-white/60 hover:bg-white/10 hover:text-white transition-colors">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Rating highlight -->
                            <div class="mt-4 flex items-center gap-4">
                                <div v-if="selectedEvent.avg_rating" class="flex items-center gap-2 bg-white/10 rounded-xl px-4 py-2.5">
                                    <svg class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="#F59E0B">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    <span class="text-2xl font-black text-white">{{ Number(selectedEvent.avg_rating).toFixed(1) }}</span>
                                    <span class="text-white/60 text-sm">/ 5.0</span>
                                </div>
                                <div v-if="selectedEvent.rating_count" class="text-white/70 text-sm font-medium">
                                    {{ selectedEvent.rating_count }} ulasan dari peserta
                                </div>
                                <div v-if="!selectedEvent.avg_rating" class="text-white/50 text-sm italic">
                                    Belum ada rating
                                </div>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-7 space-y-6">
                            <!-- Info Detail -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EFF6FF]">
                                        <svg class="h-4 w-4 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-wider">Tanggal</p>
                                        <p class="text-xs font-semibold text-[#0F172A] mt-0.5">{{ formatDate(selectedEvent.date) }}</p>
                                        <p class="text-[10px] text-[#64748B] mt-0.5">{{ formatTime(selectedEvent.start_time) }} – {{ formatTime(selectedEvent.end_time) }} WIB</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5]">
                                        <svg class="h-4 w-4 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-wider">Lokasi</p>
                                        <p class="text-xs font-semibold text-[#0F172A] mt-0.5">{{ selectedEvent.location || '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-2">Tentang Event</p>
                                <p class="text-sm text-[#475569] leading-relaxed line-clamp-4">{{ selectedEvent.description || 'Tidak ada deskripsi.' }}</p>
                            </div>

                            <!-- Rating Distribution -->
                            <div v-if="selectedEvent.avg_rating">
                                <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-3">Distribusi Rating</p>
                                <div class="flex items-start gap-6 p-4 rounded-xl bg-[#FFFBEB] border border-[#FDE68A]">
                                    <!-- Score besar -->
                                    <div class="text-center shrink-0">
                                        <p class="text-4xl font-black text-[#92400E]">{{ Number(selectedEvent.avg_rating).toFixed(1) }}</p>
                                        <div class="flex items-center justify-center gap-0.5 mt-1">
                                            <svg v-for="i in 5" :key="i" class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="starFill(selectedEvent.avg_rating, i)">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-[10px] text-[#92400E] mt-1 font-medium">{{ selectedEvent.rating_count }} ulasan</p>
                                    </div>
                                    <!-- Bar distribusi -->
                                    <div class="flex-1 space-y-1.5">
                                        <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-2 text-xs">
                                            <span class="w-3 text-[#92400E] font-bold">{{ star }}</span>
                                            <svg class="h-3 w-3 text-[#F59E0B]" viewBox="0 0 24 24" fill="#F59E0B">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <div class="flex-1 h-2 rounded-full bg-[#FDE68A] overflow-hidden">
                                                <div class="h-full rounded-full bg-[#F59E0B] transition-all duration-500"
                                                    :style="{ width: `${distPercent(selectedEvent, star)}%` }">
                                                </div>
                                            </div>
                                            <span class="w-7 text-right text-[#92400E] font-semibold">
                                                {{ selectedEvent.rating_distribution?.[star] ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rating label -->
                            <div v-if="selectedEvent.avg_rating" class="flex items-center gap-2">
                                <div class="flex items-center gap-1">
                                    <svg v-for="i in 5" :key="i" class="h-5 w-5" viewBox="0 0 24 24" :fill="starFill(selectedEvent.avg_rating, i)">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-bold" :class="ratingLabel(selectedEvent.avg_rating).cls">
                                    {{ ratingLabel(selectedEvent.avg_rating).text }}
                                </span>
                            </div>

                            <!-- Tombol aksi -->
                            <div class="flex gap-3 pt-2">
                                <button @click="closeDetail"
                                    class="flex-1 rounded-xl border border-[#E2E8F0] py-3 text-sm font-bold text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                                    Tutup
                                </button>
                                <Link :href="route('event.detail', selectedEvent.id)"
                                    class="flex-1 rounded-xl bg-[#2563EB] py-3 text-sm font-bold text-white text-center hover:bg-[#1D4ED8] transition-all hover:shadow-lg hover:shadow-[#2563EB]/20">
                                    Lihat Detail Event
                                </Link>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)] pb-24 relative w-full">
            <!-- Hero Background -->
            <div class="absolute top-0 left-0 right-0 h-[340px] bg-gradient-to-b from-[#0F172A] to-[#F8FAFC] z-0"></div>

            <div class="mx-auto w-full max-w-7xl px-6 pt-12 relative z-10">

                <!-- Back Nav -->
                <Link :href="route('event')"
                    class="inline-flex items-center text-sm font-bold text-white/70 hover:text-white transition-colors mb-5">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali ke Daftar Event
                </Link>

                <!-- Hero Title -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F59E0B]/20 border border-[#F59E0B]/30">
                            <svg class="h-6 w-6 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-extrabold text-white">Feedback <span class="text-[#F59E0B]">Event</span></h1>
                            <p class="text-white/60 text-sm mt-0.5">Event yang telah selesai beserta ulasan & rating peserta</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="rounded-2xl border border-white/10 bg-white/10 backdrop-blur-sm p-5 text-white text-center">
                        <p class="text-3xl font-black">{{ totalEvents }}</p>
                        <p class="text-white/60 text-xs font-semibold mt-1 uppercase tracking-wider">Event Selesai</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 backdrop-blur-sm p-5 text-white text-center">
                        <p class="text-3xl font-black">{{ ratedEvents }}</p>
                        <p class="text-white/60 text-xs font-semibold mt-1 uppercase tracking-wider">Event Dirating</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 backdrop-blur-sm p-5 text-white text-center">
                        <p class="text-3xl font-black flex items-center justify-center gap-1">
                            <span>{{ overallAvg ?? '—' }}</span>
                            <svg v-if="overallAvg" class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="#F59E0B">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </p>
                        <p class="text-white/60 text-xs font-semibold mt-1 uppercase tracking-wider">Rata-rata Rating</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 backdrop-blur-sm p-5 text-white text-center">
                        <p class="text-3xl font-black">{{ totalReviews }}</p>
                        <p class="text-white/60 text-xs font-semibold mt-1 uppercase tracking-wider">Total Ulasan</p>
                    </div>
                </div>

                <!-- Search & Filter Card -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-xl shadow-[#0F172A]/5 mb-8">
                    <div class="flex flex-col md:flex-row gap-3 items-center">
                        <!-- Search -->
                        <div class="flex-1 w-full relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text" v-model="keyword" id="feedback-search"
                                placeholder="Cari event atau perusahaan..."
                                class="w-full rounded-xl border border-[#E2E8F0] pl-10 pr-4 py-2.5 text-sm outline-none placeholder:text-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all" />
                        </div>
                        <!-- Kategori -->
                        <div class="w-full md:w-44 relative">
                            <select v-model="filterKat" id="feedback-filter-kat"
                                class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                <option value="">Semua Kategori</option>
                                <option value="webinar">Webinar</option>
                                <option value="workshop">Workshop</option>
                                <option value="seminar">Seminar</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#94A3B8]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <!-- Tipe -->
                        <div class="w-full md:w-40 relative">
                            <select v-model="filterTipe" id="feedback-filter-tipe"
                                class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                <option value="">Semua Lokasi</option>
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#94A3B8]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <!-- Sort -->
                        <div class="w-full md:w-48 relative">
                            <select v-model="sortBy" id="feedback-sort"
                                class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                <option value="latest">Terbaru</option>
                                <option value="rating_high">Rating Tertinggi</option>
                                <option value="rating_low">Rating Terendah</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#94A3B8]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Header -->
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#0F172A]">
                        Event Selesai
                        <span class="text-[#2563EB]">({{ filtered.length }})</span>
                    </h2>
                </div>

                <!-- Event Grid -->
                <div v-if="filtered.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="event in filtered"
                        :key="event.id"
                        class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#F59E0B]/10 hover:border-[#FDE68A] cursor-pointer"
                        @click="openDetail(event)"
                        :id="`feedback-card-${event.id}`"
                    >
                        <!-- Top Accent Bar -->
                        <div class="h-1 w-full"
                            :style="{ background: event.avg_rating
                                ? `linear-gradient(to right, #F59E0B, #FCD34D)`
                                : 'linear-gradient(to right, #E2E8F0, #CBD5E1)' }">
                        </div>

                        <!-- Card Header -->
                        <div class="relative p-5 pb-3">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F1F5F9] font-bold text-[#475569] text-xs uppercase shadow-sm">
                                            {{ event.company?.name ? event.company.name.charAt(0) : 'C' }}
                                        </div>
                                        <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">{{ event.company?.name || 'Perusahaan' }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span :class="categoryColor(event.category)"
                                            class="text-[10px] font-extrabold uppercase border px-2 py-0.5 rounded-md">
                                            {{ event.category }}
                                        </span>
                                        <span :class="event.type === 'online'
                                            ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]'
                                            : 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]'"
                                            class="text-[10px] font-extrabold uppercase border px-2 py-0.5 rounded-md">
                                            {{ event.type }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Selesai badge -->
                                <span class="inline-block rounded-full border border-[#FDE68A] bg-[#FFFBEB] px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-[#92400E]">
                                    Selesai
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-[#0F172A] group-hover:text-[#D97706] transition-colors leading-snug line-clamp-2 mb-1.5">
                                {{ event.title }}
                            </h3>
                            <p class="text-xs text-[#64748B] line-clamp-2 leading-relaxed">{{ event.description }}</p>
                        </div>

                        <!-- Card Info -->
                        <div class="px-5 py-3 bg-[#F8FAFC] border-t border-b border-[#E2E8F0]">
                            <div class="flex items-center gap-2 text-xs text-[#475569] mb-2">
                                <svg class="h-3.5 w-3.5 text-[#2563EB] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span class="font-medium">{{ formatDate(event.date) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[#475569]">
                                <svg class="h-3.5 w-3.5 text-[#10B981] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span class="font-medium line-clamp-1">{{ event.location }}</span>
                            </div>
                        </div>

                        <!-- Rating Section -->
                        <div class="p-5">
                            <!-- Ada Rating -->
                            <div v-if="event.avg_rating">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <div class="flex items-center gap-0.5">
                                            <svg v-for="i in 5" :key="i" class="h-4 w-4" viewBox="0 0 24 24" :fill="starFill(event.avg_rating, i)">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-base font-black text-[#0F172A] ml-1">
                                            {{ Number(event.avg_rating).toFixed(1) }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-[#64748B] font-medium">{{ event.rating_count }} ulasan</span>
                                </div>
                                <!-- Progress bar -->
                                <div class="h-1.5 w-full rounded-full bg-[#E2E8F0] overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#F59E0B] to-[#FCD34D] transition-all"
                                        :style="{ width: `${(event.avg_rating / 5) * 100}%` }">
                                    </div>
                                </div>
                                <p class="text-xs font-semibold mt-1.5" :class="ratingLabel(event.avg_rating).cls">
                                    {{ ratingLabel(event.avg_rating).text }}
                                </p>
                            </div>

                            <!-- Belum ada rating -->
                            <div v-else class="flex items-center gap-2 py-1">
                                <div class="flex items-center gap-0.5">
                                    <svg v-for="i in 5" :key="i" class="h-4 w-4" viewBox="0 0 24 24" fill="#E2E8F0">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                                <span class="text-xs text-[#94A3B8] font-medium italic">Belum ada rating</span>
                            </div>

                            <!-- CTA -->
                            <button
                                type="button"
                                class="mt-3 w-full flex items-center justify-center gap-2 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] py-2.5 text-xs font-bold text-[#92400E] hover:bg-[#FEF3C7] transition-all group-hover:border-[#F59E0B]"
                            >
                                <svg class="h-3.5 w-3.5 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                Lihat Detail Feedback
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="mt-4 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-white py-20 px-6 text-center shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FFFBEB] mb-4">
                        <svg class="h-8 w-8 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2">Belum Ada Event Selesai</h3>
                    <p class="text-sm text-[#64748B] max-w-md">
                        {{ keyword || filterKat || filterTipe
                            ? 'Tidak ada event selesai yang sesuai dengan filter Anda. Coba ubah kata kunci atau filter.'
                            : 'Belum ada event yang telah selesai dilaksanakan. Cek kembali nanti.' }}
                    </p>
                    <Link :href="route('event')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#1D4ED8] transition-all hover:shadow-lg hover:shadow-[#2563EB]/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Lihat Event Aktif
                    </Link>
                </div>

            </div>
        </div>
    </PortalLayout>
</template>
