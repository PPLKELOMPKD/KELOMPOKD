<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    events:          { type: Array,   default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
    isMahasiswa:     { type: Boolean, default: false },
});

const page = usePage();

// Flash messages
const flash = computed(() => page.props.flash ?? {});

// Filters
const filters = ref({ keyword: '', lokasi: '', tipe: '' });

const filteredEvents = computed(() => {
    let result = [...props.events];
    if (filters.value.keyword.trim()) {
        const q = filters.value.keyword.toLowerCase();
        result = result.filter(e =>
            (e.title || '').toLowerCase().includes(q) ||
            (e.company?.name || '').toLowerCase().includes(q) ||
            (e.description || '').toLowerCase().includes(q)
        );
    }
    if (filters.value.lokasi && filters.value.lokasi !== '') {
        const loc = filters.value.lokasi.toLowerCase();
        result = result.filter(e => (e.type || '').toLowerCase() === loc);
    }
    if (filters.value.tipe && filters.value.tipe !== '') {
        const t = filters.value.tipe.toLowerCase();
        result = result.filter(e => (e.category || '').toLowerCase() === t);
    }
    return result;
});

// Formatting helpers
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
};
const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};
const sisa = (event) => {
    if (event.max_participants === null || event.max_participants === undefined) return null;
    return Math.max(0, event.max_participants - (event.active_count ?? 0));
};

// Registration logic
const loadingId = ref(null);
const toastMsg  = ref('');
const toastType = ref('success');
const showToast = ref(false);

let toastTimer = null;
const triggerToast = (msg, type = 'success') => {
    toastMsg.value  = msg;
    toastType.value = type;
    showToast.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { showToast.value = false; }, 4000);
};

// Watch flash messages from Inertia
import { watch } from 'vue';
watch(() => page.props.flash, (f) => {
    if (f?.success) triggerToast(f.success, 'success');
    if (f?.error)   triggerToast(f.error,   'error');
}, { immediate: true, deep: true });

const daftar = (event) => {
    if (!props.isAuthenticated) {
        router.visit(route('login', { role: 'mahasiswa' }));
        return;
    }
    if (!props.isMahasiswa) return;

    loadingId.value = event.id;
    router.post(route('events.register', event.id), {}, {
        preserveScroll: true,
        onFinish: () => { loadingId.value = null; },
    });
};

const batalkan = (event) => {
    if (!confirm('Apakah Anda yakin ingin membatalkan pendaftaran event ini?')) return;
    loadingId.value = event.id;
    router.delete(route('events.register.cancel', event.id), {
        preserveScroll: true,
        onFinish: () => { loadingId.value = null; },
    });
};

// Button state helper
const buttonState = (event) => {
    // Event sudah selesai (berdasarkan tanggal + end_time)
    if (event.is_completed) return 'completed';
    // Event sudah dimulai tapi belum selesai
    if (event.is_started) return 'started';
    // Tamu
    if (!props.isAuthenticated) return 'guest';
    // Non-mahasiswa
    if (!props.isMahasiswa) return 'non-mahasiswa';
    // Sudah terdaftar (registered/attended)
    const reg = event.user_registration;
    if (reg && (reg.status === 'registered' || reg.status === 'attended')) return 'registered';
    // Kuota penuh
    if (event.is_full) return 'full';
    // Bisa daftar
    return 'available';
};
</script>

