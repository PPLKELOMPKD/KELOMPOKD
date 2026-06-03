<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    stats: Object,
    users: Object, // Tab 1
    enrollments: Object, // Tab 2
    activityLogs: Object, // Tab 3
    filters: Object,
});

// Active Tab state
const activeTab = ref(props.filters?.tab || 'pengguna');

// Search & Filter state
const searchPengguna = ref(props.filters?.search_pengguna || '');
const filterRole = ref(props.filters?.filter_role || 'all');

const searchEnrollment = ref(props.filters?.search_enrollment || '');
const filterEnrollment = ref(props.filters?.filter_enrollment || 'all');

let debounceTimeout = null;

// Watchers to trigger Inertia reload on search/filter change
watch([searchPengguna, filterRole], () => {
    if (activeTab.value !== 'pengguna') return;
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.lms.index'), {
            tab: 'pengguna',
            search_pengguna: searchPengguna.value,
            filter_role: filterRole.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

watch([searchEnrollment, filterEnrollment], () => {
    if (activeTab.value !== 'enrollment') return;
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('admin.lms.index'), {
            tab: 'enrollment',
            search_enrollment: searchEnrollment.value,
            filter_enrollment: filterEnrollment.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const changeTab = (tabName) => {
    activeTab.value = tabName;
    const params = { tab: tabName };
    if (tabName === 'pengguna') {
        params.search_pengguna = searchPengguna.value;
        params.filter_role = filterRole.value;
    } else if (tabName === 'enrollment') {
        params.search_enrollment = searchEnrollment.value;
        params.filter_enrollment = filterEnrollment.value;
    }
    router.get(route('admin.lms.index'), params, { preserveState: true });
};

// ─── Modal States ───────────────────────────────────────────────────
const showUserModal = ref(false);
const userModalData = ref(null);
const loadingUserDetail = ref(false);

const showEnrollmentModal = ref(false);
const enrollmentModalData = ref(null);
const loadingEnrollmentDetail = ref(false);

// Action Forms
const actionForm = useForm({});

// User Detail Fetch
const openUserDetail = async (userId) => {
    loadingUserDetail.value = true;
    showUserModal.value = true;
    try {
        const response = await fetch(route('admin.lms.users.detail', userId));
        if (response.ok) {
            userModalData.value = await response.json();
        }
    } catch (e) {
        console.error("Gagal memuat detail pengguna", e);
    } finally {
        loadingUserDetail.value = false;
    }
};

// Enrollment Detail Fetch
const openEnrollmentDetail = async (enrollmentId) => {
    loadingEnrollmentDetail.value = true;
    showEnrollmentModal.value = true;
    try {
        const response = await fetch(route('admin.lms.enrollments.detail', enrollmentId));
        if (response.ok) {
            enrollmentModalData.value = await response.json();
        }
    } catch (e) {
        console.error("Gagal memuat detail enrollment", e);
    } finally {
        loadingEnrollmentDetail.value = false;
    }
};

// Close Modals
const closeUserModal = () => {
    showUserModal.value = false;
    userModalData.value = null;
};

const closeEnrollmentModal = () => {
    showEnrollmentModal.value = false;
    enrollmentModalData.value = null;
};

// ─── Action Actions ────────────────────────────────────────────────
// User Actions
const suspendUser = (userId) => {
    if (confirm("Apakah Anda yakin ingin menangguhkan (suspend) akun pengguna ini?")) {
        actionForm.patch(route('admin.lms.users.suspend', userId), {
            preserveScroll: true,
            onSuccess: () => {
                closeUserModal();
            }
        });
    }
};

const activateUser = (userId) => {
    actionForm.patch(route('admin.lms.users.activate', userId), {
        preserveScroll: true,
        onSuccess: () => {
            closeUserModal();
        }
    });
};

const deleteUser = (userId) => {
    if (confirm("Apakah Anda yakin ingin menghapus akun pengguna ini dari sistem secara permanen?")) {
        actionForm.delete(route('admin.lms.users.destroy', userId), {
            preserveScroll: true,
            onSuccess: () => {
                closeUserModal();
            }
        });
    }
};

// Enrollment Actions
const resetEnrollment = (enrollmentId) => {
    if (confirm("Apakah Anda yakin ingin meriset seluruh progres belajar untuk enrollment ini? Semua riwayat penyelesaian bab, materi, kuis, dan tugas peserta akan dihapus.")) {
        actionForm.post(route('admin.lms.enrollments.reset', enrollmentId), {
            preserveScroll: true,
            onSuccess: () => {
                closeEnrollmentModal();
            }
        });
    }
};

const deleteEnrollment = (enrollmentId) => {
    if (confirm("Apakah Anda yakin ingin menghapus pendaftaran peserta ini dari kursus? Tindakan ini tidak dapat dibatalkan.")) {
        actionForm.delete(route('admin.lms.enrollments.destroy', enrollmentId), {
            preserveScroll: true,
            onSuccess: () => {
                closeEnrollmentModal();
            }
        });
    }
};

// Helper Helpers
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTime = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Pantau LMS" />
    <AdminLayout title="Pantau LMS">

        <!-- Header Banner & Hero Stats -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] p-6 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-6 -top-6 h-40 w-40 rounded-full bg-purple-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/2 -bottom-8 h-32 w-32 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Title section -->
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 ring-1 ring-blue-400/30">
                        <svg class="h-6 w-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Pantau LMS</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Pantau aktivitas pembelajaran, pengguna, dan enrollment LMS secara terpusat.</p>
                    </div>
                </div>

                <!-- Stats Badges (Right side like Moderasi Lowongan) -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex flex-col items-center px-4 py-2 bg-blue-500/20 rounded-xl ring-1 ring-blue-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-blue-300">{{ stats.total_course }}</span>
                        <span class="text-[9px] font-bold text-blue-400/85 uppercase tracking-wider">Total Course</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-emerald-300">{{ stats.total_mahasiswa_aktif }}</span>
                        <span class="text-[9px] font-bold text-emerald-400/85 uppercase tracking-wider">Mhs Aktif</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-amber-500/20 rounded-xl ring-1 ring-amber-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-amber-300">{{ stats.total_enrollment }}</span>
                        <span class="text-[9px] font-bold text-amber-400/85 uppercase tracking-wider">Enrollment</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-purple-500/20 rounded-xl ring-1 ring-purple-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-purple-300">{{ stats.completion_rate }}%</span>
                        <span class="text-[9px] font-bold text-purple-400/85 uppercase tracking-wider">Comp. Rate</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab & Filter Bar -->
        <div class="mb-5 flex flex-col md:flex-row md:items-center gap-4 animate-fade-in-up delay-100">
            <!-- Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-white border border-slate-200 p-1 shadow-sm overflow-x-auto">
                <button
                    @click="changeTab('pengguna')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="activeTab === 'pengguna'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Pengguna LMS
                </button>
                <!-- <button
                    @click="changeTab('enrollment')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="activeTab === 'enrollment'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Enrollment
                </button>
                <button
                    @click="changeTab('aktivitas')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="activeTab === 'aktivitas'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Aktivitas LMS
                </button> -->
            </div>

            <!-- Dynamic Search and Filters depending on the Tab -->
            <div v-if="activeTab === 'pengguna'" class="flex flex-1 flex-col sm:flex-row items-center gap-3 md:justify-end">
                <!-- Dropdown Filter Role -->
                <select
                    v-model="filterRole"
                    class="rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 px-3 py-2.5 shadow-sm focus:border-blue-400 focus:outline-none transition w-full sm:w-auto"
                >
                    <option value="all">Semua Role</option>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="perusahaan">Perusahaan</option>
                </select>
                <!-- Search input -->
                <div class="relative w-full sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        v-model="searchPengguna"
                        type="text"
                        placeholder="Cari nama atau email..."
                        class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                    />
                </div>
            </div>

            <div v-else-if="activeTab === 'enrollment'" class="flex flex-1 flex-col sm:flex-row items-center gap-3 md:justify-end">
                <!-- Dropdown Filter Status -->
                <select
                    v-model="filterEnrollment"
                    class="rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 px-3 py-2.5 shadow-sm focus:border-blue-400 focus:outline-none transition w-full sm:w-auto"
                >
                    <option value="all">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
                <!-- Search input -->
                <div class="relative w-full sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input
                        v-model="searchEnrollment"
                        type="text"
                        placeholder="Cari peserta atau course..."
                        class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content Table Card -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">
            
            <!-- ════════ TAB 1: PENGGUNA LMS ════════ -->
            <div v-if="activeTab === 'pengguna'">
                <!-- Empty State -->
                <div v-if="users.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Belum ada data LMS tersedia</p>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada hasil pencarian pengguna yang cocok.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Pengguna</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Email</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Role</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Total Course</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course Selesai</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status Akun</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50/60 transition-colors">
                                <!-- Pengguna Info -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 border border-blue-100 text-sm font-bold text-blue-600 uppercase">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 leading-tight">{{ user.name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">Gabung: {{ user.created_at }}</p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Email -->
                                <td class="px-5 py-4 text-sm text-slate-600">{{ user.email }}</td>
                                <!-- Role -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                        :class="user.role === 'mahasiswa' ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-500/10' : 'bg-purple-50 text-purple-600 ring-1 ring-purple-500/10'"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <!-- Total Course -->
                                <td class="px-5 py-4 text-sm font-semibold text-slate-700 text-center sm:text-left">{{ user.total_course }}</td>
                                <!-- Course Selesai -->
                                <td class="px-5 py-4 text-sm font-semibold text-slate-700 text-center sm:text-left">{{ user.course_selesai }}</td>
                                <!-- Status Akun -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="user.status === 'active' ? 'bg-emerald-50 text-emerald-700' : user.status === 'inactive' ? 'bg-slate-100 text-slate-600' : 'bg-red-50 text-red-600'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="user.status === 'active' ? 'bg-emerald-500' : user.status === 'inactive' ? 'bg-slate-400' : 'bg-red-500'"
                                        ></span>
                                        {{ user.status === 'active' ? 'Aktif' : user.status === 'inactive' ? 'Nonaktif' : 'Suspend' }}
                                    </span>
                                </td>
                                <!-- Aksi -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Detail Button -->
                                        <button
                                            @click="openUserDetail(user.id)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50 transition"
                                        >
                                            Detail
                                        </button>
                                        
                                        <!-- Suspend / Aktifkan -->
                                        <button
                                            v-if="user.status !== 'banned'"
                                            @click="suspendUser(user.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-orange-500 hover:bg-orange-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition"
                                        >
                                            Suspend
                                        </button>
                                        <button
                                            v-else
                                            @click="activateUser(user.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition"
                                        >
                                            Aktifkan
                                        </button>

                                        <!-- Hapus Button -->
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-500 hover:bg-red-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ users.from }}–{{ users.to }}</span> dari <span class="font-bold text-slate-700">{{ users.total }}</span> pengguna
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>

            <!-- ════════ TAB 2: ENROLLMENT ════════ -->
            <div v-else-if="activeTab === 'enrollment'">
                <!-- Empty State -->
                <div v-if="enrollments.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Belum ada data LMS tersedia</p>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada hasil pencarian enrollment yang cocok.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Peserta</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Instruktur</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Progress</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Daftar</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="enroll in enrollments.data" :key="enroll.id" class="group hover:bg-slate-50/60 transition-colors">
                                <!-- Peserta -->
                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ enroll.student_name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ enroll.student_email }}</p>
                                </td>
                                <!-- Course -->
                                <td class="px-5 py-4 text-sm font-semibold text-slate-700 leading-tight">{{ enroll.course_title }}</td>
                                <!-- Instruktur -->
                                <td class="px-5 py-4 text-sm text-slate-600">{{ enroll.instructor_name }}</td>
                                <!-- Progress -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-24 rounded-full bg-slate-100 overflow-hidden shrink-0">
                                            <div class="h-full rounded-full bg-blue-500 transition-all duration-300" :style="`width: ${enroll.progress}%`"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700">{{ enroll.progress }}%</span>
                                    </div>
                                </td>
                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold"
                                        :class="enroll.status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : enroll.status === 'Aktif' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="enroll.status === 'Selesai' ? 'bg-emerald-500' : enroll.status === 'Aktif' ? 'bg-blue-500' : 'bg-red-500'"
                                        ></span>
                                        {{ enroll.status }}
                                    </span>
                                </td>
                                <!-- Tanggal Daftar -->
                                <td class="px-5 py-4 text-sm text-slate-500">{{ formatDate(enroll.enrolled_at) }}</td>
                                <!-- Aksi -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Detail Button -->
                                        <button
                                            @click="openEnrollmentDetail(enroll.id)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50 transition"
                                        >
                                            Detail
                                        </button>
                                        <!-- Reset Progress -->
                                        <button
                                            @click="resetEnrollment(enroll.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-orange-500 hover:bg-orange-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition"
                                        >
                                            Reset
                                        </button>
                                        <!-- Hapus Enrollment -->
                                        <button
                                            @click="deleteEnrollment(enroll.id)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-500 hover:bg-red-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="enrollments.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ enrollments.from }}–{{ enrollments.to }}</span> dari <span class="font-bold text-slate-700">{{ enrollments.total }}</span> pendaftaran
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in enrollments.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>

            <!-- ════════ TAB 3: AKTIVITAS LMS ════════ -->
            <div v-else-if="activeTab === 'aktivitas'">
                <!-- Empty State -->
                <div v-if="activityLogs.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Belum ada data LMS tersedia</p>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada catatan aktivitas LMS dalam log sistem.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Waktu</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Pengguna</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Aktivitas</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="log in activityLogs.data" :key="log.id" class="hover:bg-slate-50/60 transition-colors">
                                <!-- Waktu -->
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-700 leading-tight">{{ formatDate(log.created_at) }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ formatTime(log.created_at) }} WIB</p>
                                </td>
                                <!-- Pengguna -->
                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ log.user?.name ?? 'System' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        <span class="inline-flex rounded-full px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">{{ log.role }}</span>
                                        <span class="ml-1.5 text-slate-500 font-mono text-[9px]">{{ log.user?.email }}</span>
                                    </p>
                                </td>
                                <!-- Aktivitas -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-600">
                                        {{ log.action }}
                                    </span>
                                </td>
                                <!-- Keterangan -->
                                <td class="px-5 py-4 text-xs text-slate-600 leading-relaxed">{{ log.description }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="activityLogs.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ activityLogs.from }}–{{ activityLogs.to }}</span> dari <span class="font-bold text-slate-700">{{ activityLogs.total }}</span> log
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in activityLogs.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══ MODAL DETAIL PENGGUNA ════════════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeUserModal">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showUserModal" class="relative z-10 w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Loading State -->
                            <div v-if="loadingUserDetail" class="flex flex-col items-center justify-center py-20">
                                <svg class="h-8 w-8 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <p class="text-xs font-bold text-slate-500 mt-3">Memuat detail pengguna...</p>
                            </div>

                            <div v-else-if="userModalData" class="flex flex-col">
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 font-bold uppercase text-lg">
                                            {{ userModalData.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-black text-slate-900">Detail Pengguna LMS</h3>
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ userModalData.user.name }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeUserModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="px-6 py-5 space-y-5 max-h-[60vh] overflow-y-auto custom-scrollbar">
                                    <!-- Info Akun -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Akun</h4>
                                        <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-xl p-4 text-xs">
                                            <div>
                                                <p class="text-slate-400">Nama Lengkap</p>
                                                <p class="font-bold text-slate-800 mt-0.5">{{ userModalData.user.name }}</p>
                                            </div>
                                            <div>
                                                <p class="text-slate-400">Email Kontak</p>
                                                <p class="font-bold text-slate-800 mt-0.5">{{ userModalData.user.email }}</p>
                                            </div>
                                            <div>
                                                <p class="text-slate-400">Peran Pengguna</p>
                                                <p class="font-bold text-slate-800 mt-0.5 capitalize">{{ userModalData.user.role }}</p>
                                            </div>
                                            <div>
                                                <p class="text-slate-400">Tanggal Bergabung</p>
                                                <p class="font-bold text-slate-800 mt-0.5">{{ userModalData.user.created_at }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Statistik -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Statistik Pembelajaran</h4>
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3.5 text-center">
                                                <p class="text-lg font-black text-blue-700">{{ userModalData.stats.total_enrollment }}</p>
                                                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider mt-0.5">
                                                    {{ userModalData.user.role === 'mahasiswa' ? 'Total Enrolled' : 'Total Siswa' }}
                                                </p>
                                            </div>
                                            <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3.5 text-center">
                                                <p class="text-lg font-black text-emerald-700">{{ userModalData.stats.course_selesai }}</p>
                                                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider mt-0.5">
                                                    {{ userModalData.user.role === 'mahasiswa' ? 'Course Selesai' : 'Course Aktif' }}
                                                </p>
                                            </div>
                                            <div class="bg-purple-50/50 border border-purple-100 rounded-xl p-3.5 text-center">
                                                <p class="text-lg font-black text-purple-700">
                                                    {{ userModalData.user.role === 'mahasiswa' ? userModalData.stats.avg_progress + '%' : 'Instruktur' }}
                                                </p>
                                                <p class="text-[10px] text-purple-600 font-bold uppercase tracking-wider mt-0.5">Progres Rata-rata</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Riwayat Course -->
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                                            {{ userModalData.user.role === 'mahasiswa' ? 'Riwayat Pembelajaran' : 'Kursus Yang Dikelola' }}
                                        </h4>
                                        <div class="border border-slate-100 rounded-xl overflow-hidden">
                                            <table class="w-full text-left text-xs">
                                                <thead class="bg-slate-50">
                                                    <tr>
                                                        <th class="px-4 py-2.5 font-bold text-slate-500">Nama Course</th>
                                                        <th class="px-4 py-2.5 font-bold text-slate-500">{{ userModalData.user.role === 'mahasiswa' ? 'Instruktur' : 'Pengguna' }}</th>
                                                        <th class="px-4 py-2.5 font-bold text-slate-500">Progres</th>
                                                        <th class="px-4 py-2.5 font-bold text-slate-500">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    <tr v-for="(item, idx) in userModalData.riwayat" :key="idx" class="hover:bg-slate-50/50">
                                                        <td class="px-4 py-2.5 font-semibold text-slate-800">{{ item.course_title }}</td>
                                                        <td class="px-4 py-2.5 text-slate-600">{{ item.instructor }}</td>
                                                        <td class="px-4 py-2.5 text-slate-700 font-bold">
                                                            {{ typeof item.progress === 'number' ? item.progress + '%' : item.progress }}
                                                        </td>
                                                        <td class="px-4 py-2.5">
                                                            <span class="rounded px-1.5 py-0.5 text-[9px] font-bold"
                                                                :class="item.status === 'Selesai' || item.status === 'Published' ? 'bg-emerald-50 text-emerald-700' : item.status === 'Dibatalkan' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700'"
                                                            >
                                                                {{ item.status }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="userModalData.riwayat.length === 0">
                                                        <td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada riwayat data LMS.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4 bg-slate-50">
                                    <!-- Delete option on the left -->
                                    <button
                                        @click="deleteUser(userModalData.user.id)"
                                        class="rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-100 transition"
                                    >
                                        Hapus Akun
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <button
                                            v-if="userModalData.user.status !== 'banned'"
                                            @click="suspendUser(userModalData.user.id)"
                                            class="rounded-xl bg-orange-500 hover:bg-orange-600 px-4 py-2 text-xs font-bold text-white transition"
                                        >
                                            Suspend Akun
                                        </button>
                                        <button
                                            v-else
                                            @click="activateUser(userModalData.user.id)"
                                            class="rounded-xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition"
                                        >
                                            Aktifkan Akun
                                        </button>
                                        <button @click="closeUserModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ MODAL DETAIL ENROLLMENT ══════════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showEnrollmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeEnrollmentModal">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showEnrollmentModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Loading State -->
                            <div v-if="loadingEnrollmentDetail" class="flex flex-col items-center justify-center py-20">
                                <svg class="h-8 w-8 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <p class="text-xs font-bold text-slate-500 mt-3">Memuat detail enrollment...</p>
                            </div>

                            <div v-else-if="enrollmentModalData" class="flex flex-col">
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-black text-slate-900">Detail Enrollment</h3>
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ enrollmentModalData.course_title }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeEnrollmentModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="px-6 py-5 space-y-4">
                                    <!-- Detail Grid -->
                                    <div class="grid grid-cols-2 gap-3.5 bg-slate-50 rounded-xl p-4 text-xs">
                                        <div>
                                            <p class="text-slate-400">Nama Peserta</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.student_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400">Instruktur</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.instructor_name }}</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="text-slate-400">Course</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.course_title }}</p>
                                        </div>
                                    </div>

                                    <!-- Progres Rincian -->
                                    <div class="space-y-2.5">
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Progress Belajar</h4>
                                        
                                        <!-- Progress Bar -->
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-blue-500 transition-all duration-500" :style="`width: ${enrollmentModalData.progress}%`"></div>
                                            </div>
                                            <span class="text-sm font-black text-slate-800">{{ enrollmentModalData.progress }}%</span>
                                        </div>

                                        <!-- Item Breakdown -->
                                        <div class="grid grid-cols-3 gap-2 text-xs pt-1.5">
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.lesson_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Lesson Selesai</p>
                                            </div>
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.quiz_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Quiz Selesai</p>
                                            </div>
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.assignment_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Tugas Selesai</p>
                                            </div>
                                        </div>

                                        <!-- Nilai & Status -->
                                        <div class="grid grid-cols-2 gap-3.5 pt-2 text-xs">
                                            <div class="flex justify-between items-center bg-slate-50 px-3.5 py-2.5 rounded-xl">
                                                <span class="text-slate-500 font-medium">Nilai Akhir:</span>
                                                <span class="font-black text-slate-800 text-sm">{{ enrollmentModalData.final_grade }}</span>
                                            </div>
                                            <div class="flex justify-between items-center bg-slate-50 px-3.5 py-2.5 rounded-xl">
                                                <span class="text-slate-500 font-medium">Status:</span>
                                                <span class="rounded px-2 py-0.5 text-[10px] font-bold"
                                                    :class="enrollmentModalData.status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : enrollmentModalData.status === 'Dibatalkan' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700'"
                                                >
                                                    {{ enrollmentModalData.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                    <button
                                        @click="resetEnrollment(enrollmentModalData.id)"
                                        class="rounded-xl bg-orange-500 hover:bg-orange-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition"
                                    >
                                        Reset Progress
                                    </button>
                                    <button
                                        @click="deleteEnrollment(enrollmentModalData.id)"
                                        class="rounded-xl bg-red-500 hover:bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition"
                                    >
                                        Hapus Enrollment
                                    </button>
                                    <button @click="closeEnrollmentModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                                        Tutup
                                    </button>
                                </div>
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

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #E2E8F0;
    border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
}
</style>
