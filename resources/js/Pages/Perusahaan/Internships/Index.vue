<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';

const props = defineProps({
    internships: Array,
});

const form = useForm({});

const deleteInternship = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus lowongan ini? Jika ada pelamar, status akan diubah menjadi Ditutup.')) {
        form.delete(route('perusahaan.internships.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Manajemen Lowongan Magang" />

    <SikaraLayout title="Manajemen Lowongan Magang" subtitle="Kelola daftar lowongan magang perusahaan Anda dengan mudah.">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#101828]">Daftar Lowongan</h2>
                <p class="mt-1 text-sm text-[#475467]">Berikut adalah semua lowongan magang yang pernah Anda posting.</p>
            </div>
            <Link :href="route('perusahaan.internships.create')" class="inline-flex items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#059669] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981] transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Lowongan Baru
            </Link>
        </div>

        <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl bg-green-50 p-4 text-sm font-medium text-green-800 border border-green-200">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="mb-4 rounded-xl bg-red-50 p-4 text-sm font-medium text-red-800 border border-red-200">
            {{ $page.props.flash.error }}
        </div>

        <div class="rounded-[16px] border border-[#eaecf0] bg-white shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#f9fafb]">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Posisi / Perusahaan</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Batas Waktu</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-[#475467] uppercase tracking-wider">Kuota</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-[#475467] uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-semibold text-[#475467] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="internship in internships" :key="internship.id" class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="font-semibold text-[#101828]">{{ internship.title }}</div>
                                <div class="text-sm text-[#667085]">{{ internship.company_name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467]">{{ internship.location }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467]">{{ formatDate(internship.deadline_at) }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467] text-center font-medium">{{ internship.quota }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                <span v-if="internship.is_published" class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                <span v-else class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Ditutup</span>
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <Link :href="route('perusahaan.internships.edit', internship.id)" class="rounded-lg text-[#2563EB] hover:text-blue-900 p-1 hover:bg-blue-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </Link>
                                    <button @click="deleteInternship(internship.id)" class="rounded-lg text-red-600 hover:text-red-900 p-1 hover:bg-red-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="internships.length === 0">
                            <td colspan="6" class="py-12 text-center text-[#667085] text-sm">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Belum ada data lowongan magang yang ditambahkan.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SikaraLayout>
</template>
