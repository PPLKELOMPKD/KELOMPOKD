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

                    <p class="text-white/70 text-sm font-semibold uppercase tracking-wider">
                        Oleh {{ event.company?.name || 'Perusahaan' }}
                    </p>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
