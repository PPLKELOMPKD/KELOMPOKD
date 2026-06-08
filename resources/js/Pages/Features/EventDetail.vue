<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    event:           { type: Object,  required: true },
    isAuthenticated: { type: Boolean, default: false },
    isMahasiswa:     { type: Boolean, default: false },
});

const page = usePage();

// Toast
const toastMsg  = ref('');
const toastType = ref('success');
const showToast = ref(false);
let toastTimer  = null;
const triggerToast = (msg, type = 'success') => {
    toastMsg.value  = msg;
    toastType.value = type;
    showToast.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { showToast.value = false; }, 4500);
};
watch(() => page.props.flash, (f) => {
    if (f?.success) triggerToast(f.success, 'success');
    if (f?.error)   triggerToast(f.error,   'error');
}, { immediate: true, deep: true });

// Cancel dialog
const showCancelDialog = ref(false);

// Loading
const isLoading = ref(false);

// Helpers
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
const formatCommentDate = (dt) => {
    if (!dt) return '';
    return new Date(dt).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
    });
};
const sisa = computed(() => {
    const e = props.event;
    if (e.max_participants === null || e.max_participants === undefined) return null;
    return Math.max(0, e.max_participants - (e.active_count ?? 0));
});

// Button state
const buttonState = computed(() => {
    if (!props.isAuthenticated) return 'guest';
    if (!props.isMahasiswa) return 'non-mahasiswa';
    const reg = props.event.user_registration;
    if (reg && (reg.status === 'registered' || reg.status === 'attended')) return 'registered';
    if (props.event.is_full) return 'full';
    return 'available';
});

const daftar = () => {
    if (!props.isAuthenticated) {
        router.visit(route('login', { role: 'mahasiswa' }));
        return;
    }
    isLoading.value = true;
    router.post(route('events.register', props.event.id), {}, {
        preserveScroll: true,
        onFinish: () => { isLoading.value = false; },
    });
};

const batalkan = () => {
    showCancelDialog.value = false;
    isLoading.value = true;
    router.delete(route('events.register.cancel', props.event.id), {
        preserveScroll: true,
        onFinish: () => { isLoading.value = false; },
    });
};

// Category badge color
const categoryColor = (cat) => {
    if (cat === 'webinar')  return 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]';
    if (cat === 'workshop') return 'bg-[#FFF7ED] text-[#EA580C] border-[#FED7AA]';
    if (cat === 'seminar')  return 'bg-[#F5F3FF] text-[#7C3AED] border-[#DDD6FE]';
    return 'bg-[#F1F5F9] text-[#475569] border-[#E2E8F0]';
};

// Rating stars helpers
const starFill = (avg, index) => {
    if (!avg) return '#E2E8F0';
    return index <= Math.round(avg) ? '#F59E0B' : '#E2E8F0';
};

// Ulasan pagination
const showAllReviews = ref(false);
const visibleRatings = computed(() => {
    const all = props.event.ratings || [];
    return showAllReviews.value ? all : all.slice(0, 3);
});
</script>

