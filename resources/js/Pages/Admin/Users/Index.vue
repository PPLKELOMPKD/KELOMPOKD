<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    users: Object,
    stats: Object,
    search: String,
    roleFilter: String,
    statusFilter: String,
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});

// ── Local filter state ────────────────────────────────────────────────────
const searchInput  = ref(props.search ?? '');
const roleFilter   = ref(props.roleFilter ?? 'all');
const statusFilter = ref(props.statusFilter ?? 'all');

function applyFilters() {
    router.get(route('admin.users.index'), {
        search: searchInput.value || undefined,
        role:   roleFilter.value !== 'all' ? roleFilter.value : undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, replace: true });
}

let searchTimer = null;
watch(searchInput, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 400);
});
watch([roleFilter, statusFilter], applyFilters);

// ── Action modal ─────────────────────────────────────────────────────────
const modal       = ref({ open: false, user: null, status: '', reason: '' });
const processing  = ref(false);

function openModal(user, status) {
    modal.value = { open: true, user, status, reason: '' };
}
function closeModal() {
    modal.value = { open: false, user: null, status: '', reason: '' };
}

function submitStatusChange() {
    if (processing.value) return;
    processing.value = true;
    router.patch(
        route('admin.users.updateStatus', modal.value.user.id),
        { status: modal.value.status, reason: modal.value.reason },
        {
            preserveScroll: true,
            onSuccess: () => { closeModal(); processing.value = false; },
            onError:   () => { processing.value = false; },
        }
    );
}

