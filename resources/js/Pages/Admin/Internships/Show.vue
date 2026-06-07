<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    internship: Object,
});

// ─── Status Config ──────────────────────────────────────────────────
const statusConfig = {
    pending:  { label: 'Menunggu Review',    bg: 'bg-amber-100',   text: 'text-amber-700',   dot: 'bg-amber-500',   borderBar: 'border-amber-400',   gradient: 'from-amber-50 to-orange-50' },
    approved: { label: 'Disetujui & Tayang', bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500', borderBar: 'border-emerald-400', gradient: 'from-emerald-50 to-teal-50' },
    rejected: { label: 'Ditolak',            bg: 'bg-red-100',     text: 'text-red-700',     dot: 'bg-red-500',     borderBar: 'border-red-400',     gradient: 'from-red-50 to-rose-50' },
    closed:   { label: 'Ditutup (Takedown)', bg: 'bg-slate-100',   text: 'text-slate-600',   dot: 'bg-slate-400',   borderBar: 'border-slate-400',   gradient: 'from-slate-50 to-gray-50' },
};

const currentStatus = () => statusConfig[props.internship.moderation_status] ?? statusConfig.pending;

// ─── Format helpers ─────────────────────────────────────────────────
const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};
const formatDateTime = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
const isExpired = (d) => d && new Date(d) < new Date();

// ─── Approve ────────────────────────────────────────────────────────
const approveForm = useForm({});
const handleApprove = () => {
    approveForm.patch(route('admin.internships.approve', props.internship.id), {
        preserveScroll: true,
    });
};

// ─── Reject / Takedown Modal ─────────────────────────────────────────
const showModal = ref(false);
const modalMode = ref('reject'); // 'reject' | 'takedown'
const rejectForm = useForm({ rejection_reason: '' });

const openModal = (mode) => {
    modalMode.value = mode;
    rejectForm.reset();
    showModal.value = true;
};
const closeModal = () => { showModal.value = false; };

