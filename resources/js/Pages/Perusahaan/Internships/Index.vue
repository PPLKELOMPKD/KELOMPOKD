<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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

// ─── Rejection Reason Modal ────────────────────────────────────────
const showRejectionModal = ref(false);
const selectedRejectedInternship = ref(null);

const openRejectionModal = (internship) => {
    selectedRejectedInternship.value = internship;
    showRejectionModal.value = true;
};

const closeRejectionModal = () => {
    showRejectionModal.value = false;
    selectedRejectedInternship.value = null;
};

// Apakah status adalah takedown (closed)?
const isClosed = (internship) => internship.moderation_status === 'closed';
const isRejected = (internship) => internship.moderation_status === 'rejected';
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
                                <!-- Pending -->
                                <span v-if="internship.moderation_status === 'pending'" class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Menunggu Review</span>
                                <!-- Approved / Aktif -->
                                <span v-else-if="internship.moderation_status === 'approved' && internship.is_published" class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                <!-- Rejected — bisa diedit & resubmit -->
                                <span v-else-if="internship.moderation_status === 'rejected'" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Ditolak
                                </span>
                                <!-- Closed / Ditutup (takedown) — tidak bisa edit -->
                                <span v-else-if="internship.moderation_status === 'closed'" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Ditutup
                                </span>
                                <span v-else class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Ditutup</span>
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">

                                    <!-- ❶ CLOSED (takedown): hanya tombol info alasan, tanpa edit/hapus -->
                                    <template v-if="isClosed(internship)">
                                        <button
                                            v-if="internship.rejection_reason"
                                            @click="openRejectionModal(internship)"
                                            title="Lihat alasan pencabutan dari admin"
                                            class="rounded-lg text-slate-500 hover:text-slate-800 p-1 hover:bg-slate-100 transition-colors"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        </button>
                                        <!-- Tidak ada tombol edit/hapus untuk closed -->
                                    </template>

                                    <!-- ❷ REJECTED: info alasan + tombol edit (resubmit) + hapus -->
                                    <template v-else-if="isRejected(internship)">
                                        <button
                                            v-if="internship.rejection_reason"
                                            @click="openRejectionModal(internship)"
                                            title="Lihat alasan penolakan dari admin"
                                            class="rounded-lg text-amber-600 hover:text-amber-800 p-1 hover:bg-amber-50 transition-colors"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        </button>
                                        <Link
                                            :href="route('perusahaan.internships.edit', internship.id)"
                                            title="Edit & Ajukan Ulang"
                                            class="rounded-lg text-[#2563EB] hover:text-blue-900 p-1 hover:bg-blue-50 transition-colors"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                        </Link>
                                        <button @click="deleteInternship(internship.id)" title="Hapus lowongan" class="rounded-lg text-red-600 hover:text-red-900 p-1 hover:bg-red-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </template>

                                    <!-- ❸ Lainnya (pending / approved): tombol normal -->
                                    <template v-else>
                                        <Link :href="route('perusahaan.internships.edit', internship.id)" class="rounded-lg text-[#2563EB] hover:text-blue-900 p-1 hover:bg-blue-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                        </Link>
                                        <button @click="deleteInternship(internship.id)" class="rounded-lg text-red-600 hover:text-red-900 p-1 hover:bg-red-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </template>

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

        <!-- ═══ Modal Alasan Penolakan ═══════════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showRejectionModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @click.self="closeRejectionModal"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <!-- Modal Card -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showRejectionModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100">
                                        <svg class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">
                                            {{ isClosed(selectedRejectedInternship) ? 'Alasan Pencabutan (Takedown)' : 'Alasan Penolakan' }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[260px]">{{ selectedRejectedInternship?.title }}</p>
                                    </div>
                                </div>
                                <button @click="closeRejectionModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5">
                                <!-- Info Box — beda warna untuk closed vs rejected -->
                                <div
                                    class="mb-4 flex items-start gap-3 rounded-xl p-3.5 border"
                                    :class="isClosed(selectedRejectedInternship) ? 'bg-slate-50 border-slate-200' : 'bg-red-50 border-red-200'"
                                >
                                    <svg class="h-4 w-4 mt-0.5 shrink-0" :class="isClosed(selectedRejectedInternship) ? 'text-slate-500' : 'text-red-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        <span v-if="isClosed(selectedRejectedInternship)">
                                            Lowongan Anda telah <strong>dicabut (takedown)</strong> oleh admin. Lowongan ini <strong>tidak dapat diedit</strong>. Jika ingin mengajukan kembali, silakan buat lowongan baru.
                                        </span>
                                        <span v-else>
                                            Lowongan Anda telah <strong>ditolak</strong> oleh admin. Silakan perbaiki dan ajukan ulang dengan menekan tombol Edit.
                                        </span>
                                    </p>
                                </div>

                                <!-- Rejection Reason -->
                                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-4">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan dari Admin</p>
                                    <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">{{ selectedRejectedInternship?.rejection_reason }}</p>
                                </div>

                                <!-- Moderation Date -->
                                <p v-if="selectedRejectedInternship?.moderated_at" class="mt-3 text-[11px] text-slate-400 text-right">
                                    Dimoderasi pada: {{ formatDate(selectedRejectedInternship.moderated_at) }}
                                </p>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button
                                    @click="closeRejectionModal"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 transition"
                                >
                                    Tutup
                                </button>
                                <!-- Tombol Edit hanya untuk rejected, bukan closed -->
                                <Link
                                    v-if="isRejected(selectedRejectedInternship)"
                                    :href="route('perusahaan.internships.edit', selectedRejectedInternship?.id)"
                                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 transition"
                                    @click="closeRejectionModal"
                                >
                                    Edit & Ajukan Ulang
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </SikaraLayout>
</template>
