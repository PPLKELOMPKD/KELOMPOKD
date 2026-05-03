<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    internship: { type: Object, required: true },
    hasApplied: { type: Boolean, default: false },
    relatedInternships: { type: Array, default: () => [] },
});

const page = usePage();
const form = useForm({ internship_id: props.internship.id });

const apply = () => {
    if (!page.props.auth.user) {
        router.get(route('login', { role: 'mahasiswa' }));
        return;
    }
    form.post(route('internships.apply'), { preserveScroll: true });
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const daysLeft = (d) => {
    if (!d) return null;
    const diff = Math.ceil((new Date(d) - new Date()) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
};

const typeColors = {
    'Magang': { bg: 'bg-[#ECFDF5]', text: 'text-[#059669]' },
    'Full Time': { bg: 'bg-[#EFF6FF]', text: 'text-[#2563EB]' },
    'Part Time': { bg: 'bg-[#FFF7ED]', text: 'text-[#EA580C]' },
};
const getTypeColor = (type) => typeColors[type] || typeColors['Magang'];
</script>

<template>
    <Head :title="`${internship.title} — SIKARA`" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">


        <!-- Hero Header -->
        <div class="relative bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] pt-10 pb-32 overflow-hidden">
            <!-- Background decorations -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#2563EB]/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-[#60A5FA]/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#2563EB]/5 rounded-full blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-5xl px-6">
                <!-- Breadcrumb -->
                <Link :href="route('lowongan')" class="inline-flex items-center gap-2 text-sm font-semibold text-[#94A3B8] hover:text-white mb-8 transition-colors group">
                    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Daftar Lowongan
                </Link>

                <div class="flex flex-col md:flex-row md:items-start gap-6">
                    <!-- Company Logo -->
                    <div class="shrink-0">
                        <div v-if="internship.company_logo" class="h-20 w-20 rounded-2xl border-2 border-white/10 overflow-hidden bg-white shadow-2xl">
                            <img :src="internship.company_logo" :alt="internship.company_name" class="h-full w-full object-contain" />
                        </div>
                        <div v-else class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-[#2563EB] to-[#60A5FA] font-black text-white text-3xl uppercase shadow-2xl shadow-[#2563EB]/30 border-2 border-white/10">
                            {{ internship.company_name ? internship.company_name.charAt(0) : 'C' }}
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <!-- Work type badge -->
                        <span :class="[getTypeColor(internship.work_type || 'Magang').bg, getTypeColor(internship.work_type || 'Magang').text]" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider mb-3">
                            {{ internship.work_type || 'Magang' }}
                        </span>

                        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                            {{ internship.title }}
                        </h1>
                        <p class="mt-3 text-lg font-medium text-[#60A5FA]">
                            <Link v-if="internship.company_id" :href="route('perusahaan.profile', internship.company_id)" class="hover:text-white hover:underline transition-colors">{{ internship.company_name }}</Link>
                            <span v-else>{{ internship.company_name }}</span>
                        </p>

                        <!-- Quick Info Pills -->
                        <div class="flex flex-wrap gap-3 mt-5">
                            <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white/90 border border-white/10">
                                <svg class="h-4 w-4 text-[#60A5FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ internship.location || 'Belum ditentukan' }}
                            </span>
                            <span v-if="internship.duration" class="inline-flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white/90 border border-white/10">
                                <svg class="h-4 w-4 text-[#60A5FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ internship.duration }}
                            </span>
                            <span v-if="internship.salary" class="inline-flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white/90 border border-white/10">
                                <svg class="h-4 w-4 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                {{ internship.salary }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mx-auto max-w-5xl px-6 pb-20 -mt-20 relative z-20">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-[#ECFDF5] text-[#047857] rounded-xl border border-[#A7F3D0] shadow-sm flex items-center">
                <svg class="h-5 w-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-6 p-4 bg-[#FEF2F2] text-[#B91C1C] rounded-xl border border-[#FECACA] shadow-sm flex items-center">
                <svg class="h-5 w-5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Info Highlights Card -->
                    <div class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] overflow-hidden">
                        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#E2E8F0] bg-gradient-to-r from-[#F8FAFC] to-white">
                            <div class="p-5 flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8]">Tipe</span>
                                <span class="text-sm font-bold text-[#0F172A]">{{ internship.work_type || 'Magang' }}</span>
                            </div>
                            <div class="p-5 flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8]">Lokasi</span>
                                <span class="text-sm font-bold text-[#0F172A]">{{ internship.location || '-' }}</span>
                            </div>
                            <div class="p-5 flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8]">Durasi</span>
                                <span class="text-sm font-bold text-[#0F172A]">{{ internship.duration || '-' }}</span>
                            </div>
                            <div class="p-5 flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8]">Kuota</span>
                                <span class="text-sm font-bold text-[#0F172A]">{{ internship.quota || '-' }} orang</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="internship.description" class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] p-6 md:p-8">
                        <h3 class="text-lg font-bold text-[#0F172A] mb-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EFF6FF]">
                                <svg class="h-4 w-4 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            Deskripsi Pekerjaan
                        </h3>
                        <div class="text-sm text-[#475569] leading-7 whitespace-pre-wrap">{{ internship.description }}</div>
                    </div>

                    <!-- Requirements -->
                    <div class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] p-6 md:p-8">
                        <h3 class="text-lg font-bold text-[#0F172A] mb-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#ECFDF5]">
                                <svg class="h-4 w-4 text-[#10B981]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            Kualifikasi & Persyaratan
                        </h3>
                        <div class="text-sm text-[#475569] leading-7 whitespace-pre-wrap">{{ internship.requirements }}</div>
                    </div>

                    <!-- Benefits -->
                    <div v-if="internship.benefits" class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] p-6 md:p-8">
                        <h3 class="text-lg font-bold text-[#0F172A] mb-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FFF7ED]">
                                <svg class="h-4 w-4 text-[#F59E0B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            Benefit & Fasilitas
                        </h3>
                        <div class="text-sm text-[#475569] leading-7 whitespace-pre-wrap">{{ internship.benefits }}</div>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-6">
                    <!-- Apply Card -->
                    <div class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] p-6 sticky top-24">
                        <!-- Deadline -->
                        <div class="flex items-center justify-between mb-6 pb-5 border-b border-[#E2E8F0]">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8] mb-1">Tenggat Waktu</p>
                                <p class="text-base font-bold text-[#0F172A]">{{ formatDate(internship.deadline_at) }}</p>
                            </div>
                            <div v-if="daysLeft(internship.deadline_at) !== null">
                                <span :class="daysLeft(internship.deadline_at) <= 7 ? 'bg-[#FEF2F2] text-[#E11D48] border-[#FECACA]' : 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]'" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold border">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ daysLeft(internship.deadline_at) }} hari
                                </span>
                            </div>
                        </div>

                        <!-- Salary Info -->
                        <div v-if="internship.salary" class="mb-6 p-4 rounded-xl bg-gradient-to-r from-[#ECFDF5] to-[#F0FDF4] border border-[#A7F3D0]">
                            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#059669] mb-1">Kompensasi</p>
                            <p class="text-sm font-bold text-[#047857]">{{ internship.salary }}</p>
                        </div>

                        <!-- Apply Button -->
                        <button
                            :disabled="props.hasApplied || form.processing"
                            :class="[
                                'flex w-full h-14 items-center justify-center rounded-xl px-6 text-base font-bold text-white transition-all',
                                props.hasApplied
                                    ? 'bg-[#94A3B8] cursor-not-allowed shadow-none'
                                    : 'bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:from-[#1D4ED8] hover:to-[#2563EB] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#2563EB]/30 active:translate-y-0',
                            ]"
                            @click="apply"
                        >
                            <svg v-if="!form.processing && !props.hasApplied" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            <svg v-if="props.hasApplied" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>{{ props.hasApplied ? "Sudah Melamar" : "Lamar Sekarang" }}</span>
                        </button>

                        <p v-if="!$page.props.auth.user" class="mt-4 text-xs text-[#64748B] text-center leading-relaxed">
                            Anda akan diarahkan ke halaman login terlebih dahulu.
                        </p>

                        <!-- Company Info -->
                        <div class="mt-6 pt-5 border-t border-[#E2E8F0]">
                            <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#94A3B8] mb-3">Tentang Perusahaan</p>
                            <div class="flex items-center gap-3">
                                <div v-if="internship.company_logo" class="h-10 w-10 rounded-xl border border-[#E2E8F0] overflow-hidden bg-white">
                                    <img :src="internship.company_logo" :alt="internship.company_name" class="h-full w-full object-contain"/>
                                </div>
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#2563EB] to-[#60A5FA] font-bold text-white text-sm uppercase">
                                    {{ internship.company_name ? internship.company_name.charAt(0) : 'C' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#0F172A]">
                                        <Link v-if="internship.company_id" :href="route('perusahaan.profile', internship.company_id)" class="hover:text-[#2563EB] hover:underline transition-colors">{{ internship.company_name }}</Link>
                                        <span v-else>{{ internship.company_name }}</span>
                                    </p>
                                    <p class="text-xs text-[#64748B]">{{ internship.location }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Internships -->
            <div v-if="relatedInternships.length > 0" class="mt-16">
                <h2 class="text-xl font-bold text-[#0F172A] mb-6">Lowongan Terkait</h2>
                <div class="grid gap-6 md:grid-cols-3">
                    <Link v-for="related in relatedInternships" :key="related.id"
                        :href="$page.props.auth.user ? route('internships.show', related.id) : route('login', { role: 'mahasiswa' })"
                        class="group block rounded-2xl border border-[#E2E8F0] bg-white p-6 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/10 hover:border-[#CBD5E1]"
                    >
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#2563EB] to-[#60A5FA] font-bold text-white text-sm uppercase shadow-md shadow-[#2563EB]/20">
                                {{ related.company_name ? related.company_name.charAt(0) : 'C' }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#64748B]">{{ related.company_name }}</p>
                                <span :class="[getTypeColor(related.work_type || 'Magang').bg, getTypeColor(related.work_type || 'Magang').text]" class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold mt-0.5">
                                    {{ related.work_type || 'Magang' }}
                                </span>
                            </div>
                        </div>
                        <h3 class="text-sm font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors line-clamp-2">{{ related.title }}</h3>
                        <div class="flex items-center gap-1 mt-3 text-xs text-[#64748B]">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/></svg>
                            {{ related.location || '-' }}
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>