// ── Helpers ───────────────────────────────────────────────────────────────
const roleStyle  = { mahasiswa: 'bg-blue-100 text-blue-700', perusahaan: 'bg-emerald-100 text-emerald-700' };
const roleLabel  = { mahasiswa: 'Mahasiswa', perusahaan: 'Perusahaan' };
const statusCfg  = {
    active:   { cls: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500', label: 'Aktif' },
    inactive: { cls: 'bg-slate-100 text-slate-500',    dot: 'bg-slate-400',   label: 'Nonaktif' },
    banned:   { cls: 'bg-red-100 text-red-600',         dot: 'bg-red-500',     label: 'Banned' },
};
const actionCfg = {
    active:   { label: 'Aktifkan',    color: 'emerald', icon: 'check' },
    inactive: { label: 'Nonaktifkan', color: 'slate',   icon: 'pause' },
    banned:   { label: 'Blokir',      color: 'red',     icon: 'ban'   },
};

function availableActions(user) {
    return ['active', 'inactive', 'banned'].filter(s => s !== user.status);
}

const modalTitle = computed(() => {
    if (!modal.value.status) return '';
    return { active: 'Aktifkan Akun', inactive: 'Nonaktifkan Akun', banned: 'Blokir (Banned) Akun' }[modal.value.status];
});
const modalIsDanger = computed(() => modal.value.status === 'banned');
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <AdminLayout title="Manajemen Pengguna">

        <!-- Flash messages -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
            <div v-if="flash.success" class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ flash.success }}
            </div>
        </transition>
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
            <div v-if="flash.error" class="mb-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ flash.error }}
            </div>
        </transition>

        <!-- Page header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Manajemen Pengguna</h1>
                <p class="mt-0.5 text-sm text-slate-500">Kelola status akun mahasiswa &amp; perusahaan terdaftar</p>
            </div>
        </div>

        <!-- Stats cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6 animate-fade-in-up">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ stats.total }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-400">Mahasiswa</p>
                <p class="mt-1 text-2xl font-black text-blue-700">{{ stats.mahasiswa }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Perusahaan</p>
                <p class="mt-1 text-2xl font-black text-emerald-700">{{ stats.perusahaan }}</p>
            </div>
            <div class="rounded-2xl border border-green-100 bg-green-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-green-400">Aktif</p>
                <p class="mt-1 text-2xl font-black text-green-700">{{ stats.active }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nonaktif</p>
                <p class="mt-1 text-2xl font-black text-slate-600">{{ stats.inactive }}</p>
            </div>
            <div class="rounded-2xl border border-red-100 bg-red-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-red-400">Banned</p>
                <p class="mt-1 text-2xl font-black text-red-600">{{ stats.banned }}</p>
            </div>
        </div>

        <!-- Filters bar -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 animate-fade-in-up delay-100">
            <!-- Search -->
            <div class="relative flex-1 group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari nama atau email pengguna…"
                    class="block w-full rounded-2xl border-0 py-3.5 pl-11 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all bg-white hover:bg-slate-50/50"
                />
            </div>
            <!-- Role filter -->
            <select v-model="roleFilter" class="block w-full sm:w-48 rounded-2xl border-0 py-3.5 pl-4 pr-10 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all bg-white hover:bg-slate-50/50 cursor-pointer">
                <option value="all">Semua Role</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="perusahaan">Perusahaan</option>
            </select>
            <!-- Status filter -->
            <select v-model="statusFilter" class="block w-full sm:w-48 rounded-2xl border-0 py-3.5 pl-4 pr-10 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all bg-white hover:bg-slate-50/50 cursor-pointer">
                <option value="all">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="banned">Banned</option>
            </select>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Daftar Pengguna</h2>
                <span class="text-xs text-slate-500">{{ users.total }} pengguna ditemukan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Pengguna</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Profil</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Terdaftar</th>
                            <th class="px-6 py-3">Login Terakhir</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    <p class="text-sm font-semibold">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs">Coba ubah filter pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="user in users.data" :key="user.id" class="group hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">
                            <!-- Pengguna -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm"
                                         :class="user.role === 'mahasiswa' ? 'bg-gradient-to-br from-blue-500 to-blue-600' : 'bg-gradient-to-br from-emerald-500 to-emerald-600'">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ user.name }}</p>
                                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Role -->
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold" :class="roleStyle[user.role]">
                                    {{ roleLabel[user.role] }}
                                </span>
                            </td>
                            <!-- Profil ringkasan -->
                            <td class="px-6 py-4">
                                <template v-if="user.role === 'mahasiswa' && user.profile">
                                    <p class="text-xs text-slate-700 font-semibold">{{ user.profile.study_program ?? '—' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ user.profile.university ?? '' }}</p>
                                </template>
                                <template v-else-if="user.role === 'perusahaan' && user.profile">
                                    <p class="text-xs text-slate-700 font-semibold">{{ user.profile.industry ?? '—' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ user.profile.location ?? '' }}</p>
                                </template>
                                <span v-else class="text-[11px] text-slate-400 italic">Profil belum diisi</span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                      :class="statusCfg[user.status]?.cls">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="statusCfg[user.status]?.dot"></span>
                                    {{ statusCfg[user.status]?.label }}
                                </span>
                            </td>
                            <!-- Tanggal -->
                            <td class="px-6 py-4 text-xs text-slate-600 font-medium">{{ user.created_at }}</td>
                            <!-- Login terakhir -->
                            <td class="px-6 py-4 text-xs text-slate-500">{{ user.last_login_at }}</td>
                            <!-- Aksi -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Detail -->
                                    <Link :href="route('admin.users.show', user.id)"
                                          class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 transition-colors">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail
                                    </Link>
                                    <!-- Status actions -->
                                    <button v-for="s in availableActions(user)" :key="s"
                                            @click="openModal(user, s)"
                                            class="flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] font-semibold shadow-sm transition-colors"
                                            :class="{
                                                'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100': s === 'active',
                                                'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100': s === 'inactive',
                                                'border-red-200 bg-red-50 text-red-600 hover:bg-red-100': s === 'banned',
                                            }">
                                        <svg v-if="s === 'active'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        <svg v-else-if="s === 'inactive'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                                        <svg v-else class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        {{ actionCfg[s].label }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="border-t border-slate-100 px-6 py-4 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ users.from }}–{{ users.to }} dari {{ users.total }} pengguna
                </p>
                <div class="flex gap-1">
                    <Link v-for="link in users.links" :key="link.label"
                          :href="link.url ?? '#'"
                          :class="[
                              'px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors',
                              link.active
                                  ? 'bg-slate-900 text-white'
                                  : link.url
                                      ? 'border border-slate-200 text-slate-600 hover:bg-slate-50'
                                      : 'border border-slate-100 text-slate-300 cursor-not-allowed pointer-events-none',
                          ]"
                          v-html="link.label"
                          preserve-scroll />
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal"></div>
                <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden">
                        <!-- Modal header -->
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3"
                             :class="modalIsDanger ? 'bg-red-50' : 'bg-slate-50'">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                                 :class="modalIsDanger ? 'bg-red-100' : 'bg-emerald-100'">
                                <svg v-if="modal.status === 'banned'" class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <svg v-else-if="modal.status === 'active'" class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <svg v-else class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="10" y1="15" x2="10" y2="9"/><line x1="14" y1="15" x2="14" y2="9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ modalTitle }}</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ modal.user?.name }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-5 space-y-4">
                            <!-- Warning for banned + company -->
                            <div v-if="modal.status === 'banned' && modal.user?.role === 'perusahaan'"
                                 class="rounded-xl bg-amber-50 border border-amber-200 p-3 flex gap-2.5">
                                <svg class="h-4 w-4 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <p class="text-[11px] text-amber-700 font-medium">
                                    Seluruh lowongan aktif perusahaan ini akan <strong>di-unpublish otomatis</strong> dan tidak akan tampil di halaman publik.
                                </p>
                            </div>
                            <div v-else-if="modal.status === 'inactive' && modal.user?.role === 'perusahaan'"
                                 class="rounded-xl bg-slate-50 border border-slate-200 p-3 flex gap-2.5">
                                <svg class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <p class="text-[11px] text-slate-600 font-medium">
                                    Lowongan aktif perusahaan ini akan di-unpublish otomatis hingga akun diaktifkan kembali.
                                </p>
                            </div>

                            <p class="text-sm text-slate-600">
                                Anda akan mengubah status akun <strong class="text-slate-800">{{ modal.user?.email }}</strong> menjadi
                                <span class="font-bold" :class="{ 'text-red-600': modal.status === 'banned', 'text-emerald-600': modal.status === 'active', 'text-slate-600': modal.status === 'inactive' }">
                                    {{ statusCfg[modal.status]?.label }}
                                </span>.
                            </p>

                            <!-- Reason input -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Alasan <span class="text-slate-400 font-normal">(opsional)</span>
                                </label>
                                <textarea
                                    v-model="modal.reason"
                                    rows="2"
                                    placeholder="Catatan alasan perubahan status ini…"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-none"
                                ></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/80">
                            <button @click="closeModal" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all">
                                Batal
                            </button>
                            <button @click="submitStatusChange" :disabled="processing"
                                    class="rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-md"
                                    :class="modalIsDanger ? 'bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 focus:ring-2 focus:ring-red-500 focus:ring-offset-2' : 'bg-gradient-to-r from-slate-900 to-slate-800 hover:from-slate-800 hover:to-slate-700 focus:ring-2 focus:ring-slate-900 focus:ring-offset-2'">
                                <span v-if="processing" class="flex items-center gap-2">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Memproses…
                                </span>
                                <span v-else>{{ modalTitle }}</span>
                            </button>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>
