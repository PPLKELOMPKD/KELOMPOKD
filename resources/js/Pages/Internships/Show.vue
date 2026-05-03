<script setup>
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    internship: {
        type: Object,
        required: true
    },
    hasApplied: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const form = useForm({
    internship_id: props.internship.id,
});

const apply = () => {
    // Cek apakah user sudah login
    if (!page.props.auth.user) {
        // Jika belum, lempar ke halaman login
        router.get(route('login', { role: 'mahasiswa' }));
        return;
    }
    
    // Jika sudah, jalankan proses lamar
    form.post(route('internships.apply'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Detail - ${internship.title}`" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <!-- Navigasi Portal -->
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">List Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Generate CV</Link>
        </template>

        <!-- Bagian Header (Aksen Abu-abu ke Putih) -->
        <div class="bg-gradient-to-b from-[#F1F5F9] to-white pb-32 pt-12 relative z-10">
            <div class="mx-auto max-w-4xl px-6">
                <!-- Tombol Kembali -->
                <Link :href="route('lowongan')" class="inline-flex items-center text-sm font-semibold text-[#64748B] hover:text-[#2563EB] mb-8 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Daftar Lowongan
                </Link>

                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    <!-- Inisial Logo -->
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white border border-[#E2E8F0] shadow-sm font-black text-[#2563EB] text-3xl uppercase">
                        {{ internship.company_name ? internship.company_name.charAt(0) : 'C' }}
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-[#0F172A] tracking-tight">
                            {{ internship.title }}
                        </h1>
                        <p class="mt-2 text-lg font-medium text-[#2563EB]">{{ internship.company_name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Card Detail (Numpang di atas Header) -->
        <div class="mx-auto max-w-4xl px-6 pb-20 -mt-20 relative z-20">
            
            <!-- Flash Message -->
            <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-[#ECFDF5] text-[#047857] rounded-xl border border-[#A7F3D0] shadow-sm flex items-center">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-6 p-4 bg-[#FEF2F2] text-[#B91C1C] rounded-xl border border-[#FECACA] shadow-sm flex items-center">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-[#2563EB]/5 border border-[#E2E8F0] overflow-hidden">
                <!-- Info Highlights -->
                <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#E2E8F0] border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <div class="p-5 flex flex-col justify-center">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Tipe</span>
                        <span class="mt-1 font-bold text-[#0F172A]">{{ internship.work_type || 'Magang' }}</span>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Lokasi</span>
                        <span class="mt-1 font-bold text-[#0F172A]">{{ internship.location || '-' }}</span>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Durasi</span>
                        <span class="mt-1 font-bold text-[#0F172A]">{{ internship.duration || '-' }}</span>
                    </div>
                    <div class="p-5 flex flex-col justify-center">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">Tenggat Waktu</span>
                        <span class="mt-1 font-bold text-[#E11D48]">
                            {{ internship.deadline_at ? new Date(internship.deadline_at).toLocaleDateString("id-ID", { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
                        </span>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <!-- Deskripsi Umum -->
                    <div v-if="internship.description" class="mb-10">
                        <h3 class="text-xl font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Deskripsi Pekerjaan
                        </h3>
                        <p class="text-[#475467] leading-relaxed whitespace-pre-wrap">{{ internship.description }}</p>
                    </div>

                    <!-- Kualifikasi -->
                    <div class="mb-10">
                        <h3 class="text-xl font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Kualifikasi & Persyaratan
                        </h3>
                        <div class="prose max-w-none text-[#475467] leading-relaxed whitespace-pre-wrap">
                            {{ internship.requirements }}
                        </div>
                    </div>

                    <!-- Benefit -->
                    <div v-if="internship.benefits" class="mb-10">
                        <h3 class="text-xl font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Benefit Tambahan
                        </h3>
                        <p class="text-[#475467] leading-relaxed whitespace-pre-wrap">{{ internship.benefits }}</p>
                    </div>

                    <!-- Area Tombol -->
                    <div class="pt-6 border-t border-[#E2E8F0]">
                        <button
                            :disabled="props.hasApplied || form.processing"
                            :class="[
                                'flex w-full md:w-auto h-14 items-center justify-center rounded-xl px-10 text-base font-bold text-white transition-all shadow-md',
                                props.hasApplied
                                    ? 'bg-[#94A3B8] cursor-not-allowed shadow-none'
                                    : 'bg-[#2563EB] hover:bg-[#1d4ed8] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#2563EB]/30',
                            ]"
                            @click="apply"
                        >
                            <svg v-if="!form.processing && !props.hasApplied" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            <span v-if="form.processing">Memproses Lamaran...</span>
                            <span v-else>{{ props.hasApplied ? "Anda Sudah Melamar Posisi Ini" : "Lamar Pekerjaan Ini" }}</span>
                        </button>
                        
                        <p v-if="!$page.props.auth.user" class="mt-4 text-sm text-[#64748B] text-center md:text-left">
                            Anda akan diarahkan ke halaman login terlebih dahulu jika belum memiliki sesi aktif.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>