<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    registrations: { type: Array, default: () => [] },
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
    toastTimer = setTimeout(() => { showToast.value = false; }, 4000);
};
watch(() => page.props.flash, (f) => {
    if (f?.success) triggerToast(f.success, 'success');
    if (f?.error)   triggerToast(f.error,   'error');
}, { immediate: true, deep: true });

// Filter
const activeFilter = ref('all');
const filters = [
    { label: 'Semua',     value: 'all' },
    { label: 'Terdaftar', value: 'registered' },
    { label: 'Dibatalkan',value: 'cancelled' },
];

const filteredRegistrations = computed(() => {
    if (activeFilter.value === 'all') return props.registrations;
    return props.registrations.filter(r => r.status === activeFilter.value);
});

// Cancel dialog
const cancelTarget = ref(null);
const showCancelDialog = ref(false);
const isLoading = ref(false);

const openCancel = (reg) => {
    cancelTarget.value = reg;
    showCancelDialog.value = true;
};
const batalkan = () => {
    if (!cancelTarget.value) return;
    showCancelDialog.value = false;
    isLoading.value = true;
    router.delete(route('events.register.cancel', cancelTarget.value.event.id), {
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
            cancelTarget.value = null;
        },
    });
};

// ── Rating Modal ─────────────────────────────────────────────────────────────
const showRatingModal  = ref(false);
const ratingTarget     = ref(null);   // registration object
const ratingValue      = ref(0);      // 0 = belum dipilih
const ratingHover      = ref(0);
const ratingComment    = ref('');
const ratingIsEdit     = ref(false);
const ratingSubmitting = ref(false);
const ratingErrors     = ref({});

const openRatingModal = (reg, editMode = false) => {
    ratingTarget.value  = reg;
    ratingIsEdit.value  = editMode;
    ratingErrors.value  = {};

    if (editMode && reg.event?.user_rating) {
        ratingValue.value   = reg.event.user_rating.rating;
        ratingComment.value = reg.event.user_rating.comment || '';
    } else {
        ratingValue.value   = 0;
        ratingComment.value = '';
    }
    showRatingModal.value = true;
};

const closeRatingModal = () => {
    showRatingModal.value = false;
    ratingTarget.value = null;
};

const submitRating = () => {
    ratingErrors.value = {};

    // Validasi client-side
    if (!ratingValue.value || ratingValue.value < 1 || ratingValue.value > 5) {
        ratingErrors.value.rating = 'Rating bintang wajib dipilih (1–5).';
        return;
    }
    if (ratingComment.value && ratingComment.value.length > 500) {
        ratingErrors.value.comment = 'Komentar tidak boleh melebihi 500 karakter.';
        return;
    }

    ratingSubmitting.value = true;
    const eventId = ratingTarget.value.event.id;
    const payload = { rating: ratingValue.value, comment: ratingComment.value };

    const routeName = ratingIsEdit.value ? 'events.ratings.update' : 'events.ratings.store';
    const method    = ratingIsEdit.value ? 'put' : 'post';

    router[method](route(routeName, eventId), payload, {
        preserveScroll: true,
        onSuccess: () => {
            closeRatingModal();
        },
        onError: (errors) => {
            ratingErrors.value = errors;
        },
        onFinish: () => {
            ratingSubmitting.value = false;
        },
    });
};

// Helpers
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
};
const formatTime = (t) => t ? t.substring(0, 5) : '';
const formatRegisteredAt = (dt) => {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};

