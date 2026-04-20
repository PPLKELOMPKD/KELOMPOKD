<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: String,
    subtitle: String,
    stats: Array,
    stubMessage: String,
    profileSummary: Object,
    latestInternships: Array,
    latestNotifications: Array,
});

const isStudentDashboard = computed(() => Boolean(props.profileSummary));
</script>

<template>
    <Head :title="title" />

    <SikaraLayout :title="title" :subtitle="subtitle">
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
</template>
