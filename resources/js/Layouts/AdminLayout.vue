<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ title: String });

const isSidebarOpen = ref(true);
const logout = () => router.post(route('logout'));

// --- Resizable Sidebar Logic ---
const sidebarWidth = ref(256); // default 256px
const minWidth = 220;
const maxWidth = 400;
const isResizing = ref(false);

onMounted(() => {
    const savedWidth = localStorage.getItem('adminSidebarWidth');
    if (savedWidth) {
        sidebarWidth.value = parseInt(savedWidth);
    }
});

const startResize = (e) => {
    isResizing.value = true;
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', stopResize);
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
};

const doResize = (e) => {
    if (!isResizing.value) return;
    let newWidth = e.clientX;
    if (newWidth < minWidth) newWidth = minWidth;
    if (newWidth > maxWidth) newWidth = maxWidth;
    sidebarWidth.value = newWidth;
};

const stopResize = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', doResize);
    document.removeEventListener('mouseup', stopResize);
    document.body.style.cursor = '';
    document.body.style.userSelect = '';
    localStorage.setItem('adminSidebarWidth', sidebarWidth.value);
};

const menuUtama = [
    { name: 'Dashboard', href: route('admin.dashboard'), icon: 'dashboard', active: route().current('admin.dashboard') },
];

const menuManajemen = [
    { name: 'Manajemen Pengguna',    href: route('admin.users.index'), icon: 'users',     active: route().current('admin.users.*') },
    { name: 'Verifikasi Perusahaan', href: '#', icon: 'building',  active: false },
    { name: 'Moderasi Lowongan',     href: route('admin.internships.index'), icon: 'briefcase', active: route().current('admin.internships.*') },
    { name: 'Manajemen Event',       href: '#', icon: 'calendar',  active: false },
    { name: 'Pantau LMS',            href: '#', icon: 'book',      active: false },
    { name: 'Data Lamaran',          href: route('admin.applications.index'), icon: 'inbox', active: route().current('admin.applications.*') },
];