const statusConfig = (status) => {
    if (status === 'registered') return { label: 'Terdaftar',   cls: 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' };
    if (status === 'attended')   return { label: 'Hadir',       cls: 'bg-[#ECFDF5] text-[#059669] border-[#A7F3D0]' };
    if (status === 'cancelled')  return { label: 'Dibatalkan',  cls: 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]' };
    return { label: status, cls: 'bg-[#F1F5F9] text-[#475569] border-[#E2E8F0]' };
};

const categoryColor = (cat) => {
    if (cat === 'webinar')  return 'bg-[#EFF6FF] text-[#2563EB]';
    if (cat === 'workshop') return 'bg-[#FFF7ED] text-[#EA580C]';
    if (cat === 'seminar')  return 'bg-[#F5F3FF] text-[#7C3AED]';
    return 'bg-[#F1F5F9] text-[#475569]';
};

// Apakah event sudah selesai (bisa diberi rating)
const isEventCompleted = (event) => {
    if (!event) return false;
    if (event.is_completed) return true;
    if (event.status === 'completed') return true;
    return new Date(event.date) < new Date(new Date().toDateString());
};

// Tampilkan bintang (readonly)
const starDisplay = (avg) => {
    if (!avg) return [];
    return [1, 2, 3, 4, 5].map(i => {
        if (i <= Math.floor(avg)) return 'full';
        if (i - avg < 1 && i - avg > 0) return 'half';
        return 'empty';
    });
};
</script>

<template>
    <Head title="Event Saya — SIKARA" />

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
                        Anda akan membatalkan pendaftaran dari event
                        <span class="font-semibold text-[#0F172A]">{{ cancelTarget?.event?.title }}</span>.
                    </p>
                    <div class="flex gap-3">
                        <button @click="showCancelDialog = false" class="flex-1 rounded-xl border border-[#E2E8F0] py-3 text-sm font-bold text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                            Kembali
                        </button>
                        <button @click="batalkan" id="btn-confirm-cancel-myevent" class="flex-1 rounded-xl bg-[#DC2626] py-3 text-sm font-bold text-white hover:bg-[#B91C1C] transition-colors">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── Rating Modal ──────────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showRatingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeRatingModal">
                <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FFF7ED]">
                                    <svg class="h-5 w-5 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <h3 class="text-xl font-bold text-[#0F172A]">
                                    {{ ratingIsEdit ? 'Edit Rating' : 'Beri Rating Event' }}
                                </h3>
                            </div>
                            <p class="text-sm text-[#64748B] ml-11 line-clamp-1">{{ ratingTarget?.event?.title }}</p>
                        </div>
                        <button @click="closeRatingModal" class="ml-4 flex-shrink-0 rounded-xl p-2 text-[#94A3B8] hover:bg-[#F1F5F9] hover:text-[#475569] transition-colors">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Star Rating Picker -->
                    <div class="mb-6">
                        <p class="text-sm font-bold text-[#0F172A] mb-3">
                            Rating Bintang <span class="text-[#DC2626]">*</span>
                        </p>
                        <div class="flex items-center gap-2" id="star-rating-picker">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                :id="`star-btn-${star}`"
                                @mouseenter="ratingHover = star"
                                @mouseleave="ratingHover = 0"
                                @click="ratingValue = star"
                                class="transition-transform hover:scale-110 focus:outline-none"
                                :aria-label="`${star} bintang`"
                            >
                                <svg
                                    class="h-10 w-10 transition-colors duration-100"
                                    viewBox="0 0 24 24"
                                    :fill="(ratingHover || ratingValue) >= star ? '#F59E0B' : 'none'"
                                    :stroke="(ratingHover || ratingValue) >= star ? '#F59E0B' : '#CBD5E1'"
                                    stroke-width="1.5"
                                >
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            <span class="ml-2 text-sm font-semibold text-[#64748B]">
                                <template v-if="ratingHover">{{ ratingHover }} bintang</template>
                                <template v-else-if="ratingValue">{{ ratingValue }} bintang dipilih</template>
                                <template v-else>Pilih rating</template>
                            </span>
                        </div>
                        <p v-if="ratingErrors.rating" class="mt-2 text-xs font-semibold text-[#DC2626]">{{ ratingErrors.rating }}</p>
                    </div>

                    <!-- Comment Textarea -->
                    <div class="mb-6">
                        <label for="rating-comment" class="block text-sm font-bold text-[#0F172A] mb-2">
                            Komentar <span class="text-[#94A3B8] font-normal">(opsional)</span>
                        </label>
                        <textarea
                            id="rating-comment"
                            v-model="ratingComment"
                            rows="4"
                            maxlength="500"
                            placeholder="Bagikan pengalaman Anda mengikuti event ini..."
                            class="w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none transition-colors resize-none"
                            :class="ratingErrors.comment ? 'border-[#DC2626] focus:border-[#DC2626] focus:ring-[#DC2626]/20' : ''"
                        ></textarea>
                        <div class="flex items-center justify-between mt-1.5">
                            <p v-if="ratingErrors.comment" class="text-xs font-semibold text-[#DC2626]">{{ ratingErrors.comment }}</p>
                            <p v-else class="text-xs text-[#94A3B8]">Maks 500 karakter</p>
                            <span class="text-xs font-medium" :class="ratingComment.length > 480 ? 'text-[#DC2626]' : 'text-[#94A3B8]'">
                                {{ ratingComment.length }}/500
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="closeRatingModal"
                            class="flex-1 rounded-xl border border-[#E2E8F0] py-3 text-sm font-bold text-[#475569] hover:bg-[#F8FAFC] transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            id="btn-kirim-rating"
                            @click="submitRating"
                            :disabled="ratingSubmitting"
                            class="flex-1 rounded-xl bg-[#F59E0B] py-3 text-sm font-bold text-white hover:bg-[#D97706] transition-all hover:shadow-lg hover:shadow-[#F59E0B]/30 disabled:opacity-60 disabled:cursor-wait flex items-center justify-center gap-2"
                        >
                            <svg v-if="ratingSubmitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            {{ ratingSubmitting ? 'Mengirim...' : (ratingIsEdit ? 'Perbarui Rating' : 'Kirim Rating') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)] pb-24">
            <!-- Hero -->
            <div class="bg-gradient-to-br from-[#0F172A] to-[#1E3A5F] text-white py-12">
                <div class="mx-auto max-w-7xl px-6">
                    <Link :href="route('event')" class="inline-flex items-center text-sm font-semibold text-white/70 hover:text-white transition-colors mb-5">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Kembali ke Daftar Event
                    </Link>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-extrabold mb-1">Event <span class="text-[#60A5FA]">Saya</span></h1>
                            <p class="text-white/60 text-sm">Kelola semua event yang telah Anda daftarkan</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-white/10 px-4 py-2 text-center">
                                <p class="text-2xl font-black">{{ registrations.length }}</p>
                                <p class="text-xs text-white/60 font-medium">Total Event</p>
                            </div>
                            <div class="rounded-xl bg-white/10 px-4 py-2 text-center">
                                <p class="text-2xl font-black">{{ registrations.filter(r => r.status === 'registered').length }}</p>
                                <p class="text-xs text-white/60 font-medium">Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-10">

                <!-- Filter Tabs -->
                <div class="flex flex-wrap gap-2 mb-8">
                    <button
                        v-for="f in filters" :key="f.value"
                        @click="activeFilter = f.value"
                        :id="`filter-myevent-${f.value}`"
                        class="rounded-full border px-5 py-2 text-xs font-bold tracking-wide transition-all"
                        :class="activeFilter === f.value
                            ? 'border-transparent bg-[#2563EB] text-white shadow-lg shadow-[#2563EB]/20'
                            : 'border-[#E2E8F0] bg-white text-[#64748B] hover:bg-[#F8FAFC]'"
                    >
                        {{ f.label }}
                        <span class="ml-1.5 rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] font-extrabold">
                            {{ f.value === 'all' ? registrations.length : registrations.filter(r => r.status === f.value).length }}
                        </span>
                    </button>
                </div>

                <!-- Registration Cards -->
                <div v-if="filteredRegistrations.length > 0" class="space-y-5">
                    <div
                        v-for="reg in filteredRegistrations"
                        :key="reg.id"
                        class="group rounded-2xl border border-[#E2E8F0] bg-white overflow-hidden shadow-sm hover:shadow-md transition-all hover:border-[#CBD5E1]"
                    >
                        <div class="flex flex-col md:flex-row">
                            <!-- Left Accent -->
                            <div class="w-full md:w-1.5 flex-shrink-0 md:rounded-l-2xl"
                                :class="reg.status === 'registered' ? 'bg-[#2563EB] h-1.5 md:h-auto' : reg.status === 'attended' ? 'bg-[#10B981] h-1.5 md:h-auto' : 'bg-[#94A3B8] h-1.5 md:h-auto'"
                            ></div>

                            <!-- Content -->
                            <div class="flex-1 p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <!-- Event Info -->
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <!-- Status Badge -->
                                            <span :class="statusConfig(reg.status).cls" class="inline-block rounded-full border px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider">
                                                {{ statusConfig(reg.status).label }}
                                            </span>
                                            <!-- Selesai Badge -->
                                            <span v-if="isEventCompleted(reg.event)" class="inline-block rounded-full border border-[#FDE68A] bg-[#FFFBEB] px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#92400E]">
                                                Selesai
                                            </span>
                                            <!-- Category Badge -->
                                            <span v-if="reg.event?.category" :class="categoryColor(reg.event.category)" class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase">
                                                {{ reg.event.category }}
                                            </span>
                                            <!-- Type Badge -->
                                            <span v-if="reg.event?.type" :class="reg.event.type === 'online' ? 'bg-[#EFF6FF] text-[#2563EB]' : 'bg-[#F0FDF4] text-[#16A34A]'" class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase">
                                                {{ reg.event.type }}
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-bold text-[#0F172A] mb-1 group-hover:text-[#2563EB] transition-colors">
                                            {{ reg.event?.title || '—' }}
                                        </h3>

                                        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mb-3">
                                            {{ reg.event?.company?.name || 'Perusahaan' }}
                                        </p>

                                        <!-- Event Meta -->
                                        <div class="flex flex-wrap gap-4 text-sm text-[#475569]">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-[#2563EB] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                <span class="font-medium">{{ formatDate(reg.event?.date) }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-[#10B981] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                <span class="font-medium">{{ formatTime(reg.event?.start_time) }} – {{ formatTime(reg.event?.end_time) }} WIB</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-[#F59E0B] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                <span class="font-medium line-clamp-1">{{ reg.event?.location || '-' }}</span>
                                            </div>
                                        </div>

                                        <!-- Rating Avg display -->
                                        <div v-if="reg.event?.avg_rating" class="flex items-center gap-1.5 mt-2">
                                            <div class="flex items-center gap-0.5">
                                                <svg v-for="i in 5" :key="i" class="h-3.5 w-3.5"
                                                    :fill="i <= Math.round(reg.event.avg_rating) ? '#F59E0B' : '#E2E8F0'"
                                                    viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            </div>
                                            <span class="text-xs font-semibold text-[#64748B]">
                                                {{ Number(reg.event.avg_rating).toFixed(1) }}
                                                <span class="font-normal">({{ reg.event.rating_count }} ulasan)</span>
                                            </span>
                                        </div>

                                        <p class="text-xs text-[#94A3B8] mt-2 font-medium">
                                            Didaftarkan: {{ formatRegisteredAt(reg.registered_at) }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex sm:flex-col gap-2 sm:min-w-[150px]">
                                        <Link
                                            v-if="reg.event"
                                            :href="route('event.detail', reg.event.id)"
                                            class="flex-1 sm:flex-none text-center rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#475569] hover:bg-[#F8FAFC] hover:border-[#CBD5E1] transition-colors"
                                        >
                                            Lihat Detail
                                        </Link>

                                        <!-- TC-01 & TC-02: Tombol Beri Rating (event selesai & belum rating) -->
                                        <button
                                            v-if="isEventCompleted(reg.event) && !reg.event?.user_rating && reg.status !== 'cancelled'"
                                            @click="openRatingModal(reg, false)"
                                            :id="`btn-beri-rating-${reg.id}`"
                                            class="flex-1 sm:flex-none rounded-xl border border-[#FDE68A] bg-[#FFFBEB] px-4 py-2.5 text-sm font-semibold text-[#92400E] hover:bg-[#FEF3C7] transition-colors flex items-center justify-center gap-1.5"
                                        >
                                            <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            Beri Rating
                                        </button>

                                        <!-- TC-10 / A1: Sudah Rating – Lihat/Edit -->
                                        <button
                                            v-else-if="isEventCompleted(reg.event) && reg.event?.user_rating && reg.status !== 'cancelled'"
                                            @click="openRatingModal(reg, true)"
                                            :id="`btn-lihat-rating-${reg.id}`"
                                            class="flex-1 sm:flex-none rounded-xl border border-[#A7F3D0] bg-[#ECFDF5] px-4 py-2.5 text-sm font-semibold text-[#065F46] hover:bg-[#D1FAE5] transition-colors flex items-center justify-center gap-1.5"
                                        >
                                            <svg class="h-4 w-4 text-[#059669]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            Lihat Rating Anda
                                        </button>

                                        <button
                                            v-if="reg.status === 'registered' && !isEventCompleted(reg.event)"
                                            @click="openCancel(reg)"
                                            :id="`btn-batalkan-${reg.id}`"
                                            class="flex-1 sm:flex-none rounded-xl border border-[#FECACA] bg-white px-4 py-2.5 text-sm font-semibold text-[#DC2626] hover:bg-[#FEF2F2] transition-colors"
                                        >
                                            Batalkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-white py-20 px-6 text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[#EFF6FF] mb-5">
                        <svg class="h-10 w-10 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#0F172A] mb-2">
                        {{ activeFilter === 'all' ? 'Belum Ada Event' : 'Tidak Ada Event dengan Status Ini' }}
                    </h3>
                    <p class="text-sm text-[#64748B] max-w-md mb-6">
                        {{ activeFilter === 'all'
                            ? 'Anda belum mendaftarkan diri ke event manapun. Jelajahi event yang tersedia dan daftarkan diri sekarang!'
                            : 'Coba pilih filter lain atau kembali ke halaman event.' }}
                    </p>
                    <Link :href="route('event')" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-[#1D4ED8] transition-all hover:shadow-lg hover:shadow-[#2563EB]/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Jelajahi Event
                    </Link>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