<template>
    <Head :title="`${event.title} — SIKARA`" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <!-- Toast -->
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
                :class="toastType === 'success' ? 'bg-[#ECFDF5] border-[#A7F3D0] text-[#065F46]' : 'bg-[#FEF2F2] border-[#FECACA] text-[#991B1B]'"
            >
                <div class="flex-shrink-0 flex h-8 w-8 items-center justify-center rounded-full" :class="toastType === 'success' ? 'bg-[#D1FAE5]' : 'bg-[#FEE2E2]'">
                    <svg v-if="toastType === 'success'" class="h-4 w-4 text-[#059669]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                    <svg v-else class="h-4 w-4 text-[#DC2626]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <p class="text-sm font-semibold leading-snug">{{ toastMsg }}</p>
                <button @click="showToast = false" class="ml-auto opacity-60 hover:opacity-100 transition-opacity">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </Transition>

        <!-- A4: Confirm Cancel Dialog -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div v-if="showCancelDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showCancelDialog = false">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FEF2F2] mb-5">
                        <svg class="h-7 w-7 text-[#DC2626]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#0F172A] mb-2">Batalkan Pendaftaran?</h3>
                    <p class="text-sm text-[#64748B] leading-relaxed mb-6">
                        Anda akan membatalkan pendaftaran dari event <span class="font-semibold text-[#0F172A]">{{ event.title }}</span>. Tindakan ini tidak dapat dibatalkan secara langsung.
                    </p>
                    <div class="flex gap-3">
                        <button @click="showCancelDialog = false" class="flex-1 rounded-xl border border-[#E2E8F0] py-3 text-sm font-bold text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                            Kembali
                        </button>
                        <button @click="batalkan" id="btn-confirm-cancel" class="flex-1 rounded-xl bg-[#DC2626] py-3 text-sm font-bold text-white hover:bg-[#B91C1C] transition-colors">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)] pb-24">
            <!-- Hero Banner -->
            <div class="bg-gradient-to-br from-[#0F172A] to-[#1E3A5F] text-white py-14">
                <div class="mx-auto max-w-5xl px-6">
                    <Link :href="route('event')" class="inline-flex items-center text-sm font-semibold text-white/70 hover:text-white transition-colors mb-6">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Kembali ke Daftar Event
                    </Link>

                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span :class="categoryColor(event.category)" class="inline-block rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider">
                            {{ event.category }}
                        </span>
                        <span :class="event.type === 'online' ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]'" class="inline-block rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider">
                            {{ event.type }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-3">{{ event.title }}</h1>

                    <div class="flex flex-wrap items-center gap-4">
                        <p class="text-white/70 text-sm font-semibold uppercase tracking-wider">
                            Oleh {{ event.company?.name || 'Perusahaan' }}
                        </p>
                        <!-- TC-11: Avg Rating di hero -->
                        <div v-if="event.avg_rating" class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-full">
                            <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="#F59E0B"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="text-white text-sm font-bold">{{ Number(event.avg_rating).toFixed(1) }}</span>
                            <span class="text-white/60 text-xs">({{ event.rating_count }} ulasan)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-5xl px-6 py-10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Main Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Description -->
                        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
                            <h2 class="text-lg font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                                <svg class="h-5 w-5 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                Tentang Event
                            </h2>
                            <p class="text-sm text-[#475569] leading-relaxed whitespace-pre-line">{{ event.description || 'Tidak ada deskripsi tersedia.' }}</p>
                        </div>

                        <!-- Details Grid -->
                        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
                            <h2 class="text-lg font-bold text-[#0F172A] mb-5 flex items-center gap-2">
                                <svg class="h-5 w-5 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Informasi Detail
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Date -->
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EFF6FF]">
                                        <svg class="h-5 w-5 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-1">Tanggal</p>
                                        <p class="text-sm font-semibold text-[#0F172A]">{{ formatDate(event.date) }}</p>
                                        <p class="text-xs text-[#64748B] mt-0.5">{{ formatTime(event.start_time) }} – {{ formatTime(event.end_time) }} WIB</p>
                                    </div>
                                </div>
                                <!-- Location -->
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#ECFDF5]">
                                        <svg class="h-5 w-5 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-1">Lokasi</p>
                                        <p class="text-sm font-semibold text-[#0F172A]">{{ event.location || '-' }}</p>
                                    </div>
                                </div>
                                <!-- Organizer -->
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FFF7ED]">
                                        <svg class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-1">Penyelenggara</p>
                                        <p class="text-sm font-semibold text-[#0F172A]">{{ event.company?.name || '-' }}</p>
                                    </div>
                                </div>
                                <!-- Quota -->
                                <div class="flex items-start gap-4 p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F5F3FF]">
                                        <svg class="h-5 w-5 text-[#7C3AED]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-1">Kuota Peserta</p>
                                        <p v-if="sisa !== null" class="text-sm font-semibold" :class="sisa <= 5 ? 'text-[#DC2626]' : 'text-[#0F172A]'">
                                            {{ event.active_count }} / {{ event.max_participants }} terdaftar
                                        </p>
                                        <p v-else class="text-sm font-semibold text-[#0F172A]">Tidak Terbatas</p>
                                        <div v-if="event.max_participants" class="mt-2 h-1.5 w-full rounded-full bg-[#E2E8F0] overflow-hidden">
                                            <div class="h-full rounded-full transition-all" :class="event.is_full ? 'bg-[#DC2626]' : 'bg-[#10B981]'" :style="{ width: `${Math.min(100, (event.active_count / event.max_participants) * 100)}%` }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── TC-11: Ulasan & Rating Section ──────────────────────── -->
                        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 shadow-sm">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-bold text-[#0F172A] flex items-center gap-2">
                                    <svg class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Ulasan & Rating
                                </h2>
                                <span v-if="event.rating_count" class="rounded-full bg-[#FFFBEB] border border-[#FDE68A] px-3 py-1 text-xs font-bold text-[#92400E]">
                                    {{ event.rating_count }} ulasan
                                </span>
                            </div>

                            <!-- Avg Rating Summary -->
                            <div v-if="event.avg_rating" class="flex items-center gap-6 p-5 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] mb-6">
                                <div class="text-center">
                                    <p class="text-4xl font-black text-[#92400E]">{{ Number(event.avg_rating).toFixed(1) }}</p>
                                    <div class="flex items-center justify-center gap-0.5 mt-1">
                                        <svg v-for="i in 5" :key="i" class="h-4 w-4" viewBox="0 0 24 24" :fill="starFill(event.avg_rating, i)"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </div>
                                    <p class="text-xs text-[#92400E] mt-1 font-medium">dari {{ event.rating_count }} ulasan</p>
                                </div>
                                <div class="flex-1 space-y-1.5">
                                    <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-2 text-xs">
                                        <span class="w-3 text-[#92400E] font-bold">{{ star }}</span>
                                        <svg class="h-3 w-3 text-[#F59E0B]" viewBox="0 0 24 24" fill="#F59E0B"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <div class="flex-1 h-1.5 rounded-full bg-[#FDE68A] overflow-hidden">
                                            <div
                                                class="h-full rounded-full bg-[#F59E0B] transition-all"
                                                :style="{ width: `${event.rating_count ? ((event.ratings || []).filter(r => r.rating === star).length / event.rating_count) * 100 : 0}%` }"
                                            ></div>
                                        </div>
                                        <span class="w-4 text-right text-[#92400E]">{{ (event.ratings || []).filter(r => r.rating === star).length }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- No Ratings Yet -->
                            <div v-else class="flex flex-col items-center py-8 text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FFFBEB] mb-3">
                                    <svg class="h-7 w-7 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-[#475569]">Belum ada ulasan</p>
                                <p class="text-xs text-[#94A3B8] mt-1">Event ini belum mendapatkan ulasan dari peserta.</p>
                            </div>

                            <!-- Review Cards -->
                            <div v-if="(event.ratings || []).length > 0" class="space-y-4">
                                <div
                                    v-for="review in visibleRatings"
                                    :key="review.id"
                                    class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-5"
                                >
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#2563EB] to-[#7C3AED] text-white text-sm font-bold flex-shrink-0">
                                                {{ review.user_name ? review.user_name.charAt(0).toUpperCase() : 'M' }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-[#0F172A]">{{ review.user_name }}</p>
                                                <p class="text-xs text-[#94A3B8]">{{ formatCommentDate(review.created_at) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5">
                                            <svg v-for="i in 5" :key="i" class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="i <= review.rating ? '#F59E0B' : '#E2E8F0'"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <span class="ml-1 text-xs font-bold text-[#64748B]">{{ review.rating }}/5</span>
                                        </div>
                                    </div>
                                    <p v-if="review.comment" class="text-sm text-[#475569] leading-relaxed">{{ review.comment }}</p>
                                    <p v-else class="text-sm text-[#94A3B8] italic">Tidak ada komentar.</p>
                                </div>

                                <!-- Show More / Show Less -->
                                <div v-if="(event.ratings || []).length > 3" class="text-center pt-2">
                                    <button
                                        @click="showAllReviews = !showAllReviews"
                                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8] transition-colors"
                                    >
                                        {{ showAllReviews ? 'Tampilkan Lebih Sedikit' : `Lihat Semua ${event.ratings.length} Ulasan` }}
                                        <svg class="h-4 w-4 transition-transform" :class="showAllReviews ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- ── End Ulasan Section ──────────────────────────────────── -->
                    </div>

                    <!-- Sidebar Action -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-24 rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-lg">
                            <h3 class="text-base font-bold text-[#0F172A] mb-5">Daftar ke Event Ini</h3>

                            <!-- Quota bar -->
                            <div v-if="event.max_participants" class="mb-5">
                                <div class="flex justify-between text-xs font-semibold mb-1.5">
                                    <span class="text-[#64748B]">Terisi</span>
                                    <span :class="event.is_full ? 'text-[#DC2626]' : 'text-[#10B981]'">
                                        {{ event.active_count }} / {{ event.max_participants }}
                                    </span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-[#E2E8F0] overflow-hidden">
                                    <div class="h-full rounded-full transition-all" :class="event.is_full ? 'bg-[#DC2626]' : 'bg-[#10B981]'" :style="{ width: `${Math.min(100, (event.active_count / event.max_participants) * 100)}%` }"></div>
                                </div>
                                <p v-if="!event.is_full" class="text-xs text-[#64748B] mt-1.5">
                                    Sisa <span class="font-bold text-[#10B981]">{{ sisa }}</span> kuota
                                </p>
                                <p v-else class="text-xs text-[#DC2626] font-bold mt-1.5">Kuota penuh</p>
                            </div>

                            <!-- TC-01: Tamu -->
                            <Link v-if="buttonState === 'guest'"
                                :href="route('login', { role: 'mahasiswa' })"
                                id="btn-detail-login"
                                class="block w-full text-center rounded-xl bg-[#2563EB] py-3.5 text-sm font-bold text-white transition-all hover:bg-[#1D4ED8] hover:shadow-lg hover:shadow-[#2563EB]/20 mb-3"
                            >
                                Login untuk Mendaftar
                            </Link>

                            <!-- Non-mahasiswa -->
                            <button v-else-if="buttonState === 'non-mahasiswa'" type="button" disabled
                                class="w-full rounded-xl bg-[#F1F5F9] py-3.5 text-sm font-bold text-[#94A3B8] cursor-not-allowed mb-3"
                            >
                                Khusus Mahasiswa
                            </button>

                            <!-- Sudah Terdaftar + tombol Batalkan -->
                            <template v-else-if="buttonState === 'registered'">
                                <button type="button" disabled
                                    id="btn-sudah-terdaftar"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#ECFDF5] py-3.5 text-sm font-bold text-[#059669] cursor-not-allowed border border-[#A7F3D0] mb-3"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    Sudah Terdaftar
                                </button>
                                <button
                                    @click="showCancelDialog = true"
                                    id="btn-batalkan-pendaftaran"
                                    class="w-full rounded-xl border border-[#FECACA] bg-white py-2.5 text-sm font-semibold text-[#DC2626] hover:bg-[#FEF2F2] transition-colors"
                                >
                                    Batalkan Pendaftaran
                                </button>
                            </template>

                            <!-- Kuota Penuh -->
                            <button v-else-if="buttonState === 'full'" type="button" disabled
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#FEF2F2] py-3.5 text-sm font-bold text-[#DC2626] cursor-not-allowed border border-[#FECACA] mb-3"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Kuota Penuh
                            </button>

                            <!-- TC-03: Daftar Sekarang -->
                            <button v-else
                                @click="daftar"
                                :disabled="isLoading"
                                id="btn-daftar-sekarang"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-3.5 text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20 disabled:opacity-60 disabled:cursor-wait mb-3"
                            >
                                <svg v-if="!isLoading" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                {{ isLoading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                            </button>

                            <!-- Link Event Saya -->
                            <Link v-if="isMahasiswa" :href="route('my-events')" class="block w-full text-center rounded-xl border border-[#E2E8F0] py-2.5 text-sm font-semibold text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                                Lihat Event Saya
                            </Link>

                            <!-- Rating info di sidebar -->
                            <div v-if="event.avg_rating" class="mt-5 pt-5 border-t border-[#E2E8F0]">
                                <p class="text-xs font-bold text-[#94A3B8] uppercase tracking-wider mb-2">Rating Event</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-black text-[#0F172A]">{{ Number(event.avg_rating).toFixed(1) }}</span>
                                    <div>
                                        <div class="flex items-center gap-0.5">
                                            <svg v-for="i in 5" :key="i" class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="starFill(event.avg_rating, i)"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </div>
                                        <p class="text-xs text-[#94A3B8] mt-0.5">{{ event.rating_count }} ulasan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
