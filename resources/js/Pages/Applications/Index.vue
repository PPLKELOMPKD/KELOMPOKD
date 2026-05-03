<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    applications: Array,
    statusCounts: Object,
    filters: Object,
});

const statusConfig = {
    submitted: {
        label: 'Diajukan',
        classes: 'border border-[#CBD5E1] bg-[#F8FAFC] text-[#475467]',
    },
    reviewed: {
        label: 'Seleksi Berkas',
        classes: 'border border-[#93C5FD] bg-[#EFF6FF] text-[#2563EB]',
    },
    interview: {
        label: 'Wawancara',
        classes: 'border border-[#86EFAC] bg-[#F0FDF4] text-[#16A34A]',
    },
    accepted: {
        label: 'Diterima',
        classes: 'border-0 bg-[#16A34A] text-white',
    },
    rejected: {
        label: 'Ditolak',
        classes: 'border-0 bg-[#EF4444] text-white',
    },
};

const getStatus = (status) => {
    return statusConfig[status] ?? { label: status, classes: 'border border-[#CBD5E1] bg-[#F8FAFC] text-[#475467]' };
};

/* Generate a consistent pastel color from company name */
const avatarColors = [
    'from-[#2563EB] to-[#60A5FA]',
    'from-[#7C3AED] to-[#A78BFA]',
    'from-[#0891B2] to-[#67E8F9]',
    'from-[#059669] to-[#6EE7B7]',
    'from-[#D97706] to-[#FCD34D]',
    'from-[#DC2626] to-[#FCA5A5]',
    'from-[#4F46E5] to-[#818CF8]',
    'from-[#0D9488] to-[#5EEAD4]',
];

const getAvatarColor = (name) => {
    let hash = 0;
    for (let i = 0; i < (name?.length ?? 0); i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return avatarColors[Math.abs(hash) % avatarColors.length];
};
</script>

<template>
    <Head title="Riwayat Lamaran — SIKARA" />

    <SikaraLayout
        title="Riwayat Lamaran"
        subtitle="Pantau status dan riwayat pengajuan magang Anda."
    >
        <!-- ── Empty State ── -->
        <div v-if="!applications || applications.length === 0" class="rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-white px-8 py-20 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#F8FAFC] border border-[#E2E8F0]">
                <svg class="h-10 w-10 text-[#CBD5E1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                    <rect x="9" y="3" width="6" height="4" rx="1" />
                    <path d="M9 14l2 2 4-4" />
                </svg>
            </div>
            <h3 class="mt-6 text-xl font-bold text-[#344054]">Belum Ada Lamaran</h3>
            <p class="mt-2 text-sm text-[#667085] max-w-md mx-auto">Mulai eksplorasi lowongan magang dan kirim lamaran pertama Anda sekarang!</p>
            <Link
                :href="route('lowongan')"
                class="mt-8 inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-8 py-3 text-sm font-bold text-white transition-all hover:bg-[#1d4ed8] hover:shadow-lg hover:shadow-blue-500/25"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                Cari Lowongan
            </Link>
        </div>

        <!-- ── Application List ── -->
        <div v-else class="space-y-4">
            <article
                v-for="app in applications"
                :key="app.id"
                class="rounded-2xl border border-[#eaecf0] bg-white px-6 py-5 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)] transition-all duration-200 hover:shadow-md"
            >
                <div class="flex items-center gap-5">
                    <!-- Company Avatar -->
                    <div
                        :class="[
                            'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-lg font-bold text-white shadow-sm',
                            getAvatarColor(app.internship.company_name),
                        ]"
                    >
                        {{ app.internship.company_name?.charAt(0)?.toUpperCase() }}
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-[#101828] truncate">{{ app.internship.title }}</h3>
                        <p class="text-sm text-[#667085] mt-0.5 truncate">{{ app.internship.company_name }}</p>
                    </div>

                    <!-- Right Side: Date + Status + Detail -->
                    <div class="hidden sm:flex items-center gap-4 shrink-0">
                        <!-- Date -->
                        <span class="flex items-center gap-1.5 text-sm text-[#667085]">
                            <svg class="h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4" /><path d="M8 2v4" /><path d="M3 10h18" />
                            </svg>
                            {{ app.applied_at }}
                        </span>

                        <!-- Status Badge -->
                        <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold', getStatus(app.status).classes]">
                            {{ getStatus(app.status).label }}
                        </span>

                        <!-- Detail Button -->
                        <Link
                            :href="route('internships.show', app.internship.id)"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-[#2563EB] px-5 text-sm font-semibold text-white transition-all hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-500/20"
                        >
                            Detail
                        </Link>
                    </div>
                </div>

                <!-- Mobile: Date + Status + Detail (stacked below) -->
                <div class="mt-4 flex flex-wrap items-center gap-3 sm:hidden">
                    <span class="flex items-center gap-1.5 text-xs text-[#667085]">
                        <svg class="h-3.5 w-3.5 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4" /><path d="M8 2v4" /><path d="M3 10h18" />
                        </svg>
                        {{ app.applied_at }}
                    </span>
                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold', getStatus(app.status).classes]">
                        {{ getStatus(app.status).label }}
                    </span>
                    <Link
                        :href="route('internships.show', app.internship.id)"
                        class="ml-auto inline-flex h-8 items-center justify-center rounded-lg bg-[#2563EB] px-4 text-xs font-semibold text-white transition-all hover:bg-[#1d4ed8]"
                    >
                        Detail
                    </Link>
                </div>
            </article>
        </div>
    </SikaraLayout>
</template>
