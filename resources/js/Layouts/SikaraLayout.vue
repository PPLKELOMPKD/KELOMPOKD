<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

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
const user = computed(() => page.props.auth?.user ?? null);
const showUserMenu = ref(false);

const navItems = computed(() => [
    { label: 'Cari Lowongan', href: route('lowongan'), active: route().current('lowongan') },
    { label: 'List Perusahaan', href: route('perusahaan-list'), active: route().current('perusahaan-list') },
    { label: 'LMS', href: route('lms'), active: route().current('lms') },
    { label: 'Pelatihan', href: route('event'), active: route().current('event') },
    { label: 'Generate CV', href: route('generate-cv'), active: route().current('generate-cv') },
]);

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
    <div class="min-h-screen bg-[#F8FAFC] font-sans text-[#0F172A]">
        <div class="bg-[#1E293B] text-white">
            <div class="mx-auto flex max-w-7xl justify-end px-6 py-2.5 text-xs font-semibold tracking-wide">
                <div class="flex gap-7">
                    <Link :href="route('peserta')" class="text-white transition-colors hover:text-white">Peserta</Link>
                    <Link :href="route('perusahaan')" class="text-[#94A3B8] transition-colors hover:text-white">Perusahaan</Link>
                    <Link :href="route('tentang')" class="text-[#94A3B8] transition-colors hover:text-white">Tentang SIKARA</Link>
                </div>
            </div>
        </div>

        <header class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white/95 shadow-sm backdrop-blur-md transition-all">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                    <span class="text-xl font-black tracking-tight text-[#0F172A]">SIKARA</span>
                </Link>

                <nav class="hidden flex-1 items-center justify-end gap-8 pr-8 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="text-sm font-semibold transition-colors"
                        :class="item.active ? 'text-[#2563EB]' : 'text-[#64748B] hover:text-[#2563EB]'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="relative">
                    <button
                        type="button"
                        class="flex items-center gap-3 rounded-xl bg-[#F1F5F9] px-3 py-2 transition-all hover:bg-[#EAF1F8]"
                        @click="toggleUserMenu"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#2563EB] to-[#60A5FA] text-sm font-bold text-white shadow-md">
                            {{ user?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div class="hidden text-left sm:block">
                            <p class="text-sm font-semibold leading-tight text-[#0F172A]">{{ user?.name }}</p>
                            <p class="text-xs text-[#64748B]">Peserta</p>
                        </div>
                        <svg class="h-4 w-4 text-[#94A3B8] transition-transform duration-200" :class="{ 'rotate-180': showUserMenu }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl border border-[#E2E8F0] bg-white py-2 shadow-xl shadow-black/10" @mouseleave="closeUserMenu">
                            <div class="border-b border-[#F1F5F9] px-4 py-3">
                                <p class="text-sm font-semibold text-[#0F172A]">{{ user?.name }}</p>
                                <p class="mt-0.5 truncate text-xs text-[#64748B]">{{ user?.email }}</p>
                            </div>
                            <Link :href="route('profile.show')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] transition-colors hover:bg-[#F8FAFC]">
                                <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
                                Profil Saya
                            </Link>
                            <Link :href="route('notifications.index')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] transition-colors hover:bg-[#F8FAFC]">
                                <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                Notifikasi
                            </Link>
                            <Link :href="route('internships.index')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] transition-colors hover:bg-[#F8FAFC]">
                                <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path d="M4 7h16v12H4z"/><path d="M4 12h16"/></svg>
                                Lamaran Saya
                            </Link>
                            <Link :href="route('cv.download')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#344054] transition-colors hover:bg-[#F8FAFC]">
                                <svg class="h-4 w-4 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M3 15h6"/><path d="M3 18h6"/></svg>
                                Download CV
                            </Link>
                            <div class="mt-1 border-t border-[#F1F5F9] pt-1">
                                <button type="button" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50" @click="logout">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Keluar
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 md:px-8 md:py-8">
            <header class="mb-6 flex flex-col gap-4 rounded-[20px] border border-slate-200 bg-white px-6 py-5 shadow-[0_16px_40px_rgba(15,23,42,0.04)] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-1 max-w-3xl text-sm leading-6 text-[#64748B]">{{ subtitle }}</p>
                </div>
                <slot name="headerAction" />
            </header>

            <slot />
        </main>
    </div>
</template>
