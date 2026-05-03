<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Kelola Pelamar" />
    <SikaraLayout title="Kelola Pelamar" subtitle="Daftar pelamar yang melamar pada lowongan perusahaan Anda.">
        <div class="mt-8 rounded-2xl border border-[#eaecf0] bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-blue-500 mb-6">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-[#101828]">Halaman Kelola Pelamar</h2>
            <p class="mt-2 text-[#667085] max-w-md mx-auto">Fitur ini masih dalam tahap pengembangan. Nantinya Anda dapat meninjau, mewawancarai, dan menerima/menolak pelamar di sini.</p>
        </div>
    </SikaraLayout>
</template>
