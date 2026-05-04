<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    applicants: Array,
    pagination: Object,
    stats: Object,
    internships: Array,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const showModal = ref(false);
const modalAction = ref('');
const modalApplicant = ref(null);
const processing = ref(false);

const statusTabs = [
    { key: 'all', label: 'Semua', color: 'blue' },
    { key: 'menunggu ulasan', label: 'Menunggu Ulasan', color: 'blue' },
    { key: 'submitted', label: 'Submitted', color: 'blue' },
    { key: 'wawancara', label: 'Wawancara', color: 'purple' },
    { key: 'lolos', label: 'Lolos', color: 'green' },
    { key: 'tidak lolos', label: 'Tidak Lolos', color: 'red' },
];

const getColorClass = (color, type) => {
    const map = {
        blue:   { bg: 'bg-blue-50',    text: 'text-blue-600',    border: 'border-blue-200',    ring: 'ring-blue-500/20' },
        green:  { bg: 'bg-emerald-50',  text: 'text-emerald-600', border: 'border-emerald-200', ring: 'ring-emerald-500/20' },
        purple: { bg: 'bg-purple-50',   text: 'text-purple-600',  border: 'border-purple-200',  ring: 'ring-purple-500/20' },
        red:    { bg: 'bg-red-50',      text: 'text-red-600',     border: 'border-red-200',     ring: 'ring-red-500/20' },
        orange: { bg: 'bg-orange-50',   text: 'text-orange-600',  border: 'border-orange-200',  ring: 'ring-orange-500/20' },
    };
    return map[color]?.[type] || map.blue[type];
};

let searchTimeout = null;
const onSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
};

const setStatus = (status) => {
    statusFilter.value = status;
    applyFilters();
};

