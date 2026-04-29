<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    activeRole: String, // 'peserta' or 'perusahaan'
    loginRole: { type: String, default: 'mahasiswa' },
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-[#0F172A]">
        <!-- ── Top Bar ── -->
        <div class="bg-[#1E293B] text-white">
            <div class="mx-auto flex max-w-7xl justify-end px-6 py-2.5 text-xs font-semibold tracking-wide">
                <div class="flex gap-7">
                    <Link :href="route('peserta')" :class="['transition-colors hover:text-white', activeRole === 'peserta' ? 'text-white' : 'text-[#94A3B8]']">Peserta</Link>
                    <Link :href="route('perusahaan')" :class="['transition-colors hover:text-white', activeRole === 'perusahaan' ? 'text-white' : 'text-[#94A3B8]']">Perusahaan</Link>
                    <Link :href="route('tentang')" :class="['transition-colors hover:text-white', activeRole === 'tentang' ? 'text-white' : 'text-[#94A3B8]']">Tentang SIKARA</Link>
                </div>
            </div>
        </div>

        <!-- ── Main Navbar ── -->
        <header class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white/95 backdrop-blur-md shadow-sm transition-all">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                    <span class="text-xl font-black tracking-tight text-[#0F172A]">SIKARA</span>
                </Link>

                <!-- Navigation Slots -->
                <nav class="hidden flex-1 items-center justify-end gap-8 pr-8 md:flex">
                    <slot name="navigation" />
                </nav>

                <!-- Login Button Only -->
                <div>
                    <Link :href="route('login', { role: loginRole })" class="flex items-center gap-2 rounded-lg bg-[#1E293B] px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-[#0F172A] hover:shadow-md">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Masuk
                    </Link>
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
