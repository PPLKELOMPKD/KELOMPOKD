<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    courses: { type: Array, default: () => [] },
    isAuthenticated: { type: Boolean, default: false },
    isMahasiswa: { type: Boolean, default: false },
});

const search = ref('');
const filterLevel = ref('');
const filterStatus = ref('');

const levelColors = {
    'BEGINNER': { bg: 'bg-[#ECFDF5]', text: 'text-[#059669]', dot: 'bg-[#10B981]', label: 'Pemula' },
    'INTERMEDIATE': { bg: 'bg-[#EFF6FF]', text: 'text-[#2563EB]', dot: 'bg-[#3B82F6]', label: 'Menengah' },
    'ADVANCED': { bg: 'bg-[#FDF4FF]', text: 'text-[#9333EA]', dot: 'bg-[#A855F7]', label: 'Lanjutan' },
};
const getLevelInfo = (level) => levelColors[level] || levelColors['BEGINNER'];

const statusLabels = {
    'available': { label: 'Tersedia', color: 'text-[#059669]', bg: 'bg-[#ECFDF5]' },
    'in_progress': { label: 'Sedang Berjalan', color: 'text-[#2563EB]', bg: 'bg-[#EFF6FF]' },
    'completed': { label: 'Selesai', color: 'text-[#7C3AED]', bg: 'bg-[#F5F3FF]' },
};
const getStatusInfo = (status) => statusLabels[status] || statusLabels['available'];

const filteredCourses = computed(() => {
    let result = [...props.courses];

    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        result = result.filter(c =>
            (c.title || '').toLowerCase().includes(q) ||
            (c.provider || '').toLowerCase().includes(q) ||
            (c.description || '').toLowerCase().includes(q)
        );
    }

    if (filterLevel.value) {
        result = result.filter(c => c.level === filterLevel.value);
    }

    if (filterStatus.value) {
        result = result.filter(c => c.status === filterStatus.value);
    }

    return result;
});

const resetFilters = () => {
    search.value = '';
    filterLevel.value = '';
    filterStatus.value = '';
};

const hasActiveFilters = computed(() => search.value || filterLevel.value || filterStatus.value);

