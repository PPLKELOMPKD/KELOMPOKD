<script setup>
import { computed } from 'vue';
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

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard'), icon: 'dashboard' },
    ];

    if (user.value?.role === 'mahasiswa') {
        items.push(
            { label: 'Profil Saya', href: route('profile.show'), active: route().current('profile.*'), icon: 'profile' },
            { label: 'Lowongan', href: route('internships.index'), active: route().current('internships.*'), icon: 'briefcase' },
            { label: 'Pelatihan', href: route('lms'), active: route().current('lms'), icon: 'school' },
            { label: 'Riwayat', href: '#', active: false, icon: 'history' },
            { label: 'Notifikasi', href: route('notifications.index'), active: route().current('notifications.*'), icon: 'bell' },
        );
    }

    return items;
});

const footerItems = [
    { label: 'Pengaturan', href: '#', icon: 'settings' },
    { label: 'Bantuan', href: '#', icon: 'help' },
];
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] font-sans text-[#0F172A]">
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white py-6 md:flex">
            <div class="px-6">
                <div class="text-3xl font-extrabold tracking-tight text-[#2563EB]">SIKARA</div>
                <p class="mt-1 text-sm text-[#64748B]">Student Portal</p>
            </div>

            <nav class="mt-10 flex-1 space-y-1 px-3">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-full px-5 py-3 text-sm font-medium transition"
                    :class="item.active ? 'bg-[#2563EB] text-white shadow-[0_10px_20px_rgba(37,99,235,0.16)]' : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]'"
                >
                    <svg v-if="item.icon === 'dashboard'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M4 4h6v6H4z" />
                        <path d="M14 4h6v6h-6z" />
                        <path d="M4 14h6v6H4z" />
                        <path d="M14 14h6v6h-6z" />
                    </svg>
                    <svg v-else-if="item.icon === 'profile'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20a8 8 0 0 1 16 0" />
                    </svg>
                    <svg v-else-if="item.icon === 'briefcase'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1" />
                        <path d="M4 7h16v12H4z" />
                        <path d="M4 12h16" />
                    </svg>
                    <svg v-else-if="item.icon === 'school'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="m3 8 9-5 9 5-9 5z" />
                        <path d="M7 11v5c0 1.7 2.2 3 5 3s5-1.3 5-3v-5" />
                    </svg>
                    <svg v-else-if="item.icon === 'history'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M4 12a8 8 0 1 0 2.35-5.65" />
                        <path d="M4 4v5h5" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                    <svg v-else class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                        <path d="M10 21h4" />
                    </svg>
                    <span>{{ item.label }}</span>
                    <span
                        v-if="item.label === 'Notifikasi' && unreadCount"
                        class="ml-auto rounded-full bg-white/20 px-2 py-0.5 text-xs"
                    >
                        {{ unreadCount }}
                    </span>
                </Link>
            </nav>

            <div class="border-t border-slate-200 px-3 pt-4">
                <a
                    v-for="item in footerItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-full px-5 py-3 text-sm font-medium text-[#64748B] transition hover:bg-slate-50 hover:text-[#0F172A]"
                >
                    <svg v-if="item.icon === 'settings'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.4 1.08V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 8.6 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.08-.4H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 8.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6A1.65 1.65 0 0 0 10.4 2.92V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15.4 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.23.38.6.65 1 .76.18.05.36.07.54.07H21a2 2 0 1 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15Z" />
                    </svg>
                    <svg v-else class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.1 9a3 3 0 1 1 5.8 1c-.4 1.2-1.9 1.7-2.5 2.7-.2.3-.3.7-.3 1.1" />
                        <path d="M12 17h.01" />
                    </svg>
                    {{ item.label }}
                </a>
            </div>
        </aside>

        <div class="min-h-screen md:pl-64">
            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-[#F8FAFC]/90 px-4 py-4 backdrop-blur md:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 rounded-[20px] border border-slate-200 bg-white px-6 py-5 shadow-[0_16px_40px_rgba(15,23,42,0.04)] sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">{{ title }}</h1>
                        <p v-if="subtitle" class="mt-1 max-w-3xl text-sm leading-6 text-[#64748B]">{{ subtitle }}</p>
                    </div>
                    <slot name="headerAction" />
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 md:px-8 md:py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
