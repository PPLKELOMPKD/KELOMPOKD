<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    internships:  Object,
    statusFilter: String,
    search:       String,
    counts:       Object,
});

// ─── Search ────────────────────────────────────────────────────────
const searchQuery = ref(props.search || '');
let searchTimeout = null;

watch(searchQuery, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.internships.index'), {
            status: props.statusFilter,
            search: val,
        }, { preserveState: true, replace: true });
    }, 400);
});

// ─── Filter Tabs ────────────────────────────────────────────────────
const tabs = computed(() => [
    { key: 'all',      label: 'Semua',    count: props.counts?.all,      color: 'slate' },
    { key: 'pending',  label: 'Pending',  count: props.counts?.pending,  color: 'amber' },
    { key: 'approved', label: 'Disetujui', count: props.counts?.approved, color: 'emerald' },
    { key: 'rejected', label: 'Ditolak',  count: props.counts?.rejected, color: 'red' },
    { key: 'closed',   label: 'Ditutup',  count: props.counts?.closed,   color: 'slate' },
]);

const changeFilter = (key) => {
    router.get(route('admin.internships.index'), {
        status: key,
        search: searchQuery.value,
    }, { preserveState: false });
};

// ─── Status Style ────────────────────────────────────────────────────
const statusConfig = {
    pending:  { label: 'Menunggu Review', bg: 'bg-amber-100',   text: 'text-amber-700',   dot: 'bg-amber-500' },
    approved: { label: 'Disetujui',       bg: 'bg-emerald-100', text: 'text-emerald-700', dot: 'bg-emerald-500' },
    rejected: { label: 'Ditolak',         bg: 'bg-red-100',     text: 'text-red-700',     dot: 'bg-red-500' },
    closed:   { label: 'Ditutup',         bg: 'bg-slate-100',   text: 'text-slate-600',   dot: 'bg-slate-400' },
};

const tabConfig = {
    all:      { activeClass: 'border-slate-900 text-slate-900 bg-slate-50',       badgeClass: 'bg-slate-200 text-slate-700' },
    pending:  { activeClass: 'border-amber-500 text-amber-700 bg-amber-50',       badgeClass: 'bg-amber-200 text-amber-800' },
    approved: { activeClass: 'border-emerald-500 text-emerald-700 bg-emerald-50', badgeClass: 'bg-emerald-200 text-emerald-800' },
    rejected: { activeClass: 'border-red-500 text-red-700 bg-red-50',             badgeClass: 'bg-red-200 text-red-800' },
    closed:   { activeClass: 'border-slate-500 text-slate-700 bg-slate-50',       badgeClass: 'bg-slate-200 text-slate-700' },
};

const activeTab = computed(() => props.statusFilter || 'all');

// ─── Inline Quick Approve ────────────────────────────────────────────
const approveForm = useForm({});
const quickApprove = (id) => {
    approveForm.patch(route('admin.internships.approve', id), {
        preserveScroll: true,
        onSuccess: () => {},
    });
};

// ─── Inline Quick Reject Modal ───────────────────────────────────────
const showRejectModal = ref(false);
const selectedInternship = ref(null);
const rejectForm = useForm({ rejection_reason: '' });

const openRejectModal = (internship) => {
    selectedInternship.value = internship;
    rejectForm.reset();
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedInternship.value = null;
};

const submitReject = () => {
    if (!selectedInternship.value) return;

    // Jika lowongan sedang 'approved' → aksi adalah TAKEDOWN (status → closed)
    // Selain itu → aksi adalah REJECT (status → rejected)
    const isTakedown = selectedInternship.value.moderation_status === 'approved';
    const routeName  = isTakedown
        ? 'admin.internships.takedown'
        : 'admin.internships.reject';

    rejectForm.patch(route(routeName, selectedInternship.value.id), {
        preserveScroll: true,
        onSuccess: () => closeRejectModal(),
    });
};

// ─── Flash ───────────────────────────────────────────────────────────
const flash = computed(() => {
    const p = window?.$page?.props ?? {};
    return p.flash ?? {};
});

// Format tanggal
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Check apakah deadline sudah lewat
const isExpired = (dateStr) => {
    if (!dateStr) return false;
    return new Date(dateStr) < new Date();
};
</script>

