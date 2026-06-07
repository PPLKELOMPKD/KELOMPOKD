<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';

const props = defineProps({
    events: Array,
});

const form = useForm({});

const deleteEvent = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus event ini?')) {
        form.delete(route('perusahaan.events.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};

const moderationConfig = {
    pending:  {
        label: 'Menunggu Persetujuan Admin',
        bg: 'bg-amber-50',
        text: 'text-amber-700',
        ring: 'ring-amber-200',
        dot: 'bg-amber-500',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    approved: {
        label: 'Disetujui & Tayang',
        bg: 'bg-emerald-50',
        text: 'text-emerald-700',
        ring: 'ring-emerald-200',
        dot: 'bg-emerald-500',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    rejected: {
        label: 'Ditolak Admin',
        bg: 'bg-red-50',
        text: 'text-red-700',
        ring: 'ring-red-200',
        dot: 'bg-red-500',
        icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};
</script>

<template>
    <Head title="Manajemen Event" />

    <SikaraLayout title="Manajemen Event" subtitle="Kelola acara dan pelatihan yang diselenggarakan oleh perusahaan Anda.">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#101828]">Daftar Event</h2>
                <p class="mt-1 text-sm text-[#475467]">Berikut adalah semua event yang Anda ajukan.</p>
            </div>
            <Link :href="route('perusahaan.events.create')" class="inline-flex items-center justify-center rounded-xl bg-[#10B981] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#059669] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981] transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Event Baru
            </Link>
        </div>

        <!-- Info Box: Alur Persetujuan -->
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3">
            <svg class="h-5 w-5 shrink-0 text-blue-500 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-blue-800">Alur Persetujuan Event</p>
                <p class="text-xs text-blue-600 mt-0.5">Event yang Anda buat akan masuk ke status <strong>Menunggu Persetujuan</strong> terlebih dahulu. Admin akan meninjau dan memutuskan apakah event disetujui untuk ditampilkan kepada mahasiswa atau ditolak.</p>
            </div>
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
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Judul Event</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Tanggal & Waktu</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-[#475467] uppercase tracking-wider">Tipe</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-[#475467] uppercase tracking-wider">Kuota</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-[#475467] uppercase tracking-wider">Status Moderasi</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-semibold text-[#475467] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="event in events" :key="event.id" class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                <div class="font-semibold text-[#101828]">{{ event.title }}</div>
                                <div class="text-sm text-[#667085] truncate max-w-[200px]">{{ event.location }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467]">
                                <div class="font-medium">{{ formatDate(event.date) }}</div>
                                <div class="text-xs text-[#667085]">{{ formatTime(event.start_time) }} - {{ formatTime(event.end_time) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467]">
                                <span class="capitalize">{{ event.type }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-[#475467] text-center font-medium">{{ event.max_participants }}</td>

                            <!-- Status Moderasi -->
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold ring-1"
                                        :class="[
                                            moderationConfig[event.moderation_status]?.bg,
                                            moderationConfig[event.moderation_status]?.text,
                                            moderationConfig[event.moderation_status]?.ring,
                                        ]"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="moderationConfig[event.moderation_status]?.dot"></span>
                                        {{ moderationConfig[event.moderation_status]?.label ?? event.moderation_status }}
                                    </span>

                                    <!-- Alasan Penolakan (jika rejected) -->
                                    <div v-if="event.moderation_status === 'rejected' && event.rejection_reason"
                                         class="mt-1 max-w-[200px] rounded-lg bg-red-50 border border-red-200 px-2.5 py-1.5 text-left">
                                        <p class="text-[10px] font-bold text-red-700 mb-0.5">Alasan Penolakan:</p>
                                        <p class="text-[10px] text-red-600 leading-relaxed">{{ event.rejection_reason }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <!-- Edit hanya bisa jika pending atau rejected -->
                                    <Link
                                        v-if="event.moderation_status !== 'approved'"
                                        :href="route('perusahaan.events.edit', event.id)"
                                        class="rounded-lg text-[#2563EB] hover:text-blue-900 p-1 hover:bg-blue-50 transition-colors"
                                        title="Edit Event"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </Link>
                                    <!-- Edit event approved: re-submit untuk approval ulang -->
                                    <Link
                                        v-else
                                        :href="route('perusahaan.events.edit', event.id)"
                                        class="rounded-lg text-amber-600 hover:text-amber-800 p-1 hover:bg-amber-50 transition-colors"
                                        title="Edit (akan memerlukan persetujuan ulang)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </Link>

                                    <button
                                        @click="deleteEvent(event.id)"
                                        class="rounded-lg text-red-600 hover:text-red-900 p-1 hover:bg-red-50 transition-colors"
                                        title="Hapus Event"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="events && events.length === 0">
                            <td colspan="6" class="py-12 text-center text-[#667085] text-sm">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                    </svg>
                                    Belum ada data event yang ditambahkan.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SikaraLayout>
</template>
