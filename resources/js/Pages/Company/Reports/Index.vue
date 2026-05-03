<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Laporan & Analitik" />
    <SikaraLayout title="Laporan & Analitik" subtitle="Unduh laporan lengkap mengenai aktivitas rekrutmen perusahaan Anda.">
        <div class="mt-8 rounded-2xl border border-[#eaecf0] bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-purple-50 text-purple-500 mb-6">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-[#101828]">Halaman Laporan & Analitik</h2>
            <p class="mt-2 text-[#667085] max-w-md mx-auto">Fitur ini masih dalam tahap pengembangan. Nantinya Anda dapat mengekspor rekapitulasi data rekrutmen dan event ke PDF/Excel di sini.</p>
        </div>
    </SikaraLayout>
</template>