const menuSistem = [
    { name: 'Log Aktivitas',     href: route('admin.activity-logs.index'), icon: 'activity', active: route().current('admin.activity-logs.*') },
    { name: 'Pengaturan Sistem', href: route('admin.settings.index'), icon: 'settings', active: route().current('admin.settings.*') },
];
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC]">
        <Head :title="title + ' — SIKARA Admin'" />

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-40 h-screen border-r border-[#E2E8F0] bg-white flex flex-col"
            :class="[
                !isSidebarOpen ? '-translate-x-full' : '',
                !isResizing ? 'transition-transform duration-300' : ''
            ]"
            :style="{ width: sidebarWidth + 'px' }"
        >
            <!-- Resizer Handle -->
            <div 
                @mousedown.prevent="startResize" 
                class="absolute right-0 top-0 bottom-0 w-1.5 cursor-col-resize hover:bg-blue-400/50 active:bg-blue-500 z-50 transition-colors"
                title="Tarik untuk mengubah ukuran sidebar"
            ></div>
            <!-- Brand -->
            <div class="flex h-20 shrink-0 items-center border-b border-[#F1F5F9] px-6">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2.5 transition-transform hover:scale-105">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto drop-shadow-sm" />
                    <div class="flex items-center">
                        <span class="text-xl font-black tracking-tight text-[#0F172A]">{{ $page.props.global_settings?.app_name || 'SIKARA' }}</span>
                        <span class="ml-1.5 rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-600 ring-1 ring-inset ring-blue-500/20">Admin</span>
                    </div>
                </Link>
            </div>

            <!-- Nav items (Scrollable) -->
            <div class="flex-1 overflow-y-auto px-4 py-5 custom-scrollbar">
                <nav class="space-y-6">
                    <!-- Menu Utama -->
                    <div>
                        <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-[#94A3B8]">Menu Utama</p>
                        <div class="space-y-1.5">
                            <Link
                                v-for="item in menuUtama"
                                :key="item.name"
                                :href="item.href"
                                :title="item.name"
                                class="sidebar-item group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200"
                                :class="item.active
                                    ? 'bg-[#0F172A] text-white shadow-md shadow-slate-900/10'
                                    : 'text-[#64748B] hover:bg-[#F8FAFC] hover:text-[#0F172A]'"
                            >
                                <span class="sidebar-icon flex h-[22px] w-[22px] shrink-0 items-center justify-center transition-transform group-hover:scale-110">
                                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                </span>
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Manajemen -->
                    <div>
                        <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-[#94A3B8]">Manajemen</p>
                        <div class="space-y-1.5">
                            <Link
                                v-for="item in menuManajemen"
                                :key="item.name"
                                :href="item.href"
                                :title="item.name"
                                class="sidebar-item group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200"
                                :class="item.active
                                    ? 'bg-[#0F172A] text-white shadow-md shadow-slate-900/10'
                                    : 'text-[#64748B] hover:bg-[#F8FAFC] hover:text-[#0F172A]'"
                            >
                                <span class="sidebar-icon flex h-[22px] w-[22px] shrink-0 items-center justify-center transition-transform group-hover:scale-110">
                                    <svg v-if="item.icon==='users'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <svg v-if="item.icon==='building'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 20h16"/><path d="M7 20V6l5-2 5 2v14"/><path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01"/></svg>
                                    <svg v-if="item.icon==='briefcase'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    <svg v-if="item.icon==='calendar'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    <svg v-if="item.icon==='book'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    <svg v-if="item.icon==='inbox'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                                </span>
                                <span class="truncate">{{ item.name }}</span>
                                <span v-if="item.href==='#'" class="ml-auto shrink-0 rounded-full bg-[#F1F5F9] px-1.5 py-0.5 text-[9px] font-bold text-[#94A3B8]">SOON</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Sistem -->
                    <div>
                        <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-[#94A3B8]">Sistem</p>
                        <div class="space-y-1.5">
                            <Link
                                v-for="item in menuSistem"
                                :key="item.name"
                                :href="item.href"
                                :title="item.name"
                                class="sidebar-item group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200"
                                :class="item.active
                                    ? 'bg-[#0F172A] text-white shadow-md shadow-slate-900/10'
                                    : 'text-[#64748B] hover:bg-[#F8FAFC] hover:text-[#0F172A]'"
                            >
                                <span class="sidebar-icon flex h-[22px] w-[22px] shrink-0 items-center justify-center transition-transform group-hover:scale-110">
                                    <svg v-if="item.icon==='activity'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    <svg v-if="item.icon==='settings'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                </span>
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Logout (Fixed at bottom) -->
            <div class="shrink-0 border-t border-[#F1F5F9] p-4 bg-white">
                <button
                    @click="logout"
                    class="sidebar-item group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition-all hover:bg-red-50 hover:text-red-700"
                >
                    <span class="sidebar-icon flex h-[22px] w-[22px] shrink-0 items-center justify-center transition-transform group-hover:scale-110">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </span>
                    <span class="truncate">Keluar Sistem</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main 
            class="min-h-screen flex flex-col" 
            :class="[!isResizing ? 'transition-all duration-300' : '']"
            :style="{ paddingLeft: isSidebarOpen ? sidebarWidth + 'px' : '0px' }"
        >
            <!-- Header -->
            <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#E2E8F0] bg-white/80 px-8 backdrop-blur-md">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = !isSidebarOpen"
                        class="rounded-lg p-2 text-[#64748B] hover:bg-[#F1F5F9] transition-colors">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/>
                        </svg>
                    </button>
                    <div class="flex items-center gap-3">
                        <img src="/images/Logo-SIKARA.png" alt="SIKARA Logo" class="h-9 w-auto object-contain drop-shadow-sm" />
                        <div class="hidden sm:block">
                            <h2 class="text-base font-bold text-[#0F172A]">{{ title }}</h2>
                            <p class="text-xs text-[#64748B]">Panel Administrasi SIKARA</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-bold text-[#0F172A]">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs font-semibold text-[#10B981]">Administrator System</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0F172A] text-sm font-bold text-white ring-2 ring-[#E2E8F0]">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-8 flex-1">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
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
