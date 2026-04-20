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
const unreadCount = computed(() => page.props.notifications.unreadCount ?? 0);

const navItems = computed(() => {
    const items = [
        { label: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
    ];

    if (user.value?.role === 'mahasiswa') {
        items.push(
            { label: 'Profil', href: route('profile.show'), active: route().current('profile.*') },
            { label: 'Magang', href: route('internships.index'), active: route().current('internships.*') },
            { label: 'Notifikasi', href: route('notifications.index'), active: route().current('notifications.*') },
        );
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-[#f3f4f6] text-[#101828]">
        <div class="mx-auto flex min-h-screen max-w-[1440px] flex-col gap-6 px-4 py-6 lg:flex-row">
            <aside class="w-full rounded-[24px] bg-[#111827] p-6 text-white lg:w-72">
                <div class="mb-8">
                    <div class="text-xs uppercase tracking-[0.35em] text-stone-400">SIKARA</div>
                    <h1 class="mt-2 text-2xl font-semibold">Sistem Informasi Karir Mahasiswa</h1>
                </div>

                <nav class="space-y-2">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="flex items-center justify-between rounded-2xl px-4 py-3 transition"
                        :class="item.active ? 'bg-white text-[#111827]' : 'bg-white/5 text-stone-200 hover:bg-white/10'"
                    >
                        <span>{{ item.label }}</span>
                        <span
                            v-if="item.label === 'Notifikasi' && unreadCount"
                            class="rounded-full bg-black px-2 py-0.5 text-xs text-white"
                        >
                            {{ unreadCount }}
                        </span>
                    </Link>
                </nav>

                <div class="mt-8 rounded-2xl bg-white/5 p-4 text-sm text-stone-300">
                    <div class="font-medium text-white">{{ user?.name }}</div>
                    <div class="mt-1 capitalize">{{ user?.role }}</div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="mt-4 w-full rounded-xl bg-white px-4 py-2 text-sm font-medium text-[#111827]"
                    >
                        Keluar
                    </Link>
                </div>
            </aside>

            <div class="flex-1">
                <header class="mb-6 rounded-[24px] border border-[#eaecf0] bg-white px-6 py-5 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <p class="text-sm uppercase tracking-[0.25em] text-[#98a2b3]">Sprint 1</p>
                    <h2 class="mt-1 text-3xl font-semibold tracking-[-0.03em] text-[#101828]">{{ title }}</h2>
                    <p v-if="subtitle" class="mt-2 max-w-3xl text-sm leading-6 text-[#667085]">{{ subtitle }}</p>
                </header>

                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