<template>
    <Head title="Event — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <!-- Toast Notification -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div v-if="showToast"
                class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border max-w-sm"
                :class="toastType === 'success'
                    ? 'bg-[#ECFDF5] border-[#A7F3D0] text-[#065F46]'
                    : 'bg-[#FEF2F2] border-[#FECACA] text-[#991B1B]'"
            >
                <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full"
                    :class="toastType === 'success' ? 'bg-[#D1FAE5]' : 'bg-[#FEE2E2]'"
                >
                    <svg v-if="toastType === 'success'" class="h-4 w-4 text-[#059669]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <svg v-else class="h-4 w-4 text-[#DC2626]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <p class="text-sm font-semibold leading-snug">{{ toastMsg }}</p>
                <button @click="showToast = false" class="ml-auto opacity-60 hover:opacity-100 transition-opacity">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </Transition>

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)] pb-24 relative w-full">
            <!-- Hero Background -->
            <div class="absolute top-0 left-0 right-0 h-[300px] bg-gradient-to-b from-[#E2E8F0] to-[#F8FAFC] z-0"></div>

            <div class="mx-auto w-full max-w-7xl px-6 pt-12 relative z-10">

                <!-- Header & Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <Link :href="route('peserta')" class="inline-flex items-center text-sm font-bold text-[#2563EB] hover:text-[#1d4ed8] transition-colors mb-3">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Kembali ke Beranda
                        </Link>
                        <h1 class="text-3xl font-extrabold text-[#0F172A]">Eksplorasi <span class="text-[#2563EB]">Event</span></h1>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Button Feedback - dapat dilihat semua user -->
                        <Link :href="route('event.feedback')" id="btn-feedback-event"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#FDE68A] bg-[#FFFBEB] text-[#92400E] px-5 py-2.5 text-sm font-bold hover:bg-[#FEF3C7] hover:border-[#F59E0B] transition-all whitespace-nowrap shadow-sm">
                            <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Feedback
                        </Link>
                        <!-- Button Event Saya - hanya mahasiswa -->
                        <Link v-if="isMahasiswa" :href="route('my-events')" id="btn-event-saya"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#2563EB] text-[#2563EB] px-5 py-2.5 text-sm font-bold hover:bg-[#EFF6FF] transition-all whitespace-nowrap">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path d="M4 7h16v12H4z"/><path d="M4 12h16"/></svg>
                            Event Saya
                        </Link>
                    </div>
                </div>

                <!-- Search Card -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-xl shadow-[#2563EB]/5 mb-10">
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="flex-1 w-full relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" v-model="filters.keyword" id="event-search" placeholder="Cari Event atau Nama Perusahaan..." class="w-full rounded-xl border border-[#E2E8F0] pl-11 pr-4 py-3 text-sm outline-none placeholder:text-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all" />
                        </div>
                        <div class="w-full md:w-48">
                            <div class="relative">
                                <select v-model="filters.lokasi" id="event-filter-lokasi" class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                    <option value="">Semua Lokasi</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-48">
                            <div class="relative">
                                <select v-model="filters.tipe" id="event-filter-tipe" class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                    <option value="">Semua Tipe</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="seminar">Seminar</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Header -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#0F172A]">Event Tersedia <span class="text-[#2563EB]">({{ filteredEvents.length }})</span></h2>
                </div>

                <!-- Event Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="event in filteredEvents" :key="event.id"
                        class="group flex flex-col justify-between rounded-2xl border overflow-hidden transition-all hover:-translate-y-1"
                        :class="event.is_completed
                            ? 'border-[#FDE68A] bg-[#FFFBEB]/40 hover:shadow-xl hover:shadow-[#F59E0B]/10 hover:border-[#F59E0B]'
                            : event.is_started
                                ? 'border-[#A7F3D0] bg-[#ECFDF5]/40 hover:shadow-xl hover:shadow-[#10B981]/10 hover:border-[#10B981]'
                                : 'border-[#E2E8F0] bg-white hover:shadow-xl hover:shadow-[#2563EB]/10 hover:border-[#CBD5E1]'"
                    >
                        <!-- Card Header -->
                        <div class="relative p-6 pb-4">
                            <div class="absolute top-0 left-0 right-0 h-1 transition-opacity"
                                :class="event.is_completed
                                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#FCD34D] opacity-100'
                                    : event.is_started
                                        ? 'bg-gradient-to-r from-[#10B981] to-[#34D399] opacity-100'
                                        : 'bg-gradient-to-r from-[#10B981] to-[#34D399] opacity-0 group-hover:opacity-100'"
                            ></div>

                            <div class="flex items-start justify-between mb-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F1F5F9] font-bold text-[#475569] text-xs uppercase shadow-sm">
                                            {{ event.company?.name ? event.company.name.charAt(0) : 'C' }}
                                        </div>
                                        <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">{{ event.company?.name || 'Perusahaan' }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span v-if="event.category" class="text-[10px] font-extrabold uppercase bg-[#F1F5F9] text-[#475569] px-2 py-0.5 rounded-md border border-[#E2E8F0]">
                                            {{ event.category }}
                                        </span>
                                        <!-- Badge Selesai -->
                                        <span v-if="event.is_completed" class="text-[10px] font-extrabold uppercase bg-[#FFFBEB] text-[#92400E] px-2 py-0.5 rounded-md border border-[#FDE68A]">
                                            Selesai
                                        </span>
                                        <!-- Badge Sedang Berlangsung -->
                                        <span v-else-if="event.is_started" class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase bg-[#ECFDF5] text-[#059669] px-2 py-0.5 rounded-md border border-[#A7F3D0]">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#10B981]"></span>
                                            </span>
                                            Sedang Berlangsung
                                        </span>
                                    </div>
                                </div>
                                <span :class="event.type === 'online' ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]'" class="inline-block rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider">
                                    {{ event.type }}
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors leading-snug line-clamp-2 mb-2">
                                {{ event.title }}
                            </h3>
                            <p class="text-sm text-[#64748B] line-clamp-2 leading-relaxed">{{ event.description }}</p>
                        </div>

                        <!-- Card Info -->
                        <div class="px-6 py-4 bg-[#F8FAFC] border-t border-b border-[#E2E8F0]">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <div class="font-medium">
                                        {{ formatDate(event.date) }}
                                        <div class="text-xs text-[#94A3B8] mt-0.5">{{ formatTime(event.start_time) }} - {{ formatTime(event.end_time) }} WIB</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <span class="font-medium line-clamp-1">{{ event.location }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    </div>
                                    <div class="font-medium">
                                        <span v-if="sisa(event) !== null">
                                            Sisa Kuota:
                                            <span :class="sisa(event) <= 5 ? 'text-[#DC2626] font-bold' : 'text-[#0F172A]'">
                                                {{ sisa(event) }}
                                            </span>
                                            / {{ event.max_participants }}
                                        </span>
                                        <span v-else>Kuota: Tidak Terbatas</span>
                                    </div>
                                </div>
                                <!-- Rating jika ada -->
                                <div v-if="event.avg_rating" class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </div>
                                    <div class="font-medium text-sm text-[#475569]">
                                        <span class="font-bold text-[#0F172A]">{{ Number(event.avg_rating).toFixed(1) }}</span>
                                        <span class="text-xs text-[#94A3B8] ml-1">({{ event.rating_count }} ulasan)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-6 flex flex-col gap-3">
                            <!-- Lihat Detail -->
                            <Link :href="route('event.detail', event.id)" class="w-full flex items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white py-2.5 text-center text-sm font-semibold text-[#475569] transition-all hover:bg-[#F8FAFC] hover:border-[#CBD5E1]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                Lihat Detail
                            </Link>

                            <!-- Event sudah selesai → arahkan ke Feedback -->
                            <Link v-if="buttonState(event) === 'completed'"
                                :href="route('event.feedback')"
                                :id="`btn-event-selesai-${event.id}`"
                                class="w-full flex items-center justify-center gap-2 rounded-xl border border-[#FDE68A] bg-[#FFFBEB] py-3 text-center text-sm font-bold text-[#92400E] transition-all hover:bg-[#FEF3C7] hover:border-[#F59E0B]"
                            >
                                <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Event Selesai — Lihat Feedback
                            </Link>

                            <!-- Event sedang berlangsung → tidak bisa daftar -->
                            <button v-else-if="buttonState(event) === 'started'"
                                type="button" disabled
                                :id="`btn-sedang-berlangsung-${event.id}`"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#ECFDF5] py-3 text-sm font-bold text-[#059669] cursor-not-allowed border border-[#A7F3D0]"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#10B981]"></span>
                                </span>
                                Sedang Berlangsung
                            </button>

                            <!-- TC-01: Tamu → Login untuk Mendaftar -->
                            <Link v-else-if="buttonState(event) === 'guest'"
                                :href="route('login', { role: 'mahasiswa' })"
                                id="btn-login-daftar"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] py-3 text-center text-sm font-bold text-white transition-all hover:bg-[#1D4ED8] hover:shadow-lg hover:shadow-[#2563EB]/20"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                Login untuk Mendaftar
                            </Link>

                            <!-- Non-mahasiswa -->
                            <button v-else-if="buttonState(event) === 'non-mahasiswa'"
                                type="button" disabled
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#F1F5F9] py-3 text-sm font-bold text-[#94A3B8] cursor-not-allowed"
                            >
                                Khusus Mahasiswa
                            </button>

                            <!-- Sudah terdaftar -->
                            <button v-else-if="buttonState(event) === 'registered'"
                                type="button" disabled
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#ECFDF5] py-3 text-sm font-bold text-[#059669] cursor-not-allowed border border-[#A7F3D0]"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                Sudah Terdaftar
                            </button>

                            <!-- Kuota penuh -->
                            <button v-else-if="buttonState(event) === 'full'"
                                type="button" disabled
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#FEF2F2] py-3 text-sm font-bold text-[#DC2626] cursor-not-allowed border border-[#FECACA]"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Kuota Penuh
                            </button>

                            <!-- TC-02 / TC-03: Daftar Sekarang -->
                            <button v-else
                                @click="daftar(event)"
                                :disabled="loadingId === event.id"
                                :id="`btn-daftar-${event.id}`"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-3 text-center text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20 disabled:opacity-60 disabled:cursor-wait"
                            >
                                <svg v-if="loadingId !== event.id" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                {{ loadingId === event.id ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredEvents.length === 0" class="mt-12 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-white py-16 px-6 text-center shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] mb-4">
                        <svg class="h-8 w-8 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2">Event Tidak Ditemukan</h3>
                    <p class="text-sm text-[#64748B] max-w-md">Tidak ada event yang sesuai dengan pencarian Anda. Coba ubah kata kunci atau filter untuk menemukan event lainnya.</p>
                </div>

            </div>
        </div>
    </PortalLayout>
</template>
