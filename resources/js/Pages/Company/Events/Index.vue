<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Kelola Event" />
    <SikaraLayout title="Kelola Event" subtitle="Manajemen acara dan pelatihan yang diselenggarakan perusahaan.">
        <div class="mt-8 rounded-2xl border border-[#eaecf0] bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 mb-6">
                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-[#101828]">Halaman Kelola Event</h2>
            <p class="mt-2 text-[#667085] max-w-md mx-auto">Fitur ini masih dalam tahap pengembangan. Nantinya Anda dapat membuat, mengubah, dan mengelola peserta event di sini.</p>
        </div>
    </SikaraLayout>
</template>
