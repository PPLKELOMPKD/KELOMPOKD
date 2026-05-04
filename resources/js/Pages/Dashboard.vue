<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    title: String,
    subtitle: String,
    role: String,
    stats: Array,
    pipeline: Array,
    recentApplicants: Array,
    upcomingEvents: Array,
    notifications: Array,
    stubMessage: String,
    profileSummary: Object,
    latestInternships: Array,
    latestNotifications: Array,
});

const getColorClass = (color, type) => {
    const map = {
        blue: { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-200' },
        green: { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-200' },
        emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-200' },
        purple: { bg: 'bg-purple-50', text: 'text-purple-600', border: 'border-purple-200' },
        orange: { bg: 'bg-orange-50', text: 'text-orange-600', border: 'border-orange-200' },
        red: { bg: 'bg-red-50', text: 'text-red-600', border: 'border-red-200' },
    };
    return map[color]?.[type] || map.blue[type];
};

const isStudentDashboard = computed(() => Boolean(props.profileSummary && props.role !== 'perusahaan'));
const isCompanyDashboard = computed(() => props.role === 'perusahaan');

// ── Modal state for Terima/Tolak on dashboard ──
const showModal = ref(false);
const modalAction = ref('');
const modalApplicant = ref(null);
const processing = ref(false);

const openModal = (applicant, action) => { modalApplicant.value = applicant; modalAction.value = action; showModal.value = true; };
const closeModal = () => { showModal.value = false; modalApplicant.value = null; modalAction.value = ''; };
const confirmAction = () => {
    if (!modalApplicant.value) return;
    processing.value = true;
    router.patch(route('perusahaan.applicants.updateStatus', modalApplicant.value.id), {
        status: modalAction.value, redirect: 'index',
    }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; closeModal(); },
    });
};
const modalConfig = computed(() => {
    const c = {
        lolos:         { title: 'Terima Pelamar', msg: `Terima ${modalApplicant.value?.name} untuk posisi ${modalApplicant.value?.position}?`, iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600', btnClass: 'bg-emerald-600 hover:bg-emerald-700', btnText: 'Ya, Terima' },
        'tidak lolos': { title: 'Tolak Pelamar',  msg: `Tolak ${modalApplicant.value?.name} untuk posisi ${modalApplicant.value?.position}?`,  iconBg: 'bg-red-100',     iconColor: 'text-red-600',     btnClass: 'bg-red-600 hover:bg-red-700',         btnText: 'Ya, Tolak' },
    };
    return c[modalAction.value] || c.lolos;
});
</script>

<template>
    <Head :title="title" />

    <SikaraLayout :title="title" :subtitle="subtitle">
        <template #headerAction v-if="isCompanyDashboard">
            <div class="flex flex-wrap items-center gap-3">
                <Link :href="route('perusahaan.internships.create')" class="flex h-11 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Lowongan
                </Link>
                <button class="flex h-11 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-slate-50 transition-all">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    Tambah Event
                </button>
            </div>
        </template>

        <div v-if="isStudentDashboard" class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
            <div class="space-y-6">
                <section class="rounded-[16px] border border-[#eaecf0] bg-white px-6 pt-6 pb-5 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full border-4 border-[#f3f4f6] bg-[#e5e7eb] text-4xl font-semibold text-[#344054]">
                            {{ profileSummary.name?.charAt(0) }}
                        </div>
                        <h3 class="mt-5 text-[32px] font-semibold tracking-[-0.03em] text-[#101828]">{{ profileSummary.name }}</h3>
                        <p class="mt-1 text-lg text-[#475467]">{{ profileSummary.department }}</p>
                        <p class="mt-1 text-sm text-[#667085]">{{ profileSummary.university }}</p>
                        <Link
                            :href="route('profile.show')"
                            class="mt-5 flex h-11 w-full items-center justify-center rounded-xl bg-black px-4 text-base font-medium text-white"
                        >
                            Edit Profil
                        </Link>
                    </div>

                    <div class="mt-6 border-t border-[#f2f4f7] pt-5 text-sm text-[#344054]">
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16v12H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <span>{{ $page.props.auth.user.email }}</span>
                        </div>
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                                </svg>
                            </span>
                            <span>{{ profileSummary.phone }}</span>
                        </div>
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                            </span>
                            <span>{{ profileSummary.location }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-[28px] font-semibold tracking-[-0.03em] text-[#101828]">Tentang Saya</h3>
                    <p class="mt-4 text-sm leading-8 text-[#475467]">{{ profileSummary.bio }}</p>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-[28px] font-semibold tracking-[-0.03em] text-[#101828]">Resume / CV</h3>
                    <div class="mt-4 rounded-xl border-2 border-dashed border-[#d0d5dd] px-6 py-9 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full text-[#98a2b3]">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 16V6" />
                                <path d="m7 11 5-5 5 5" />
                                <path d="M4 18v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                            </svg>
                        </div>
                        <p class="mt-3 text-base text-[#475467]">Unggah CV Terbaru</p>
                        <p class="mt-1 text-sm text-[#667085]">atau seret file ke sini</p>
                        <p class="mt-2 text-sm text-[#98a2b3]">PDF, DOC (Max. 5MB)</p>
                    </div>
                    <Link
                        :href="route('cv.download')"
                        class="mt-4 flex h-11 w-full items-center justify-center rounded-xl bg-[#1e293b] px-4 text-base font-medium text-white"
                    >
                        Generate CV Otomatis (PDF)
                    </Link>
                    <div class="mt-4 flex items-center justify-between rounded-xl bg-[#f9fafb] px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-[#101828]">CV_{{ profileSummary.name?.replaceAll(' ', '') }}.pdf</p>
                            <p class="text-xs text-[#667085]">Diambil dari data profil terbaru</p>
                        </div>
                        <Link :href="route('cv.download')" class="rounded-lg p-2 text-[#667085]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                        </Link>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div v-for="stat in stats" :key="stat.label" class="rounded-[12px] bg-[#f9fafb] p-5">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-[#667085]">{{ stat.label }}</p>
                            <p class="mt-3 text-3xl font-semibold text-[#101828]">{{ stat.value }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[28px] font-semibold tracking-[-0.03em] text-[#101828]">Lowongan Terbaru</h3>
                        <Link :href="route('internships.index')" class="text-sm font-medium text-black">Lihat semua</Link>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div v-for="internship in latestInternships" :key="internship.id" class="rounded-xl bg-[#f9fafb] p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-base font-semibold text-[#101828]">{{ internship.title }}</p>
                                    <p class="mt-1 text-sm text-[#475467]">{{ internship.company_name }} • {{ internship.location }}</p>
                                    <p class="mt-2 text-sm leading-6 text-[#667085]">{{ internship.requirements }}</p>
                                    <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-[#98a2b3]">Deadline {{ internship.deadline_at }}</p>
                                </div>
                                <Link :href="route('internships.index')" class="inline-flex h-10 items-center justify-center rounded-xl bg-black px-4 text-sm font-medium text-white">
                                    Detail
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[28px] font-semibold tracking-[-0.03em] text-[#101828]">Notifikasi Terbaru</h3>
                        <Link :href="route('notifications.index')" class="text-sm font-medium text-black">Buka inbox</Link>
                    </div>
                    <div class="mt-6 space-y-4">
                        <div v-for="item in latestNotifications" :key="item.id" class="rounded-xl bg-[#f9fafb] p-4">
                            <p class="text-base font-semibold text-[#101828]">{{ item.title }}</p>
                            <p class="mt-2 text-sm leading-6 text-[#667085]">{{ item.message }}</p>
                            <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-[#98a2b3]">
                                {{ item.type }}{{ item.read_at ? ` • ${item.read_at}` : ' • Baru' }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div v-else-if="isCompanyDashboard" class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="stat in stats" :key="stat.label" class="flex flex-col justify-between rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">{{ stat.label }}</p>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="[getColorClass(stat.color, 'bg'), getColorClass(stat.color, 'text')]">
                            <svg v-if="stat.icon === 'briefcase'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path d="M4 7h16v12H4z"/><path d="M4 12h16"/></svg>
                            <svg v-if="stat.icon === 'users'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <svg v-if="stat.icon === 'user-check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                            <svg v-if="stat.icon === 'check-circle'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <svg v-if="stat.icon === 'calendar'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <svg v-if="stat.icon === 'ticket'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-4xl font-bold text-[#101828]">{{ stat.value }}</p>
                        <p v-if="stat.trend" class="text-sm font-medium flex items-center gap-1" :class="stat.trendUp ? 'text-emerald-600' : 'text-red-600'">
                            <svg v-if="stat.trendUp" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            {{ stat.trend }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Pipeline -->
                <div class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)] lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-[#101828]">Pipeline Rekrutmen</h3>
                        <button class="text-[#667085] hover:text-[#101828]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                        </button>
                    </div>
                    <div class="mt-10 mb-4 flex items-center justify-between relative px-4">
                        <!-- Connecting line -->
                        <div class="absolute left-10 right-10 top-6 h-0.5 bg-[#eaecf0] z-0"></div>
                        <div v-for="(item, index) in pipeline" :key="item.label" class="relative z-10 flex flex-col items-center flex-1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white border-4 border-white shadow-sm ring-1 ring-[#eaecf0]" :class="[getColorClass(item.color, 'bg'), getColorClass(item.color, 'text')]">
                                <svg v-if="item.icon === 'clock'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <svg v-if="item.icon === 'chat'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
                                <svg v-if="item.icon === 'check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <svg v-if="item.icon === 'x'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </div>
                            <p class="mt-4 text-[28px] font-bold text-[#101828]">{{ item.value }}</p>
                            <p class="mt-1 text-sm font-medium text-[#667085]">{{ item.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Trend -->
                <div class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-xl font-semibold text-[#101828]">Tren Rekrutmen</h3>
                    <!-- A simple bar chart representation using flex divs -->
                    <div class="mt-8 flex h-32 items-end justify-between gap-3">
                         <div v-for="(h, i) in [30, 45, 35, 60, 50, 85]" :key="i" class="w-full rounded-t bg-[#DBEAFE] relative group transition-all" :style="`height: ${h}%`">
                             <div v-if="i===5" class="absolute inset-0 rounded-t bg-[#2563EB]"></div>
                         </div>
                    </div>
                    <div class="mt-3 flex justify-between text-xs font-medium text-[#667085]">
                         <span>Mei</span><span>Jun</span><span>Jul</span><span>Ags</span><span>Sep</span><span>Okt</span>
                    </div>
                </div>
            </div>

            <!-- Table Daftar Pelamar -->
            <div class="rounded-2xl border border-[#eaecf0] bg-white shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)] overflow-hidden">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h3 class="text-xl font-semibold text-[#101828]">Daftar Pelamar Terbaru</h3>
                        <p class="mt-1 text-sm text-[#667085]">Menampilkan {{ recentApplicants?.length || 0 }} pelamar terakhir</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('perusahaan.applicants.index')" class="flex h-10 w-10 items-center justify-center rounded-lg border border-[#eaecf0] text-[#667085] hover:bg-[#EFF6FF] hover:text-[#2563EB] hover:border-[#2563EB] transition-all duration-300 active:scale-95">
                             <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        </Link>
                        <Link :href="route('perusahaan.applicants.index')" class="flex h-10 items-center justify-center rounded-lg border border-[#eaecf0] px-4 text-sm font-semibold text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] hover:border-[#2563EB] transition-all duration-300 active:scale-95 shadow-sm">
                            Lihat Semua
                        </Link>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#667085]">
                        <thead class="border-y border-[#eaecf0] bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                            <tr>
                                <th class="px-6 py-4">NAMA PELAMAR</th>
                                <th class="px-6 py-4">POSISI DILAMAR</th>
                                <th class="px-6 py-4">UNIVERSITAS & JURUSAN</th>
                                <th class="px-6 py-4">TGL. DAFTAR</th>
                                <th class="px-6 py-4">STATUS</th>
                                <th class="px-6 py-4 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eaecf0]">
                            <tr v-for="applicant in recentApplicants" :key="applicant.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold text-sm" :class="[getColorClass(applicant.statusColor, 'bg'), getColorClass(applicant.statusColor, 'text')]">
                                            {{ applicant.initials }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-[#101828]">{{ applicant.name }}</p>
                                            <p class="text-xs text-[#667085]">{{ applicant.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[#344054] font-medium">{{ applicant.position }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-[#344054] font-medium">{{ applicant.university }}</p>
                                    <p class="text-xs">{{ applicant.major }}</p>
                                </td>
                                <td class="px-6 py-4 font-medium">{{ applicant.date }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="[getColorClass(applicant.statusColor, 'bg'), getColorClass(applicant.statusColor, 'text'), getColorClass(applicant.statusColor, 'border'), 'border']">
                                        {{ applicant.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 text-xs font-semibold">
                                        <button v-if="applicant.statusRaw !== 'lolos'" @click="openModal(applicant, 'lolos')" class="rounded-md bg-emerald-50 text-emerald-600 px-3 py-1.5 hover:bg-emerald-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">Terima</button>
                                        <button v-if="applicant.statusRaw !== 'tidak lolos'" @click="openModal(applicant, 'tidak lolos')" class="rounded-md bg-red-50 text-red-600 px-3 py-1.5 hover:bg-red-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">Tolak</button>
                                        <Link :href="route('perusahaan.applicants.show', applicant.id)" class="rounded-md border border-[#eaecf0] bg-white text-[#344054] px-3 py-1.5 hover:bg-slate-800 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95 inline-flex items-center justify-center">Detail</Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!recentApplicants || recentApplicants.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-400 mb-4">
                                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-[#344054]">Belum ada pelamar</p>
                                    <p class="mt-1 text-xs text-[#667085]">Buat lowongan magang untuk mulai menerima lamaran.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="grid gap-6 lg:grid-cols-2">
                 <!-- Event Mendatang -->
                 <div class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                         <h3 class="text-xl font-semibold text-[#101828]">Event Mendatang</h3>
                         <Link :href="route('perusahaan.events.index')" class="text-sm font-semibold text-[#2563EB] hover:text-[#1d4ed8] hover:underline transition-all">Kelola Event</Link>
                    </div>
                    <div class="mt-6 space-y-4">
                         <div v-if="!upcomingEvents || upcomingEvents.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
                             <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-400 mb-4">
                                 <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                             </div>
                             <p class="text-sm font-semibold text-[#344054]">Belum ada event</p>
                             <p class="mt-1 text-xs text-[#667085]">Buat event pertama Anda untuk menarik peserta.</p>
                         </div>
                         <div v-for="event in upcomingEvents" :key="event.id" class="flex items-center gap-4 rounded-xl border border-[#eaecf0] p-4 transition hover:border-[#2563EB]/30 hover:bg-blue-50/30">
                             <div class="flex h-14 w-14 flex-col items-center justify-center rounded-lg bg-[#EFF6FF] text-[#2563EB]">
                                 <span class="text-[10px] font-bold uppercase tracking-wider">{{ event.month }}</span>
                                 <span class="text-xl font-bold leading-none">{{ event.date }}</span>
                             </div>
                             <div class="flex-1">
                                 <p class="font-semibold text-[#101828]">{{ event.title }}</p>
                                 <div class="mt-1.5 flex items-center gap-3 text-xs text-[#667085] font-medium">
                                     <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>{{ event.time }}</span>
                                     <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>{{ event.location }}</span>
                                 </div>
                             </div>
                             <div class="text-right">
                                 <p class="text-lg font-bold text-[#101828]">{{ event.participants || 'Draf' }}</p>
                                 <p class="text-[11px] uppercase tracking-wider font-semibold text-[#667085]">{{ event.participants ? 'Peserta' : 'Status' }}</p>
                             </div>
                         </div>
                         <Link :href="route('perusahaan.events.index')" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-[#d0d5dd] bg-white py-3 text-sm font-semibold text-[#344054] hover:bg-[#EFF6FF] hover:border-[#2563EB] hover:text-[#2563EB] hover:shadow-sm transition-all duration-300 active:scale-95 group">
                             <svg class="h-4 w-4 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                             Tambah Event Baru
                         </Link>
                    </div>
                 </div>

                 <div class="space-y-6">
                     <!-- Laporan -->
                     <div class="rounded-2xl bg-[#0F172A] p-6 text-white shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)] relative overflow-hidden">
                         <!-- Decorative background -->
                         <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-500 rounded-full blur-3xl opacity-20"></div>
                         <div class="relative z-10">
                             <h3 class="text-xl font-semibold">Laporan Komprehensif</h3>
                             <p class="mt-2 text-sm text-slate-300 leading-relaxed pr-8">Unduh data rekrutmen, analitik event, dan performa pelamar dalam format PDF atau Excel.</p>
                             <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                 <Link :href="route('perusahaan.reports.index')" class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-700 bg-white/5 py-3 text-sm font-semibold hover:bg-[#2563EB] hover:border-[#2563EB] hover:shadow-[0_0_15px_rgba(37,99,235,0.4)] hover:-translate-y-1 transition-all duration-300 active:scale-95">
                                     <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                     Laporan Rekrutmen
                                 </Link>
                                 <Link :href="route('perusahaan.reports.index')" class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-700 bg-white/5 py-3 text-sm font-semibold hover:bg-[#10B981] hover:border-[#10B981] hover:shadow-[0_0_15px_rgba(16,185,129,0.4)] hover:-translate-y-1 transition-all duration-300 active:scale-95">
                                     <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                     Laporan Event
                                 </Link>
                             </div>
                         </div>
                     </div>
                     
                     <!-- Notifikasi -->
                     <div class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                         <div class="flex items-center justify-between">
                             <h3 class="text-xl font-semibold text-[#101828]">Notifikasi Sistem</h3>
                             <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600">2 Baru</span>
                         </div>
                         <div class="mt-6 space-y-5">
                             <div v-for="notif in notifications" :key="notif.id" class="flex gap-4">
                                 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#f9fafb] border border-[#f2f4f7] text-[#667085]">
                                     <svg v-if="notif.type === 'user'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                     <svg v-if="notif.type === 'calendar'" class="h-4 w-4 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                     <svg v-if="notif.type === 'check'" class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                 </div>
                                 <div>
                                     <p class="text-sm font-medium text-[#344054]" v-html="notif.message.replace(/(Frontend Engineer|Budi Santoso)/, '<strong class=\'text-[#101828]\'>$1</strong>')"></p>
                                     <p class="mt-1 text-xs font-medium text-[#98a2b3]">{{ notif.time }}</p>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
            </div>
        </div>

        <div v-else>
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]"
                >
                    <div class="text-sm uppercase tracking-[0.2em] text-[#98a2b3]">{{ stat.label }}</div>
                    <div class="mt-4 text-3xl font-semibold text-[#101828]">{{ stat.value }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                <h3 class="text-xl font-semibold text-[#101828]">Ringkasan Sprint 1</h3>
                <p class="mt-3 text-sm leading-7 text-[#667085]">
                    Modul autentikasi, dashboard, profil mahasiswa, skill, lamaran magang, notifikasi, dan generator CV dibangun sebagai fondasi awal SIKARA.
                </p>
                <p v-if="stubMessage" class="mt-4 rounded-xl bg-[#f9fafb] px-4 py-3 text-sm text-[#667085]">
                    {{ stubMessage }}
                </p>
            </div>
        </div>
    </SikaraLayout>

    <!-- Confirmation Modal for Dashboard Terima/Tolak -->
    <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
                    <div class="text-center">
                        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full" :class="modalConfig.iconBg">
                            <svg v-if="modalAction === 'lolos'" class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <svg v-else class="h-8 w-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#101828]">{{ modalConfig.title }}</h3>
                        <p class="mt-3 text-sm text-[#667085] leading-relaxed">{{ modalConfig.msg }}</p>
                        <p class="mt-2 text-xs text-[#98a2b3]">Notifikasi akan otomatis dikirim ke pelamar.</p>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button @click="closeModal" class="flex-1 rounded-xl border border-[#d0d5dd] bg-white py-3 text-sm font-semibold text-[#344054] hover:bg-[#f9fafb] transition-all">Batal</button>
                        <button @click="confirmAction" :disabled="processing" :class="['flex-1 rounded-xl py-3 text-sm font-semibold text-white transition-all shadow-sm disabled:opacity-50', modalConfig.btnClass]">
                            <span v-if="processing" class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Memproses...
                            </span>
                            <span v-else>{{ modalConfig.btnText }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