const submitModal = () => {
    const routeName = modalMode.value === 'takedown'
        ? 'admin.internships.takedown'
        : 'admin.internships.reject';

    rejectForm.patch(route(routeName, props.internship.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>
    <Head :title="`Review: ${internship.title}`" />
    <AdminLayout :title="`Review Lowongan`">

        <!-- Breadcrumb -->
        <div class="mb-5 flex items-center gap-2 text-xs text-slate-500 animate-fade-in-up">
            <Link :href="route('admin.internships.index')" class="hover:text-slate-800 font-semibold transition">Moderasi Lowongan</Link>
            <svg class="h-3 w-3 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-700 font-bold truncate max-w-xs">{{ internship.title }}</span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- ══ LEFT COLUMN — Main Detail ══════════════════════════════ -->
            <div class="xl:col-span-2 space-y-5">

                <!-- Status Banner -->
                <div
                    class="rounded-2xl border-l-4 p-4 flex items-center gap-4 animate-fade-in-up"
                    :class="[`bg-gradient-to-r ${currentStatus().gradient}`, currentStatus().borderBar]"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="currentStatus().bg">
                        <!-- Pending Icon -->
                        <svg v-if="internship.moderation_status === 'pending'" class="h-5 w-5" :class="currentStatus().text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <!-- Approved Icon -->
                        <svg v-else-if="internship.moderation_status === 'approved'" class="h-5 w-5" :class="currentStatus().text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <!-- Rejected Icon -->
                        <svg v-else class="h-5 w-5" :class="currentStatus().text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-black" :class="currentStatus().text">{{ currentStatus().label }}</p>
                        <p v-if="internship.moderated_at" class="text-xs text-slate-500 mt-0.5">
                            Dimoderasi oleh <strong>{{ internship.moderator?.name ?? 'Admin' }}</strong> pada {{ formatDateTime(internship.moderated_at) }}
                        </p>
                        <p v-else class="text-xs text-slate-500 mt-0.5">Menunggu tindakan moderasi dari Admin</p>
                    </div>
                </div>

                <!-- Rejection/Takedown Reason Card -->
                <div v-if="(internship.moderation_status === 'rejected' || internship.moderation_status === 'closed') && internship.rejection_reason"
                    :class="internship.moderation_status === 'closed' ? 'rounded-2xl border border-slate-200 bg-slate-50 p-5 animate-fade-in-up' : 'rounded-2xl border border-red-200 bg-red-50 p-5 animate-fade-in-up'"
                >
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 shrink-0 mt-0.5" :class="internship.moderation_status === 'closed' ? 'text-slate-500' : 'text-red-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <div>
                            <p class="text-sm font-black mb-1" :class="internship.moderation_status === 'closed' ? 'text-slate-700' : 'text-red-700'">
                                {{ internship.moderation_status === 'closed' ? 'Alasan Pencabutan (Takedown)' : 'Alasan Penolakan' }}
                            </p>
                            <p class="text-sm leading-relaxed" :class="internship.moderation_status === 'closed' ? 'text-slate-800' : 'text-red-800'">{{ internship.rejection_reason }}</p>
                        </div>
                    </div>
                </div>

                <!-- Main Info Card -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-100">
                    <!-- Card Header -->
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                            <img v-if="internship.company_logo" :src="internship.company_logo" :alt="internship.company_name" class="h-full w-full object-cover" @error="$event.target.style.display='none'" />
                            <span v-else class="text-sm font-black text-slate-500">{{ (internship.title || '?').charAt(0) }}</span>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-slate-900">{{ internship.title }}</h2>
                            <p class="text-xs text-slate-500">{{ internship.company_name }}</p>
                        </div>
                        <span class="ml-auto inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                            :class="[statusConfig[internship.moderation_status]?.bg, statusConfig[internship.moderation_status]?.text]">
                            <span class="h-1.5 w-1.5 rounded-full" :class="statusConfig[internship.moderation_status]?.dot"></span>
                            {{ statusConfig[internship.moderation_status]?.label }}
                        </span>
                    </div>

                    <!-- Info Grid -->
                    <div class="px-6 py-5">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Lokasi</p>
                                <p class="text-sm font-bold text-slate-800">{{ internship.location || '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tipe Kerja</p>
                                <p class="text-sm font-bold text-slate-800">{{ internship.work_type || '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Durasi</p>
                                <p class="text-sm font-bold text-slate-800">{{ internship.duration || '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pendidikan</p>
                                <p class="text-sm font-bold text-slate-800">{{ internship.education_level || 'Semua Jenjang' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Gaji / Range</p>
                                <p class="text-sm font-bold text-slate-800">{{ internship.salary_range || internship.salary || 'Tidak disebutkan' }}</p>
                            </div>
                            <div class="rounded-xl p-3" :class="isExpired(internship.deadline_at) ? 'bg-red-50 border border-red-200' : 'bg-slate-50'">
                                <p class="text-[10px] font-bold uppercase tracking-wider mb-1" :class="isExpired(internship.deadline_at) ? 'text-red-400' : 'text-slate-400'">Deadline</p>
                                <p class="text-sm font-bold" :class="isExpired(internship.deadline_at) ? 'text-red-700' : 'text-slate-800'">
                                    {{ formatDate(internship.deadline_at) }}
                                    <span v-if="isExpired(internship.deadline_at)" class="text-[10px] ml-1 font-normal text-red-500">(Lewat)</span>
                                </p>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-5">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Deskripsi Lowongan</h3>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ internship.description || '-' }}</p>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="mb-5">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Kualifikasi & Persyaratan</h3>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ internship.requirements || '-' }}</p>
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div v-if="internship.benefits">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Keuntungan / Benefit</h3>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ internship.benefits }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-150">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                            <svg class="h-4 w-4 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Garis Waktu Alur Lowongan
                        </h3>
                    </div>
                    <div class="px-6 py-5">
                        <div class="relative border-l-2 border-slate-200 ml-4 space-y-8 py-2">
                            
                            <!-- Titik 1: Lowongan Dibuat -->
                            <div class="relative pl-6">
                                <div class="absolute -left-2.5 top-1 h-4 w-4 rounded-full bg-blue-500 border-4 border-white shadow-sm"></div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-850">Lowongan Dibuat</h4>
                                    <p class="text-xs text-slate-500 mt-1">Dibuat oleh mitra perusahaan <strong>{{ internship.company_name }}</strong></p>
                                    <span class="inline-block text-[10px] font-semibold text-slate-500 mt-2 bg-slate-100 px-2.5 py-1 rounded-lg">
                                        {{ formatDateTime(internship.created_at) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Titik 2: Proses Moderasi -->
                            <div class="relative pl-6">
                                <!-- Status Approved -->
                                <template v-if="internship.moderation_status === 'approved'">
                                    <div class="absolute -left-2.5 top-1 h-4 w-4 rounded-full bg-emerald-500 border-4 border-white shadow-sm"></div>
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-800">Disetujui & Diterbitkan</h4>
                                        <p class="text-xs text-slate-600 mt-1">Lolos tahap moderasi oleh Admin <strong>{{ internship.moderator?.name ?? 'Admin' }}</strong> dan saat ini sedang tayang di katalog lowongan.</p>
                                        <span class="inline-block text-[10px] font-semibold text-emerald-700 mt-2 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                            {{ formatDateTime(internship.moderated_at) }}
                                        </span>
                                    </div>
                                </template>
                                
                                <!-- Status Rejected -->
                                <template v-else-if="internship.moderation_status === 'rejected'">
                                    <div class="absolute -left-2.5 top-1 h-4 w-4 rounded-full bg-red-500 border-4 border-white shadow-sm"></div>
                                    <div>
                                        <h4 class="text-sm font-bold text-red-800">Ditolak / Dicabut dari Penayangan</h4>
                                        <p class="text-xs text-slate-600 mt-1">Ditolak/dicabut oleh Admin <strong>{{ internship.moderator?.name ?? 'Admin' }}</strong>.</p>
                                        <div v-if="internship.rejection_reason" class="mt-2 text-xs text-red-700 bg-red-50 p-3 rounded-xl border border-red-100 whitespace-pre-wrap">
                                            <strong>Alasan:</strong> {{ internship.rejection_reason }}
                                        </div>
                                        <span class="inline-block text-[10px] font-semibold text-red-700 mt-2 bg-red-50 px-2.5 py-1 rounded-lg">
                                            {{ formatDateTime(internship.moderated_at) }}
                                        </span>
                                    </div>
                                </template>

                                <!-- Status Pending -->
                                <template v-else>
                                    <div class="absolute -left-2.5 top-1 h-4 w-4 rounded-full bg-amber-500 border-4 border-white shadow-sm animate-pulse"></div>
                                    <div>
                                        <h4 class="text-sm font-bold text-amber-800">Menunggu Review Admin</h4>
                                        <p class="text-xs text-slate-500 mt-1">Masuk ke antrean moderasi lowongan. Menunggu keputusan persetujuan atau penolakan oleh Administrator.</p>
                                        <span class="inline-block text-[10px] font-semibold text-amber-700 mt-2 bg-amber-50 px-2.5 py-1 rounded-lg">
                                            Dalam Antrean
                                        </span>
                                    </div>
                                </template>
                            </div>

                            <!-- Titik 3: Batas Waktu Pendaftaran -->
                            <div class="relative pl-6">
                                <div class="absolute -left-2.5 top-1 h-4 w-4 rounded-full border-4 border-white shadow-sm"
                                    :class="isExpired(internship.deadline_at) ? 'bg-red-400' : 'bg-slate-400'"></div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Batas Waktu Pelamaran (Deadline)</h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        <span v-if="isExpired(internship.deadline_at)" class="text-red-600 font-bold">Masa pendaftaran mahasiswa telah berakhir.</span>
                                        <span v-else>Batas akhir bagi mahasiswa untuk mengirimkan lamaran ke lowongan ini.</span>
                                    </p>
                                    <span class="inline-block text-[10px] font-semibold mt-2 px-2.5 py-1 rounded-lg"
                                        :class="isExpired(internship.deadline_at) ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600'">
                                        {{ formatDate(internship.deadline_at) }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- ══ RIGHT COLUMN — Sidebar Aksi ════════════════════════════ -->
            <div class="space-y-5">

                <!-- Sticky Action Panel -->
                <div class="sticky top-24">

                    <!-- Company Info Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-4 animate-fade-in-up delay-100">
                        <div class="border-b border-slate-100 px-5 py-3.5">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Informasi Perusahaan</h3>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200">
                                    <img v-if="internship.company_logo" :src="internship.company_logo" class="h-full w-full object-cover" />
                                    <span v-else class="text-sm font-black text-slate-500">{{ (internship.company_name || '?').charAt(0) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">{{ internship.company_name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ internship.company?.email ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-50 space-y-1.5">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Kuota Peserta</span>
                                    <span class="font-bold text-slate-800">{{ internship.quota }} orang</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Total Pelamar</span>
                                    <span class="font-bold text-slate-800">{{ internship.applications?.length ?? 0 }} orang</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-500">Dibuat</span>
                                    <span class="font-bold text-slate-800">{{ formatDate(internship.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Moderation Action Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">
                        <div class="border-b border-slate-100 px-5 py-3.5">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Tindakan Moderasi</h3>
                        </div>
                        <div class="px-5 py-4 space-y-3">

                            <!-- Jika PENDING -->
                            <template v-if="internship.moderation_status === 'pending'">
                                <p class="text-xs text-slate-500 mb-1">Lowongan ini menunggu keputusan moderasi Anda.</p>

                                <!-- Approve Button -->
                                <button
                                    @click="handleApprove"
                                    :disabled="approveForm.processing"
                                    class="w-full flex items-center justify-center gap-2.5 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-black text-white shadow-sm hover:bg-emerald-600 transition disabled:opacity-60"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                    {{ approveForm.processing ? 'Memproses...' : 'Setujui & Tayangkan' }}
                                </button>

                                <!-- Reject Button -->
                                <button
                                    @click="openModal('reject')"
                                    class="w-full flex items-center justify-center gap-2.5 rounded-xl border-2 border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-600 hover:bg-red-100 hover:border-red-300 transition"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Tolak Lowongan
                                </button>
                            </template>

                            <!-- Jika APPROVED -->
                            <template v-else-if="internship.moderation_status === 'approved'">
                                <div class="flex items-start gap-2 rounded-xl bg-emerald-50 p-3 mb-2">
                                    <svg class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    <p class="text-xs text-emerald-700 font-medium">Lowongan ini sedang tayang dan dapat dilihat oleh mahasiswa.</p>
                                </div>
                                <button
                                    @click="openModal('takedown')"
                                    class="w-full flex items-center justify-center gap-2.5 rounded-xl border-2 border-orange-200 bg-orange-50 px-4 py-3 text-sm font-black text-orange-600 hover:bg-orange-100 hover:border-orange-300 transition"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 19.36"/><path d="M2 2l20 20"/></svg>
                                    Takedown Lowongan
                                </button>
                            </template>

                            <!-- Jika REJECTED -->
                            <template v-else-if="internship.moderation_status === 'rejected'">
                                <div class="flex items-start gap-2 rounded-xl bg-red-50 p-3">
                                    <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                    <p class="text-xs text-red-700 font-medium">Lowongan ini sudah ditolak. Perusahaan bisa mengedit dan mengajukan ulang.</p>
                                </div>
                            </template>

                            <!-- Jika CLOSED (takedown) -->
                            <template v-else-if="internship.moderation_status === 'closed'">
                                <div class="flex items-start gap-2 rounded-xl bg-slate-100 p-3">
                                    <svg class="h-4 w-4 text-slate-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                    <p class="text-xs text-slate-700 font-medium">Lowongan ini telah dicabut (takedown). Perusahaan tidak bisa mengedit — hanya bisa melihat alasan pencabutan.</p>
                                </div>
                            </template>

                            <!-- Back Button -->
                            <Link
                                :href="route('admin.internships.index')"
                                class="w-full flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                Kembali ke Daftar
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ═══ Modal Reject / Takedown ═══════════════════════════════════ -->
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
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @click.self="closeModal"
                >
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="modalMode === 'takedown' ? 'bg-orange-100' : 'bg-red-100'">
                                        <svg class="h-5 w-5" :class="modalMode === 'takedown' ? 'text-orange-600' : 'text-red-600'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path v-if="modalMode === 'takedown'" d="M18.36 6.64A9 9 0 1 1 5.64 19.36"/><path v-if="modalMode === 'takedown'" d="M2 2l20 20"/>
                                            <path v-else d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">
                                            {{ modalMode === 'takedown' ? 'Takedown Lowongan' : 'Tolak Lowongan' }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[260px]">{{ internship.title }}</p>
                                    </div>
                                </div>
                                <button @click="closeModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5">
                                <div class="mb-4 flex items-start gap-3 rounded-xl p-3.5 border" :class="modalMode === 'takedown' ? 'bg-orange-50 border-orange-200' : 'bg-red-50 border-red-200'">
                                    <svg class="h-4 w-4 mt-0.5 shrink-0" :class="modalMode === 'takedown' ? 'text-orange-500' : 'text-red-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        <span v-if="modalMode === 'takedown'">
                                            Lowongan akan <strong>dicabut dari penayangan</strong> dan tidak lagi bisa dilihat mahasiswa. Status berubah menjadi <strong>Ditutup</strong> — perusahaan tidak bisa mengedit.
                                        </span>
                                        <span v-else>
                                            Lowongan akan <strong>ditolak</strong>. Perusahaan perlu membuat lowongan baru untuk mengajukan kembali.
                                        </span>
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        {{ modalMode === 'takedown' ? 'Alasan Pencabutan' : 'Alasan Penolakan' }}
                                        <span class="text-red-500 ml-0.5">*</span>
                                    </label>
                                    <textarea
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        placeholder="Jelaskan alasan secara spesifik agar perusahaan memahaminya..."
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-100 transition resize-none"
                                        :class="rejectForm.errors.rejection_reason ? 'border-red-300' : ''"
                                    ></textarea>
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-[11px] text-red-600">{{ rejectForm.errors.rejection_reason }}</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="closeModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                                <button
                                    @click="submitModal"
                                    :disabled="rejectForm.processing || !rejectForm.rejection_reason.trim()"
                                    class="rounded-xl px-5 py-2 text-sm font-bold text-white shadow-sm transition disabled:opacity-60"
                                    :class="modalMode === 'takedown' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-red-500 hover:bg-red-600'"
                                >
                                    <span v-if="rejectForm.processing">Memproses...</span>
                                    <span v-else>{{ modalMode === 'takedown' ? 'Ya, Cabut Lowongan' : 'Ya, Tolak Lowongan' }}</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>
