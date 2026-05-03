<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
</script>

<template>
    <Head title="Generate CV — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Daftar Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#2563EB]">Buat CV</Link>
        </template>

        <div class="bg-[#0F172A] relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute top-0 right-0 h-[40rem] w-[40rem] -translate-y-1/2 translate-x-1/3 rounded-full bg-[#2563EB] opacity-20 blur-[100px]"></div>
            
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-16 px-6 py-24 lg:flex-row lg:py-32 relative z-10">
                <div class="max-w-xl text-center lg:text-left animate-fade-in-up">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 backdrop-blur-sm">
                        <span class="text-xs font-bold text-[#10B981] tracking-widest uppercase">ATS-Friendly</span>
                    </div>

                    <h1 class="text-4xl font-extrabold leading-tight text-white lg:text-5xl">
                        Buat CV Profesional<br />
                        <span class="text-[#2563EB]">Hanya dalam Hitungan Detik</span>
                    </h1>

                    <p class="mt-6 text-base text-[#94A3B8] leading-relaxed">
                        Tinggalkan cara lama membuat CV secara manual. Cukup lengkapi profil Anda di SIKARA, 
                        dan sistem cerdas kami akan mengekstrak data Anda menjadi format CV berstandar industri (ATS) yang siap dikirim.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                        <Link :href="$page.props.auth.user ? '#' : route('login', { role: 'mahasiswa' })" class="rounded-xl bg-[#2563EB] px-8 py-3.5 text-sm font-bold text-white transition-all hover:bg-[#1d4ed8] hover:shadow-lg hover:shadow-[#2563EB]/30">
                            Buat CV Saya Sekarang
                        </Link>
                    </div>
                </div>

                <!-- Interactive Document Mockup -->
                <div class="relative w-full max-w-md animate-fade-in-up" style="animation-delay: 200ms;">
                    <div class="absolute -inset-1 rounded-2xl bg-gradient-to-tr from-[#2563EB] to-[#10B981] opacity-30 blur-lg"></div>
                    <div class="relative rounded-2xl bg-white p-8 shadow-2xl">
                        <div class="flex items-center gap-4 border-b border-[#F1F5F9] pb-6">
                            <div class="h-16 w-16 rounded-full bg-[#E2E8F0]"></div>
                            <div>
                                <div class="h-4 w-32 rounded bg-[#0F172A]"></div>
                                <div class="mt-2 h-3 w-24 rounded bg-[#64748B]"></div>
                            </div>
                        </div>
                        <div class="mt-6 space-y-4">
                            <div>
                                <div class="h-3 w-20 rounded bg-[#2563EB] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                                <div class="mt-2 h-2 w-5/6 rounded bg-[#E2E8F0]"></div>
                                <div class="mt-2 h-2 w-4/6 rounded bg-[#E2E8F0]"></div>
                            </div>
                            <div class="pt-4">
                                <div class="h-3 w-24 rounded bg-[#2563EB] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                                <div class="mt-2 h-2 w-full rounded bg-[#E2E8F0]"></div>
                            </div>
                        </div>
                        <div class="absolute -right-6 bottom-12 rounded-xl bg-[#10B981] px-4 py-3 text-sm font-bold text-white shadow-xl flex items-center gap-2">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export PDF
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