const enroll = (courseSlug) => {
    router.post(route('lms.enrollments.store', courseSlug), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="LMS — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <!-- Hero Header -->
        <div class="bg-gradient-to-b from-[#F1F5F9] to-white pb-12 pt-20 relative z-30 w-full">
            <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-10">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#DBEAFE] bg-[#EFF6FF] px-5 py-2 shadow-sm">
                        <span class="flex h-2 w-2 rounded-full bg-[#2563EB] animate-pulse"></span>
                        <span class="text-xs font-bold text-[#1D4ED8] tracking-wide uppercase">Sistem Manajemen Pembelajaran</span>
                    </div>
                    <h1 class="text-4xl font-extrabold text-[#0F172A] tracking-tight">Tingkatkan <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2563EB] to-[#10B981]">Skill & Kompetensi</span></h1>
                    <p class="mt-4 text-[#64748B] max-w-2xl mx-auto">Akses ratusan materi pembelajaran interaktif, tugas praktikal, dan kuis evaluasi dari perusahaan mitra SIKARA.</p>
                </div>

                <!-- Search & Filter -->
                <div class="w-full bg-white p-6 rounded-2xl shadow-xl shadow-[#2563EB]/5 border border-[#E2E8F0]">
                    <div class="flex flex-col md:flex-row gap-3 mb-3">
                        <div class="flex-grow flex items-center w-full bg-white border border-[#E2E8F0] hover:border-[#CBD5E1] transition-all duration-300 rounded-xl py-3 px-4 outline-none focus-within:ring-2 focus-within:ring-[#2563EB]/20 focus-within:border-[#2563EB]">
                            <svg class="h-5 w-5 text-[#94A3B8] mr-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" v-model="search" placeholder="Cari kursus, materi, atau penyedia..." class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm text-[#0F172A] placeholder-[#94A3B8]" />
                        </div>
                        <select v-model="filterLevel" class="rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] bg-white">
                            <option value="">Semua Level</option>
                            <option value="BEGINNER">Pemula</option>
                            <option value="INTERMEDIATE">Menengah</option>
                            <option value="ADVANCED">Lanjutan</option>
                        </select>
                        <select v-if="isMahasiswa" v-model="filterStatus" class="rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm text-[#0F172A] focus:ring-2 focus:ring-[#2563EB]/20 focus:border-[#2563EB] bg-white">
                            <option value="">Semua Status</option>
                            <option value="available">Tersedia</option>
                            <option value="in_progress">Sedang Berjalan</option>
                            <option value="completed">Selesai</option>
                        </select>
                        <button v-if="hasActiveFilters" @click="resetFilters" class="bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#64748B] p-3 rounded-xl transition-all flex items-center justify-center shrink-0" title="Reset Filter">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Listings -->
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8 py-16 relative z-10">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">
                        Kursus Tersedia
                        <span class="text-[#2563EB]">({{ filteredCourses.length }})</span>
                    </h2>
                    <p v-if="hasActiveFilters" class="text-sm text-[#64748B] mt-1">
                        Menampilkan hasil filter dari {{ courses.length }} kursus
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="course in filteredCourses" :key="course.slug"
                    class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-0 overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/10 hover:border-[#CBD5E1]"
                >
                    <!-- Card Image -->
                    <div class="relative">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#2563EB] to-[#10B981] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="aspect-video bg-gradient-to-br from-[#1E293B] to-[#0F172A] relative overflow-hidden">
                            <img v-if="course.image_url" :src="course.image_url" :alt="course.title" class="h-full w-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500" />
                            <div v-else class="h-full w-full flex items-center justify-center">
                                <svg class="h-16 w-16 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <!-- Level Badge -->
                            <div class="absolute top-3 left-3">
                                <span :class="[getLevelInfo(course.level).bg, getLevelInfo(course.level).text]" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur-sm">
                                    <span :class="getLevelInfo(course.level).dot" class="h-1.5 w-1.5 rounded-full"></span>
                                    {{ getLevelInfo(course.level).label }}
                                </span>
                            </div>
                            <!-- Status Badge (enrolled) -->
                            <div v-if="course.is_enrolled" class="absolute top-3 right-3">
                                <span :class="[getStatusInfo(course.status).bg, getStatusInfo(course.status).color]" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur-sm">
                                    {{ getStatusInfo(course.status).label }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 pb-0">
                            <!-- Provider -->
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#2563EB] to-[#60A5FA] font-bold text-white text-sm uppercase shadow-md shadow-[#2563EB]/20">
                                    {{ (course.provider || 'S').charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-[#64748B] line-clamp-1">{{ course.provider }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span v-if="course.location" class="text-[10px] text-[#94A3B8] flex items-center gap-0.5">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                            {{ course.location }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors line-clamp-2 leading-snug">
                                {{ course.title }}
                            </h3>

                            <!-- Description -->
                            <p class="mt-2 text-xs text-[#64748B] line-clamp-2 leading-relaxed">{{ course.description }}</p>
                        </div>

                        <!-- Tags & Meta -->
                        <div class="px-6 pt-3 pb-4">
                            <div class="flex flex-wrap gap-1.5">
                                <span v-if="course.started_at" class="inline-flex items-center gap-1 rounded-lg bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#475569] border border-[#E2E8F0]">
                                    <svg class="h-3 w-3 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    {{ course.started_at }}
                                </span>
                                <span v-if="course.quota" class="inline-flex items-center gap-1 rounded-lg bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#475569] border border-[#E2E8F0]">
                                    <svg class="h-3 w-3 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    {{ course.enrolled_count }}/{{ course.quota }} Peserta
                                </span>
                            </div>

                            <!-- Progress bar for enrolled -->
                            <div v-if="course.is_enrolled" class="mt-3">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider">Progres</span>
                                    <span class="text-xs font-black text-[#2563EB]">{{ course.progress }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-[#E2E8F0]">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-[#2563EB] to-[#10B981] transition-all duration-500" :style="{ width: `${course.progress}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 pb-5 mt-auto">
                        <div class="flex items-center gap-2">
                            <!-- Enrolled: Go to module -->
                            <Link v-if="course.is_enrolled"
                                :href="route('lms.module.show', course.slug)"
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] py-2.5 text-center text-sm font-bold text-white transition-all hover:bg-[#1D4ED8] hover:shadow-lg hover:shadow-[#2563EB]/20"
                            >
                                Lanjutkan Belajar
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                            <!-- Not enrolled, authenticated mahasiswa: Enroll -->
                            <button v-else-if="isMahasiswa"
                                @click="enroll(course.slug)"
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-2.5 text-center text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Daftar Kursus
                            </button>
                            <!-- Guest: Login -->
                            <Link v-else
                                :href="route('login', { role: 'mahasiswa' })"
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-2.5 text-center text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20"
                            >
                                Masuk untuk Mendaftar
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredCourses.length === 0" class="col-span-full flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-[#F8FAFC] py-16 px-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] mb-4">
                        <svg class="h-8 w-8 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2">Belum Ada Kursus</h3>
                    <p class="text-sm text-[#64748B] max-w-md mb-6">
                        <template v-if="hasActiveFilters">Tidak ada kursus yang sesuai dengan filter Anda. Coba ubah kata kunci atau reset filter.</template>
                        <template v-else>Belum ada kursus yang dipublikasikan oleh perusahaan mitra. Silakan kembali lagi nanti.</template>
                    </p>
                    <button v-if="hasActiveFilters" @click="resetFilters" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#1d4ed8] transition-all shadow-md shadow-[#2563EB]/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Reset Semua Filter
                    </button>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
