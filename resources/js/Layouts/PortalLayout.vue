<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

defineProps({
    activeRole: String, // 'peserta' or 'perusahaan'
    loginRole: { type: String, default: 'mahasiswa' },
});

const page = usePage();
const user = page.props.auth?.user ?? null;
const showUserMenu = ref(false);
const profilePhotoUrl = computed(() => user?.profile_photo_url ?? user?.photo ?? null);

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const closeUserMenu = () => {
    showUserMenu.value = false;
};

const logout = () => {
    router.post(route('logout'));
};

const homeRoute = computed(() => {
    if (!user) return '/';
    if (user.role === 'perusahaan') return route('perusahaan.dashboard');
    if (user.role === 'admin') return route('dashboard');
    return route('peserta');
});
</script>

<template>
    <div class="flex flex-col min-h-screen bg-[#F8FAFC] font-sans text-[#0F172A]">
        <!-- ── Main Navbar ── -->
        <header class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white/80 backdrop-blur-xl shadow-sm transition-all">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <!-- Logo -->
                <Link :href="homeRoute" class="flex items-center gap-3 transition-transform hover:scale-105">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                    <span class="text-xl font-black tracking-tight text-[#0F172A]">SIKARA</span>
                </Link>

                <!-- Navigation Slots -->
                <nav class="hidden flex-1 items-center justify-end gap-8 pr-8 md:flex">
                    <slot name="navigation">
                        <Link :href="route('lowongan')" :class="route().current('lowongan') ? 'text-sm font-semibold text-[#2563EB]' : 'text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]'">Cari Lowongan</Link>
                        <Link :href="route('perusahaan-list')" :class="route().current('perusahaan-list') ? 'text-sm font-semibold text-[#2563EB]' : 'text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]'">Daftar Perusahaan</Link>
                        <Link :href="route('lms')" :class="route().current('lms') ? 'text-sm font-semibold text-[#2563EB]' : 'text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]'">LMS</Link>
                        <Link :href="route('event')" :class="route().current('event') ? 'text-sm font-semibold text-[#2563EB]' : 'text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]'">Pelatihan</Link>
                        <Link :href="route('generate-cv')" :class="route().current('generate-cv') ? 'text-sm font-semibold text-[#2563EB]' : 'text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]'">Buat CV</Link>
                    </slot>
                </nav>

                <!-- User Menu (Authenticated) or Login Button (Guest) -->
                <div class="relative">
                    <template v-if="user">
                        <button
                            @click="toggleUserMenu"
                            class="flex items-center gap-3 rounded-full border border-transparent px-2 py-1.5 transition-all hover:bg-white hover:border-[#E2E8F0] hover:shadow-sm"
                        >
                            <div v-if="profilePhotoUrl" class="h-9 w-9 overflow-hidden rounded-full border border-[#E2E8F0] shadow-sm">
                                <img :src="profilePhotoUrl" alt="Profile" class="h-full w-full object-cover" />
                            </div>
                            <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] text-sm font-bold text-white shadow-md">
                                {{ user.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div class="hidden sm:block text-left pr-2">
                                <p class="text-sm font-bold text-[#0F172A] leading-tight">{{ user.name }}</p>
                                <p class="text-xs font-medium text-[#64748B] capitalize">{{ user.role }}</p>
                            </div>
                            <svg class="h-4 w-4 text-[#94A3B8] transition-transform duration-300" :class="{'rotate-180': showUserMenu}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <!-- Dropdown -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-2 scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-2 scale-95"
                        >
                            <div v-if="showUserMenu" class="absolute right-0 mt-3 w-64 origin-top-right rounded-2xl border border-[#E2E8F0] bg-white p-2 shadow-2xl shadow-blue-900/5 z-50" @mouseleave="closeUserMenu">
                                <div class="px-3 py-3 mb-2 rounded-xl bg-[#F8FAFC]">
                                    <p class="text-sm font-bold text-[#0F172A] truncate">{{ user.name }}</p>
                                    <p class="text-xs font-medium text-[#64748B] truncate mt-0.5">{{ user.email }}</p>
                                </div>
                                <div class="space-y-1">
                                    <template v-if="user.role === 'perusahaan' || user.role === 'admin'">
                                        <Link :href="user.role === 'perusahaan' ? route('perusahaan.dashboard') : route('dashboard')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] transition-colors">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                                            Dashboard
                                        </Link>
                                    </template>
                                    <template v-else>
                                        <Link :href="route('profile.show')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] transition-colors">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
                                            Profil Saya
                                        </Link>
                                        <Link :href="route('notifications.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] transition-colors">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                            Notifikasi
                                        </Link>
                                        <Link :href="route('applications.index')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] transition-colors">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path d="M4 7h16v12H4z"/><path d="M4 12h16"/></svg>
                                            Lamaran Saya
                                        </Link>
                                        <Link :href="route('cv.download')" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#344054] hover:bg-[#EFF6FF] hover:text-[#2563EB] transition-colors">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M3 15h6"/><path d="M3 18h6"/></svg>
                                            Unduh CV
                                        </Link>
                                    </template>
                                </div>
                                <div class="border-t border-[#E2E8F0] mt-2 pt-2">
                                    <button @click="logout" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </template>

                    <template v-else>
                        <Link :href="route('login', { role: loginRole })" class="flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-[#1D4ED8] hover:shadow-lg hover:shadow-blue-500/25">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            Masuk
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- ── Content ── -->
        <main class="flex-1 animate-fade-in flex flex-col">
            <slot />
        </main>

        <!-- ── Footer (Global untuk Peserta) ── -->
        <footer class="mt-auto border-t border-[#E2E8F0] bg-[#F8FAFC] py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12">
                    <!-- Brand -->
                    <div class="col-span-1 md:col-span-2">
                        <Link :href="homeRoute" class="flex items-center gap-3 mb-4">
                            <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-7 w-auto" />
                            <span class="text-lg font-black tracking-tight text-[#0F172A]">SIKARA</span>
                        </Link>
                        <h3 class="font-bold text-[#0F172A] text-base mb-1">SIKARA Indonesia</h3>
                        <p class="text-sm font-medium text-[#64748B]">
                            Email: <a href="mailto:cs@sikara.id" class="text-[#2563EB] hover:text-[#1D4ED8] hover:underline transition-colors">cs@sikara.id</a>
                        </p>
                    </div>

                    <!-- Menu Peserta -->
                    <div>
                        <h3 class="font-bold text-[#0F172A] text-sm tracking-wider uppercase mb-4">Peserta</h3>
                        <ul class="space-y-3">
                            <li><Link :href="route('lowongan')" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Cari Lowongan</Link></li>
                            <li><Link :href="route('event')" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Cari Event</Link></li>
                            <li><Link :href="route('perusahaan-list')" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Daftar Perusahaan</Link></li>
                        </ul>
                    </div>

                    <!-- Menu Tentang SIKARA -->
                    <div>
                        <h3 class="font-bold text-[#0F172A] text-sm tracking-wider uppercase mb-4">Tentang SIKARA</h3>
                        <ul class="space-y-3">
                            <li><Link href="#" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Panduan</Link></li>
                            <li><Link href="#" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Pusat Informasi</Link></li>
                            <li><Link :href="route('tentang')" class="text-sm font-medium text-[#64748B] hover:text-[#2563EB] transition-colors">Tentang Kami</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 border-t border-[#E2E8F0] pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm font-medium text-[#94A3B8]">© 2026 SIKARA. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
</style>
