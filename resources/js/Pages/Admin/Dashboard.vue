<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Array,
    pipeline: Array,
    recentUsers: Array,
    recentApplications: Array,
    platformModules: Array,
});

const colorMap = {
    blue:    { bg: 'bg-blue-50',    text: 'text-blue-600',    pill: 'bg-blue-100 text-blue-700',    bar: 'bg-blue-500' },
    indigo:  { bg: 'bg-indigo-50',  text: 'text-indigo-600',  pill: 'bg-indigo-100 text-indigo-700',  bar: 'bg-indigo-500' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', pill: 'bg-emerald-100 text-emerald-700', bar: 'bg-emerald-500' },
    purple:  { bg: 'bg-purple-50',  text: 'text-purple-600',  pill: 'bg-purple-100 text-purple-700',  bar: 'bg-purple-500' },
    orange:  { bg: 'bg-orange-50',  text: 'text-orange-600',  pill: 'bg-orange-100 text-orange-700',  bar: 'bg-orange-500' },
    pink:    { bg: 'bg-pink-50',    text: 'text-pink-600',    pill: 'bg-pink-100 text-pink-700',    bar: 'bg-pink-500' },
    teal:    { bg: 'bg-teal-50',    text: 'text-teal-600',    pill: 'bg-teal-100 text-teal-700',    bar: 'bg-teal-500' },
    cyan:    { bg: 'bg-cyan-50',    text: 'text-cyan-600',    pill: 'bg-cyan-100 text-cyan-700',    bar: 'bg-cyan-500' },
    red:     { bg: 'bg-red-50',     text: 'text-red-600',     pill: 'bg-red-100 text-red-700',     bar: 'bg-red-500' },
};
const c = (color, type) => colorMap[color]?.[type] ?? colorMap.blue[type];

const roleStyle = {
    mahasiswa:  'bg-blue-100 text-blue-700',
    perusahaan: 'bg-emerald-100 text-emerald-700',
    admin:      'bg-purple-100 text-purple-700',
};
const statusStyle = {
    active:   'bg-emerald-100 text-emerald-700',
    inactive: 'bg-slate-100 text-slate-500',
    banned:   'bg-red-100 text-red-600',
};
const roleLabel = { mahasiswa: 'Mahasiswa', perusahaan: 'Perusahaan', admin: 'Admin' };
const statusLabel = { active: 'Aktif', inactive: 'Inaktif', banned: 'Banned' };

const today = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>

<template>
    <Head title="Dashboard" />
    <AdminLayout title="Dashboard Overview">

        <!-- Welcome Banner -->
        <div class="mb-8 relative overflow-hidden rounded-2xl bg-[#0F172A] p-7 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-10 -top-10 h-52 w-52 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute right-40 -bottom-8 h-36 w-36 rounded-full bg-purple-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Selamat datang kembali</p>
                    <h1 class="mt-1.5 text-2xl font-bold">{{ $page.props.auth.user.name }}</h1>
                    <p class="mt-1 text-sm text-slate-300">Administrator SIKARA — Platform Magang Terintegrasi</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-[11px] text-slate-400">Hari ini</p>
                        <p class="text-sm font-semibold text-white">{{ today }}</p>
                    </div>
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold ring-2 ring-white/20">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 animate-fade-in-up delay-100">
            <div v-for="stat in stats" :key="stat.label"
                class="group relative overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
                <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl" :class="c(stat.color, 'bar')"></div>
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl transition-transform group-hover:scale-105"
                        :class="[c(stat.color, 'bg'), c(stat.color, 'text')]">
                        <svg v-if="stat.icon==='users'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <svg v-if="stat.icon==='graduation'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <svg v-if="stat.icon==='building'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                        <svg v-if="stat.icon==='briefcase'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <svg v-if="stat.icon==='inbox'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        <svg v-if="stat.icon==='calendar'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <svg v-if="stat.icon==='book'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <svg v-if="stat.icon==='chart'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-bold text-[#0F172A]">{{ stat.value }}</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-[#64748B]">{{ stat.label }}</p>
                    <p v-if="stat.sub" class="mt-1.5 text-xs text-[#94A3B8]">{{ stat.sub }}</p>
                </div>
            </div>
        </div>

        <!-- Application Pipeline -->
        <div class="mt-8 rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm animate-fade-in-up delay-200">
            <div class="mb-8">
                <h3 class="text-base font-bold text-[#0F172A]">Pipeline Lamaran Magang — Seluruh Platform</h3>
                <p class="mt-0.5 text-sm text-[#64748B]">Agregat status seluruh lamaran yang masuk di SIKARA</p>
            </div>
            <div class="relative flex items-start justify-between px-4">
                <div class="absolute left-16 right-16 top-6 h-0.5 bg-[#E2E8F0] z-0"></div>
                <div v-for="item in pipeline" :key="item.label" class="relative z-10 flex flex-col items-center flex-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full ring-4 ring-white shadow-sm"
                        :class="[c(item.color, 'bg'), c(item.color, 'text')]">
                        <svg v-if="item.icon==='clock'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <svg v-if="item.icon==='chat'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
                        <svg v-if="item.icon==='check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <svg v-if="item.icon==='x'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-[#0F172A]">{{ item.value }}</p>
                    <p class="mt-1 text-sm font-medium text-[#64748B] text-center">{{ item.label }}</p>
                </div>
            </div>
        </div>

        <!-- Platform Modules Grid -->
        <div class="mt-8 animate-fade-in-up delay-300">
            <div class="mb-4">
                <h3 class="text-base font-bold text-[#0F172A]">Modul Platform SIKARA</h3>
                <p class="mt-0.5 text-sm text-[#64748B]">Ringkasan data setiap modul yang berjalan di platform</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="mod in platformModules" :key="mod.name"
                    class="group flex items-center gap-5 rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 hover:-translate-y-0.5 cursor-pointer">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl transition-transform group-hover:scale-105"
                        :class="[c(mod.color, 'bg'), c(mod.color, 'text')]">
                        <svg v-if="mod.icon==='users'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <svg v-if="mod.icon==='building'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                        <svg v-if="mod.icon==='briefcase'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <svg v-if="mod.icon==='calendar'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <svg v-if="mod.icon==='book'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <svg v-if="mod.icon==='inbox'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-[#0F172A] truncate">{{ mod.name }}</p>
                            <span class="shrink-0 text-lg font-bold" :class="c(mod.color, 'text')">{{ mod.count }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-[#64748B] truncate">{{ mod.desc }}</p>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider" :class="c(mod.color, 'text')">{{ mod.unit }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Two-Column: Recent Users + Recent Applications -->
        <div class="mt-8 grid gap-8 lg:grid-cols-5 animate-fade-in-up delay-400">
            <!-- Recent Users Table -->
            <div class="lg:col-span-3 rounded-2xl border border-[#E2E8F0] bg-white shadow-sm overflow-hidden">
                <div class="border-b border-[#F1F5F9] px-6 py-5">
                    <h3 class="text-base font-bold text-[#0F172A]">Registrasi Pengguna Terbaru</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">8 pengguna terakhir yang baru mendaftar</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-[#64748B]">
                        <thead class="bg-[#F8FAFC] text-[11px] font-bold uppercase tracking-wider text-[#94A3B8]">
                            <tr>
                                <th class="px-6 py-3">Pengguna</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Tgl Daftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F8FAFC]">
                            <tr v-for="user in recentUsers" :key="user.id" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#0F172A] text-xs font-bold text-white">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-[#0F172A] truncate max-w-[140px]">{{ user.name }}</p>
                                            <p class="text-xs text-[#94A3B8] truncate max-w-[140px]">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="roleStyle[user.role] ?? 'bg-slate-100 text-slate-600'">
                                        {{ roleLabel[user.role] ?? user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="statusStyle[user.status] ?? 'bg-slate-100 text-slate-600'">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="user.status==='active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        {{ statusLabel[user.status] ?? user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-xs font-medium text-[#64748B]">{{ user.created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="lg:col-span-2 rounded-2xl border border-[#E2E8F0] bg-white shadow-sm overflow-hidden">
                <div class="border-b border-[#F1F5F9] px-6 py-5">
                    <h3 class="text-base font-bold text-[#0F172A]">Lamaran Terbaru</h3>
                    <p class="mt-0.5 text-xs text-[#64748B]">5 lamaran magang terbaru di platform</p>
                </div>
                <div v-if="recentApplications && recentApplications.length > 0" class="divide-y divide-[#F8FAFC]">
                    <div v-for="app in recentApplications" :key="app.id" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/60 transition-colors">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="[c(app.statusColor, 'bg'), c(app.statusColor, 'text')]">
                            {{ app.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-[#0F172A] text-sm truncate">{{ app.name }}</p>
                            <p class="text-xs text-[#64748B] truncate">{{ app.position }}</p>
                            <p class="text-[11px] text-[#94A3B8]">{{ app.date }}</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                            :class="[c(app.statusColor, 'bg'), c(app.statusColor, 'text')]">
                            {{ app.status }}
                        </span>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-[#344054]">Belum ada lamaran</p>
                    <p class="mt-1 text-xs text-[#64748B]">Lamaran baru akan muncul di sini.</p>
                </div>

                <!-- Security Panel -->
                <div class="border-t border-[#F1F5F9] bg-[#0F172A] p-5 text-white relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 h-20 w-20 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
                    <div class="relative z-10">
                        <p class="text-sm font-bold">Pusat Keamanan</p>
                        <p class="mt-1 text-xs text-slate-400 leading-relaxed">Pantau audit log dan sesi aktif sistem secara berkala.</p>
                        <div class="mt-4 space-y-2">
                            <button class="flex w-full items-center justify-between rounded-xl bg-white/5 px-4 py-3 text-xs font-semibold transition hover:bg-white/10">
                                Cek Audit Logs
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            </button>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-4 py-2.5">
                                <span class="text-xs text-slate-300">Durasi Sesi</span>
                                <span class="text-xs font-semibold text-emerald-400">30 Menit (Ketat)</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-4 py-2.5">
                                <span class="text-xs text-slate-300">Concurrent Login</span>
                                <span class="text-xs font-semibold text-red-400">Dinonaktifkan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
.delay-400 { animation-delay: 400ms; }
</style>
