<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const activeFilter = ref('all');

const filters = [
    { label: 'ALL', value: 'all' },
    { label: 'IN-PROGRESS', value: 'in_progress' },
    { label: 'COMPLETED', value: 'completed' },
];

const filteredCourses = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return props.courses.filter((course) => {
        const matchesFilter = activeFilter.value === 'all' || course.status === activeFilter.value;
        const matchesSearch = !keyword || [course.title, course.provider, course.level]
            .join(' ')
            .toLowerCase()
            .includes(keyword);

        return matchesFilter && matchesSearch;
    });
});

const levelClass = (level) => {
    if (level === 'BEGINNER') return 'text-[#006c49]';
    if (level === 'ADVANCED') return 'text-[#4d556b]';
    return 'text-[#004ac6]';
};

const enroll = (course) => {
    router.post(route('lms.enrollments.store', course.slug));
};

const selectedCourseForDetail = ref(null);
const showDetailModal = ref(false);

const openDetail = (course) => {
    selectedCourseForDetail.value = course;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="LMS — SIKARA">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    </Head>

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <!-- Modal Detail -->
        <div v-if="showDetailModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showDetailModal = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="relative h-56 w-full">
                    <img :src="selectedCourseForDetail.image_url" class="w-full h-full object-cover rounded-t-2xl">
                    <button @click="showDetailModal = false" class="absolute top-4 right-4 bg-black/20 hover:bg-black/40 text-white rounded-full p-2 backdrop-blur-md transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    <div class="absolute bottom-4 left-4">
                        <span class="rounded-md border border-white/20 bg-white/90 px-3 py-1 text-xs font-semibold tracking-[0.05em] shadow-sm backdrop-blur-sm" :class="levelClass(selectedCourseForDetail.level)">
                            {{ selectedCourseForDetail.level }}
                        </span>
                    </div>
                </div>
                <div class="p-8">
                    <div class="mb-6">
                        <p class="text-sm font-bold text-blue-600 uppercase tracking-widest mb-2">{{ selectedCourseForDetail.provider }}</p>
                        <h2 class="text-3xl font-black text-[#0F172A] mb-4">{{ selectedCourseForDetail.title }}</h2>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-600 border-y border-slate-100 py-4 mb-6">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-500">calendar_today</span>
                                <span>{{ selectedCourseForDetail.started_at }} - {{ selectedCourseForDetail.ends_at }}</span>
                            </div>
                            <div class="flex items-center gap-2" v-if="selectedCourseForDetail.location">
                                <span class="material-symbols-outlined text-blue-500">location_on</span>
                                <span>{{ selectedCourseForDetail.location }}</span>
                            </div>
                            <div class="flex items-center gap-2" v-if="selectedCourseForDetail.start_time">
                                <span class="material-symbols-outlined text-blue-500">schedule</span>
                                <span>Pukul {{ selectedCourseForDetail.start_time }}</span>
                            </div>
                            <div class="flex items-center gap-2" v-if="selectedCourseForDetail.quota">
                                <span class="material-symbols-outlined text-blue-500">groups</span>
                                <span>Kuota: {{ selectedCourseForDetail.enrolled_count }} / {{ selectedCourseForDetail.quota }}</span>
                            </div>
                        </div>
                        <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                            <h4 class="text-slate-900 font-bold mb-2">Deskripsi Pelatihan</h4>
                            <p>{{ selectedCourseForDetail.description || 'Tidak ada deskripsi tersedia.' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4 pt-4 border-t border-slate-100">
                        <button v-if="!selectedCourseForDetail.is_enrolled" @click="enroll(selectedCourseForDetail); showDetailModal = false" class="flex-1 rounded-xl bg-[#10b981] py-4 text-sm font-bold text-white hover:bg-[#059669] transition-all shadow-lg shadow-emerald-200">
                            Daftar Sekarang
                        </button>
                        <Link v-else :href="route('lms.module.show', selectedCourseForDetail.slug)" class="flex-1 text-center rounded-xl bg-[#2563eb] py-4 text-sm font-bold text-white hover:bg-[#1d4ed8] transition-all shadow-lg shadow-blue-200">
                            Lanjutkan Belajar
                        </Link>
                        <button @click="showDetailModal = false" class="flex-1 rounded-xl border border-slate-200 py-4 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mx-auto flex min-h-[calc(100vh-76px)] max-w-7xl flex-col items-center justify-between gap-12 px-6 py-12 lg:flex-row lg:py-16 relative">
            <div class="max-w-xl animate-fade-in-up">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#2563EB]/20 bg-[#EFF6FF] px-4 py-1.5 shadow-sm">
                    <span class="text-xs font-black text-[#2563EB] tracking-widest uppercase">Sistem Manajemen Pembelajaran</span>
                </div>

                <h1 class="text-4xl font-black leading-tight text-[#0F172A] lg:text-6xl">
                    Tingkatkan <strong>Skill</strong><br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2563EB] to-[#10B981]">Sebelum Terjun ke Industri</span>
                </h1>

                <p class="mt-6 text-base text-[#64748B] leading-relaxed">
                    Akses ratusan materi pembelajaran interaktif, tugas praktikal, dan kuis evaluasi. SIKARA LMS dirancang bersama pakar industri agar keahlian yang Anda pelajari 100% relevan dengan kebutuhan pasar saat ini.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="#courses" class="rounded-xl bg-[#0F172A] px-8 py-4 text-sm font-bold text-white transition-all hover:bg-[#1E293B] hover:-translate-y-0.5 hover:shadow-lg flex items-center gap-2">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Mulai Belajar
                    </a>
                </div>

                <div class="mt-10 flex gap-8 border-t border-[#E2E8F0] pt-6">
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">250+</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Materi</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">50+</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Mentor</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">100%</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Gratis</div>
                    </div>
                </div>
            </div>

            <div class="relative w-full max-w-lg lg:max-w-xl animate-fade-in-up" style="animation-delay: 200ms;">
                <div class="absolute -inset-4 rounded-[3rem] bg-gradient-to-tr from-[#2563EB] to-[#10B981] opacity-20 blur-2xl"></div>

                <div class="relative w-full overflow-hidden rounded-[2rem] border border-[#E2E8F0] bg-white shadow-2xl">
                    <div class="bg-[#F8FAFC] border-b border-[#E2E8F0] px-4 py-3 flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-red-400"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                        <div class="h-3 w-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="p-8">
                        <div class="h-8 w-48 rounded bg-[#F1F5F9] mb-8"></div>

                        <div class="flex gap-6 mb-6 items-center p-4 rounded-xl border border-[#E2E8F0]">
                            <div class="h-16 w-16 shrink-0 rounded-lg bg-[#2563EB] flex items-center justify-center">
                                <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="w-full">
                                <div class="h-4 w-3/4 rounded bg-[#0F172A] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                                <div class="h-2 w-1/2 rounded bg-[#10B981] mt-1"></div>
                            </div>
                        </div>
                        <div class="flex gap-6 mb-6 items-center p-4 rounded-xl border border-[#E2E8F0]">
                            <div class="h-16 w-16 shrink-0 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center">
                                <svg class="h-8 w-8 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            </div>
                            <div class="w-full">
                                <div class="h-4 w-2/3 rounded bg-[#94A3B8] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section id="courses" class="bg-[#f8f9ff] px-4 pb-24 pt-4 md:px-8 md:pb-28 md:pt-8">
            <div class="mx-auto w-full max-w-screen-2xl">
                <div class="mb-16">
                    <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                        <div class="max-w-2xl">
                            <h2 class="mb-4 text-4xl font-bold leading-tight text-[#0b1c30] md:text-5xl">Pembelajaran Saya</h2>
                            <p class="text-lg leading-relaxed text-[#434655]">
                                Lanjutkan perjalanan belajarmu dan tingkatkan kompetensi sebelum terjun ke industri.
                            </p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#737686]">search</span>
                            <input
                                v-model="search"
                                class="w-full rounded-lg border border-[#c3c6d7] bg-white py-3 pl-10 pr-4 text-base text-[#0b1c30] shadow-sm transition-all focus:border-[#004ac6] focus:ring-2 focus:ring-[#004ac6]/20"
                                placeholder="Cari materi..."
                                type="text"
                            />
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <button
                            v-for="filter in filters"
                            :key="filter.value"
                            type="button"
                            class="rounded-full border px-6 py-2 text-xs font-semibold tracking-[0.05em] shadow-sm transition-colors"
                            :class="activeFilter === filter.value ? 'border-transparent bg-[#2563eb] text-white' : 'border-[#c3c6d7] bg-white text-[#434655] hover:bg-[#eff4ff]'"
                            @click="activeFilter = filter.value"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="course in filteredCourses"
                        :key="course.slug"
                        class="group flex flex-col overflow-hidden rounded-[20px] border border-[#e5eeff] bg-white shadow-[0_4px_24px_rgba(37,99,235,0.04)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_32px_rgba(37,99,235,0.08)]"
                        :class="{ 'opacity-75': course.status === 'completed' }"
                    >
                        <div class="relative h-48 w-full overflow-hidden bg-[#cbdbf5]">
                            <img
                                :src="course.image_url"
                                :alt="course.image_alt || course.title"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                :class="{ grayscale: course.status === 'completed' }"
                            />
                            <div class="absolute left-4 top-4">
                                <span class="rounded-md border border-white/20 bg-white/90 px-3 py-1 text-xs font-semibold tracking-[0.05em] shadow-sm backdrop-blur-sm" :class="levelClass(course.level)">
                                    {{ course.level }}
                                </span>
                            </div>
                            <div v-if="course.status !== 'completed'" class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <div class="flex h-12 w-12 scale-90 items-center justify-center rounded-full bg-white shadow-lg transition-transform duration-300 group-hover:scale-100">
                                    <span class="material-symbols-outlined ml-1 text-2xl text-[#004ac6]" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-4 flex-1">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.05em] text-[#4d556b]">{{ course.provider }}</p>
                                <h3 class="line-clamp-2 text-2xl font-semibold leading-snug text-[#0b1c30]">{{ course.title }}</h3>
                            </div>

                            <div class="mt-auto space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm text-[#434655]" :class="{ 'text-[#006c49]': course.status === 'completed' }">
                                        <span>{{ course.status === 'completed' ? 'Completed' : 'Progress' }}</span>
                                        <span class="font-semibold" :class="course.status === 'completed' ? 'text-[#006c49]' : 'text-[#004ac6]'">{{ course.progress }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-[#e5eeff]">
                                        <div class="h-full rounded-full" :class="course.status === 'completed' ? 'bg-[#006c49]' : 'bg-[#2563eb]'" :style="{ width: `${course.progress}%` }"></div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-[#e5eeff] pt-4 text-sm text-[#737686]">
                                    <template v-if="course.status === 'completed'">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px]">verified</span>
                                            <span>Certificate Earned</span>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                            <span>{{ course.started_at }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px]">flag</span>
                                            <span>{{ course.ends_at }}</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <button @click="openDetail(course)" class="flex-1 rounded-lg border border-slate-200 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                        Lihat Detail
                                    </button>
                                    <Link v-if="course.is_enrolled" :href="route('lms.module.show', course.slug)" class="flex-1 text-center rounded-lg bg-[#2563eb] py-2 text-sm font-semibold text-white hover:bg-[#004ac6]">
                                        Lanjutkan Belajar
                                    </Link>
                                    <button v-else @click="enroll(course)" class="flex-1 rounded-lg bg-[#10b981] py-2 text-sm font-semibold text-white hover:bg-[#059669]">
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PortalLayout>
</template>
