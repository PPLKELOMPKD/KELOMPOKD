<script setup>
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

defineProps({
    activeRole: String, // 'peserta' or 'perusahaan'
    loginRole: { type: String, default: 'mahasiswa' },
});

const page = usePage();
const user = page.props.auth?.user ?? null;
const showUserMenu = ref(false);

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const closeUserMenu = () => {
    showUserMenu.value = false;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-[#0F172A]">
        <!-- ── Main Navbar ── -->
        <header class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white/95 backdrop-blur-md shadow-sm transition-all">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <!-- Logo -->
                <Link :href="route('peserta')" class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                    <span class="text-xl font-black tracking-tight text-[#0F172A]">SIKARA</span>
                </Link>

                <!-- Navigation Slots -->
                <nav class="hidden flex-1 items-center justify-end gap-8 pr-8 md:flex">
                    <slot name="navigation" />
                </nav>

                <!-- User Menu (Authenticated) or Login Button (Guest) -->
                <div class="relative">
                    <template v-if="user">
                        <button
                            @click="toggleUserMenu"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 transition-all hover:bg-[#F1F5F9]"
                        >
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] text-sm font-bold text-white shadow-md">
                                {{ user.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-semibold text-[#0F172A] leading-tight">{{ user.name }}</p>
                                <p class="text-xs text-[#64748B]">Peserta</p>
                            </div>
                            <svg class="h-4 w-4 text-[#94A3B8] transition-transform" :class="{'rotate-180': showUserMenu}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <!-- Dropdown -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl border border-[#E2E8F0] bg-white py-2 shadow-xl shadow-black/10" @mouseleave="closeUserMenu">
                                <div class="px-4 py-3 border-b border-[#F1F5F9]">
                                    <p class="text-sm font-semibold text-[#0F172A]">{{ user.name }}</p>
                                    <p class="text-xs text-[#64748B] mt-0.5">{{ user.email }}</p>
                                </div>
                                <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] hover:bg-[#F8FAFC] transition-colors">
                                    <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
                                    Profil Saya
                                </Link>
                                <Link :href="route('notifications.index')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] hover:bg-[#F8FAFC] transition-colors">
                                    <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                    Notifikasi
                                </Link>
                                <Link :href="route('applications.index')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] hover:bg-[#F8FAFC] transition-colors">
                                    <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path d="M4 7h16v12H4z"/><path d="M4 12h16"/></svg>
                                    Tracking Lamaran
                                </Link>
                                <Link :href="route('cv.download')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] hover:bg-[#F8FAFC] transition-colors">
                                    <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M3 15h6"/><path d="M3 18h6"/></svg>
                                    Download CV
                                </Link>
                                <div class="border-t border-[#F1F5F9] mt-1 pt-1">
                                    <button @click="logout" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </template>

                    <template v-else>
                        <Link :href="route('login', { role: loginRole })" class="flex items-center gap-2 rounded-lg bg-[#1E293B] px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-[#0F172A] hover:shadow-md">
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
        <main class="animate-fade-in">
            <slot />
        </main>
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