const applyFilters = () => {
    router.get(route('perusahaan.applicants.index'), {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const openModal = (applicant, action) => {
    modalApplicant.value = applicant;
    modalAction.value = action;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    modalApplicant.value = null;
    modalAction.value = '';
};

const confirmAction = () => {
    if (!modalApplicant.value) return;
    processing.value = true;
    router.patch(route('perusahaan.applicants.updateStatus', modalApplicant.value.id), {
        status: modalAction.value,
        redirect: 'index',
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            closeModal();
        },
    });
};

const setInterview = (applicant) => {
    openModal(applicant, 'wawancara');
};

const modalConfig = computed(() => {
    const configs = {
        lolos: {
            title: 'Terima Pelamar',
            message: `Apakah Anda yakin ingin menerima ${modalApplicant.value?.name} untuk posisi ${modalApplicant.value?.position}?`,
            icon: 'check',
            color: 'emerald',
            btnClass: 'bg-emerald-600 hover:bg-emerald-700',
            btnText: 'Ya, Terima',
        },
        'tidak lolos': {
            title: 'Tolak Pelamar',
            message: `Apakah Anda yakin ingin menolak ${modalApplicant.value?.name} untuk posisi ${modalApplicant.value?.position}?`,
            icon: 'x',
            color: 'red',
            btnClass: 'bg-red-600 hover:bg-red-700',
            btnText: 'Ya, Tolak',
        },
        wawancara: {
            title: 'Undang Wawancara',
            message: `Apakah Anda yakin ingin mengundang ${modalApplicant.value?.name} untuk wawancara posisi ${modalApplicant.value?.position}?`,
            icon: 'calendar',
            color: 'purple',
            btnClass: 'bg-purple-600 hover:bg-purple-700',
            btnText: 'Ya, Undang',
        },
    };
    return configs[modalAction.value] || configs.lolos;
});

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Kelola Pelamar" />
    <SikaraLayout title="Kelola Pelamar" subtitle="Kelola dan tinjau semua lamaran yang masuk pada lowongan perusahaan Anda.">
        <!-- Flash Message -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="$page.props.flash?.success" class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ $page.props.flash.success }}
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-6">
            <div class="rounded-2xl border border-[#eaecf0] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">Total Pelamar</p>
                <p class="mt-2 text-3xl font-bold text-[#101828]">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-[#eaecf0] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Menunggu</p>
                <p class="mt-2 text-3xl font-bold text-[#101828]">{{ stats.menunggu_ulasan + stats.submitted }}</p>
            </div>
            <div class="rounded-2xl border border-[#eaecf0] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600">Wawancara</p>
                <p class="mt-2 text-3xl font-bold text-[#101828]">{{ stats.wawancara }}</p>
            </div>
            <div class="rounded-2xl border border-[#eaecf0] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Diterima</p>
                <p class="mt-2 text-3xl font-bold text-[#101828]">{{ stats.lolos }}</p>
            </div>
            <div class="rounded-2xl border border-[#eaecf0] bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-600">Ditolak</p>
                <p class="mt-2 text-3xl font-bold text-[#101828]">{{ stats.tidak_lolos }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-[#eaecf0] bg-white shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#eaecf0]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Search -->
                    <div class="relative max-w-md w-full">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input v-model="search" @input="onSearch" type="text" placeholder="Cari nama atau email pelamar..." class="h-11 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder-[#98a2b3] outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-blue-500/20 transition-all" />
                    </div>
                    <!-- Status Tabs -->
                    <div class="flex flex-wrap gap-2">
                        <button v-for="tab in statusTabs" :key="tab.key" @click="setStatus(tab.key)" :class="[
                            'rounded-lg px-3.5 py-2 text-xs font-semibold transition-all duration-200',
                            statusFilter === tab.key
                                ? `${getColorClass(tab.color, 'bg')} ${getColorClass(tab.color, 'text')} ${getColorClass(tab.color, 'border')} border shadow-sm`
                                : 'bg-white text-[#667085] border border-[#eaecf0] hover:bg-[#f9fafb]'
                        ]">
                            {{ tab.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#667085]">
                    <thead class="border-b border-[#eaecf0] bg-[#f9fafb] text-[11px] font-semibold uppercase tracking-wider text-[#667085]">
                        <tr>
                            <th class="px-6 py-4">Nama Pelamar</th>
                            <th class="px-6 py-4">Posisi Dilamar</th>
                            <th class="px-6 py-4">Universitas & Jurusan</th>
                            <th class="px-6 py-4">Tgl. Daftar</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eaecf0]">
                        <tr v-for="a in applicants" :key="a.id" class="hover:bg-[#f9fafb] transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold text-sm" :class="[getColorClass(a.statusColor, 'bg'), getColorClass(a.statusColor, 'text')]">
                                        {{ a.initials }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#101828]">{{ a.name }}</p>
                                        <p class="text-xs text-[#667085]">{{ a.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[#344054] font-medium">{{ a.position }}</td>
                            <td class="px-6 py-4">
                                <p class="text-[#344054] font-medium">{{ a.university }}</p>
                                <p class="text-xs">{{ a.major }}</p>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ a.date }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border" :class="[getColorClass(a.statusColor, 'bg'), getColorClass(a.statusColor, 'text'), getColorClass(a.statusColor, 'border')]">
                                    {{ a.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs font-semibold">
                                    <button v-if="a.statusRaw !== 'lolos'" @click="openModal(a, 'lolos')" class="rounded-md bg-emerald-50 text-emerald-600 px-3 py-1.5 hover:bg-emerald-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">Terima</button>
                                    <button v-if="a.statusRaw !== 'tidak lolos'" @click="openModal(a, 'tidak lolos')" class="rounded-md bg-red-50 text-red-600 px-3 py-1.5 hover:bg-red-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">Tolak</button>
                                    <button v-if="a.statusRaw !== 'wawancara' && a.statusRaw !== 'lolos' && a.statusRaw !== 'tidak lolos'" @click="setInterview(a)" class="rounded-md bg-purple-50 text-purple-600 px-3 py-1.5 hover:bg-purple-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">Wawancara</button>
                                    <Link :href="route('perusahaan.applicants.show', a.id)" class="rounded-md border border-[#eaecf0] bg-white text-[#344054] px-3 py-1.5 hover:bg-slate-800 hover:text-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95 inline-flex items-center justify-center">Detail</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!applicants || applicants.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-400 mb-4">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-[#344054]">Tidak ada pelamar ditemukan</p>
                                <p class="mt-1 text-xs text-[#667085]">Coba ubah filter atau kata kunci pencarian Anda.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-between border-t border-[#eaecf0] px-6 py-4 bg-[#f9fafb]">
                <p class="text-sm text-[#667085]">Menampilkan <span class="font-semibold text-[#101828]">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span>–<span class="font-semibold text-[#101828]">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> dari <span class="font-semibold text-[#101828]">{{ pagination.total }}</span> pelamar</p>
                <div class="flex gap-1">
                    <button v-for="link in pagination.links" :key="link.label" @click="goToPage(link.url)" :disabled="!link.url" v-html="link.label" :class="[
                        'rounded-lg px-3 py-1.5 text-sm font-medium transition-all',
                        link.active ? 'bg-[#2563EB] text-white shadow-sm' : 'text-[#667085] hover:bg-[#f1f5f9]',
                        !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                    ]" />
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
                        <div class="text-center">
                            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full" :class="modalConfig.color === 'emerald' ? 'bg-emerald-100' : modalConfig.color === 'red' ? 'bg-red-100' : 'bg-purple-100'">
                                <svg v-if="modalConfig.icon === 'check'" class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <svg v-if="modalConfig.icon === 'x'" class="h-8 w-8 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                <svg v-if="modalConfig.icon === 'calendar'" class="h-8 w-8 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-[#101828]">{{ modalConfig.title }}</h3>
                            <p class="mt-3 text-sm text-[#667085] leading-relaxed">{{ modalConfig.message }}</p>
                            <p class="mt-2 text-xs text-[#98a2b3]">Notifikasi akan otomatis dikirim ke pelamar.</p>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="flex-1 rounded-xl border border-[#d0d5dd] bg-white py-3 text-sm font-semibold text-[#344054] hover:bg-[#f9fafb] transition-all">Batal</button>
                            <button @click="confirmAction" :disabled="processing" :class="['flex-1 rounded-xl py-3 text-sm font-semibold text-white transition-all shadow-sm disabled:opacity-50', modalConfig.btnClass]">
                                <span v-if="processing" class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Memproses...
                                </span>
                                <span v-else>{{ modalConfig.btnText }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SikaraLayout>
</template>