<template>
    <Head title="Moderasi Lowongan" />
    <AdminLayout title="Moderasi Lowongan">

        <!-- Header Banner -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] p-6 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-6 -top-6 h-40 w-40 rounded-full bg-purple-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/2 -bottom-8 h-32 w-32 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-500/20 ring-1 ring-purple-400/30">
                        <svg class="h-6 w-6 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Moderasi Lowongan</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Tinjau, setujui, atau tolak lowongan dari perusahaan mitra</p>
                    </div>
                </div>
                <!-- Summary Badges -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex flex-col items-center px-4 py-2 bg-amber-500/20 rounded-xl ring-1 ring-amber-400/30">
                        <span class="text-xl font-black text-amber-300">{{ counts?.pending ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-amber-400/80 uppercase tracking-wider">Pending</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30">
                        <span class="text-xl font-black text-emerald-300">{{ counts?.approved ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-emerald-400/80 uppercase tracking-wider">Approved</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-red-500/20 rounded-xl ring-1 ring-red-400/30">
                        <span class="text-xl font-black text-red-300">{{ counts?.rejected ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-red-400/80 uppercase tracking-wider">Ditolak</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-slate-500/20 rounded-xl ring-1 ring-slate-400/30">
                        <span class="text-xl font-black text-slate-300">{{ counts?.closed ?? 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400/80 uppercase tracking-wider">Ditutup</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-5 flex flex-col sm:flex-row sm:items-center gap-4 animate-fade-in-up delay-100">
            <!-- Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-white border border-slate-200 p-1 shadow-sm overflow-x-auto">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="changeFilter(tab.key)"
                    class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="activeTab === tab.key
                        ? tabConfig[tab.key].activeClass + ' border'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    {{ tab.label }}
                    <span
                        v-if="tab.count !== undefined"
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-black"
                        :class="activeTab === tab.key ? tabConfig[tab.key].badgeClass : 'bg-slate-100 text-slate-500'"
                    >{{ tab.count }}</span>
                </button>
            </div>

            <!-- Search -->
            <div class="relative ml-auto flex-1 max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari judul atau perusahaan..."
                    class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100 transition"
                />
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">
            <!-- Empty State -->
            <div v-if="internships.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-600">Tidak ada lowongan</p>
                <p class="text-xs text-slate-400 mt-1">
                    <span v-if="searchQuery">Tidak ada hasil untuk "{{ searchQuery }}"</span>
                    <span v-else>Belum ada lowongan dengan status ini</span>
                </p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Lowongan</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Perusahaan</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tipe / Lokasi</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Deadline</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="item in internships.data"
                            :key="item.id"
                            class="group hover:bg-slate-50/60 transition-colors"
                        >
                            <!-- Lowongan -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Logo / Avatar -->
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                        <img
                                            v-if="item.company_logo"
                                            :src="item.company_logo"
                                            :alt="item.company_name"
                                            class="h-full w-full object-cover"
                                            @error="$event.target.style.display='none'"
                                        />
                                        <span v-else class="text-sm font-black text-slate-500">
                                            {{ (item.title || '?').charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 group-hover:text-purple-700 transition-colors leading-tight">{{ item.title }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">ID #{{ item.id }} · Dibuat {{ formatDate(item.created_at) }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Perusahaan -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-slate-700">{{ item.company_name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ item.company?.email ?? '-' }}</p>
                            </td>

                            <!-- Tipe / Lokasi -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600">{{ item.work_type }}</span>
                                    <span class="text-[11px] text-slate-500">{{ item.location }}</span>
                                </div>
                            </td>

                            <!-- Deadline -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold"
                                    :class="isExpired(item.deadline_at) ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600'"
                                >
                                    <span v-if="isExpired(item.deadline_at)" class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ formatDate(item.deadline_at) }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold w-fit"
                                        :class="[statusConfig[item.moderation_status]?.bg, statusConfig[item.moderation_status]?.text]"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusConfig[item.moderation_status]?.dot"></span>
                                        {{ statusConfig[item.moderation_status]?.label ?? item.moderation_status }}
                                    </span>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Lihat Detail -->
                                    <Link
                                        :href="route('admin.internships.show', item.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail
                                    </Link>

                                    <!-- Approve (hanya jika pending) -->
                                    <button
                                        v-if="item.moderation_status === 'pending'"
                                        @click="quickApprove(item.id)"
                                        :disabled="approveForm.processing"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm transition hover:bg-emerald-600 disabled:opacity-60"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                                        Setujui
                                    </button>

                                    <!-- Reject (hanya jika pending) -->
                                    <button
                                        v-if="item.moderation_status === 'pending'"
                                        @click="openRejectModal(item)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm transition hover:bg-red-600"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                        Tolak
                                    </button>

                                    <!-- Takedown (hanya jika approved) -->
                                    <button
                                        v-if="item.moderation_status === 'approved'"
                                        @click="openRejectModal(item)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-orange-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm transition hover:bg-orange-600"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64A9 9 0 1 1 5.64 19.36"/><path d="M2 2l20 20"/></svg>
                                        Takedown
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="internships.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-700">{{ internships.from }}–{{ internships.to }}</span>
                    dari <span class="font-bold text-slate-700">{{ internships.total }}</span> lowongan
                </p>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="link in internships.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        preserve-scroll
                        class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                        :class="link.active
                            ? 'bg-slate-900 text-white'
                            : link.url
                                ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700'
                                : 'text-slate-300 cursor-not-allowed'"
                    />
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
                    v-if="showRejectModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    @click.self="closeRejectModal"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <!-- Modal Card -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showRejectModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl"
                                        :class="selectedInternship?.moderation_status === 'approved' ? 'bg-orange-100' : 'bg-red-100'"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            :class="selectedInternship?.moderation_status === 'approved' ? 'text-orange-600' : 'text-red-600'"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        >
                                            <path v-if="selectedInternship?.moderation_status !== 'approved'" d="M18 6 6 18M6 6l12 12"/>
                                            <path v-else d="M18.36 6.64A9 9 0 1 1 5.64 19.36"/><path v-if="selectedInternship?.moderation_status === 'approved'" d="M2 2l20 20"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">
                                            {{ selectedInternship?.moderation_status === 'approved' ? 'Takedown Lowongan' : 'Tolak Lowongan' }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[240px]">{{ selectedInternship?.title }}</p>
                                    </div>
                                </div>
                                <button @click="closeRejectModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5">
                                <!-- Warning Box -->
                                <div
                                    class="mb-4 flex items-start gap-3 rounded-xl p-3.5"
                                    :class="selectedInternship?.moderation_status === 'approved' ? 'bg-orange-50 border border-orange-200' : 'bg-red-50 border border-red-200'"
                                >
                                    <svg class="h-4 w-4 mt-0.5 shrink-0" :class="selectedInternship?.moderation_status === 'approved' ? 'text-orange-500' : 'text-red-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        <span v-if="selectedInternship?.moderation_status === 'approved'">
                                            Lowongan ini akan <strong>dicabut dari penayangan</strong> dan tidak lagi bisa dilihat mahasiswa. Status akan berubah menjadi <strong>Ditutup</strong> (tidak bisa diedit perusahaan).
                                        </span>
                                        <span v-else>
                                            Lowongan ini akan <strong>ditolak</strong> dan tidak akan tayang. Perusahaan perlu membuat lowongan baru untuk mengajukan kembali.
                                        </span>
                                    </p>
                                </div>

                                <!-- Reason Field -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        {{ selectedInternship?.moderation_status === 'approved' ? 'Alasan Pencabutan' : 'Alasan Penolakan' }}
                                        <span class="text-red-500 ml-0.5">*</span>
                                    </label>
                                    <textarea
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        placeholder="Jelaskan alasan yang spesifik agar perusahaan bisa memahami dan memperbaiki lowongannya..."
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-100 transition resize-none"
                                        :class="rejectForm.errors.rejection_reason ? 'border-red-300 ring-red-100' : ''"
                                    ></textarea>
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-[11px] text-red-600">
                                        {{ rejectForm.errors.rejection_reason }}
                                    </p>
                                    <p class="mt-1 text-[10px] text-slate-400">Minimal 10 karakter. Alasan ini akan tersimpan dalam sistem moderasi.</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button
                                    @click="closeRejectModal"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 transition"
                                >
                                    Batal
                                </button>
                                <button
                                    @click="submitReject"
                                    :disabled="rejectForm.processing || !rejectForm.rejection_reason.trim()"
                                    class="rounded-xl px-5 py-2 text-sm font-bold text-white shadow-sm transition disabled:opacity-60"
                                    :class="selectedInternship?.moderation_status === 'approved' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-red-500 hover:bg-red-600'"
                                >
                                    <span v-if="rejectForm.processing" class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Memproses...
                                    </span>
                                    <span v-else>
                                        {{ selectedInternship?.moderation_status === 'approved' ? 'Ya, Cabut Lowongan' : 'Ya, Tolak Lowongan' }}
                                    </span>
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
