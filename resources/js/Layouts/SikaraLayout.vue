<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user);
const unreadCount = computed(() => page.props.notifications?.unreadCount ?? 0);

const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = (e) => {
    if (!e.target.closest('.profile-dropdown')) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
    ];

    if (user.value?.role === 'mahasiswa') {
        items.push(
            { label: 'Cari Lowongan', href: route('internships.index'), active: route().current('internships.*') },
            { label: 'List Perusahaan', href: '#', active: false },
            { label: 'LMS', href: route('lms'), active: route().current('lms') },
            { label: 'Pelatihan', href: '#', active: false },
            { label: 'Generate CV', href: '#', active: false },
        );
    } else if (user.value?.role === 'perusahaan' || user.value?.role === 'admin') {
        items.push(
            { label: 'Kelola Pelamar', href: route('perusahaan.applicants.index'), active: route().current('perusahaan.applicants.*') },
            { label: 'Lowongan', href: route('perusahaan.internships.index'), active: route().current('perusahaan.internships.*') },
            { label: 'Event', href: route('perusahaan.events.index'), active: route().current('perusahaan.events.*') },
            { label: 'Laporan', href: route('perusahaan.reports.index'), active: route().current('perusahaan.reports.*') },
        );
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] font-sans text-[#0F172A]">
        <!-- Top Navbar -->
        <nav class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white px-4 py-3 shadow-[0_1px_3px_rgba(16,24,40,0.02)] md:px-8">
            <div class="mx-auto flex max-w-[1400px] items-center justify-between">
                <!-- Left Side: Logo -->
                <div class="flex items-center gap-2">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                    <Link href="/" class="text-[20px] font-extrabold tracking-tight text-[#0F172A]">SIKARA</Link>
                </div>

                <!-- Right Side: Navigation, Notification & Profile -->
                <div class="flex items-center gap-6 md:gap-8">
                    <!-- Nav Items -->
                    <div class="hidden items-center gap-6 md:flex">
                        <Link
                            v-for="item in navItems"
                            :key="item.label"
                            :href="item.href"
                            class="text-[14px] font-semibold transition"
                            :class="item.active ? 'text-[#2563EB]' : 'text-[#64748B] hover:text-[#0F172A]'"
                        >
                            {{ item.label }}
                        </Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Notification Icon -->
                        <Link :href="route('notifications.index')" class="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            <span v-if="unreadCount > 0" class="absolute top-2 right-2.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </Link>

                        <div class="h-6 w-px bg-slate-200"></div>

                        <!-- Profile Dropdown -->
                        <div class="profile-dropdown relative ml-2">
                            <button @click="toggleDropdown" class="flex items-center gap-3 text-left focus:outline-none">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#2563EB] text-sm font-bold text-white shadow-sm">
                                    {{ user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div class="hidden md:block">
                                    <p class="text-[14px] font-bold leading-none text-[#0F172A]">{{ user?.name || 'User' }}</p>
                                    <p class="mt-1 text-[12px] font-medium text-[#64748B] capitalize">{{ user?.role || 'Guest' }}</p>
                                </div>
                                <svg class="hidden h-4 w-4 text-slate-400 md:block transition-transform duration-200 ml-1" :class="isDropdownOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div v-show="isDropdownOpen" class="absolute right-0 mt-3 w-64 origin-top-right rounded-xl bg-white shadow-[0_10px_25px_rgba(0,0,0,0.1),0_4px_6px_rgba(0,0,0,0.05)] ring-1 ring-slate-200 focus:outline-none z-50 overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                                    <p class="text-[15px] font-bold text-[#0F172A] truncate">{{ user?.name }}</p>
                                    <p class="text-[13px] font-medium text-slate-500 truncate">{{ user?.email }}</p>
                                </div>
                                <div class="py-2 border-b border-slate-100">
                                    <Link :href="route('profile.show')" class="flex items-center gap-3 px-5 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        Profil Saya
                                    </Link>
                                    <Link v-if="user?.role === 'perusahaan' || user?.role === 'admin'" href="#" class="flex items-center gap-3 px-5 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.4 1.08V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 8.6 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.08-.4H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6A1.65 1.65 0 0 0 10.4 2.92V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15.4 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.23.38.6.65 1 .76.18.05.36.07.54.07H21a2 2 0 1 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15Z"/></svg>
                                        Pengaturan
                                    </Link>
                                    <Link v-if="user?.role === 'perusahaan'" :href="route('perusahaan.reports.index')" class="flex items-center gap-3 px-5 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        Laporan & Analitik
                                    </Link>
                                    <Link v-if="user?.role === 'mahasiswa'" href="#" class="flex items-center gap-3 px-5 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                                        Download CV
                                    </Link>
                                </div>
                                <div class="py-2">
                                    <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 px-5 py-2.5 text-[14px] font-semibold text-red-600 hover:bg-red-50 text-left transition-colors">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        Keluar
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="mx-auto max-w-[1400px] px-4 py-8 md:px-8">
            <header v-if="title" class="mb-8">
                <div class="flex flex-col gap-4 rounded-[20px] border border-slate-200 bg-white px-6 py-5 shadow-[0_16px_40px_rgba(15,23,42,0.04)] sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">{{ title }}</h1>
                        <p v-if="subtitle" class="mt-1 max-w-3xl text-sm leading-6 text-[#64748B]">{{ subtitle }}</p>
                    </div>
                    <slot name="headerAction" />
                </div>
            </header>

            <slot />
        </main>
    </div>
</template>
