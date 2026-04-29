<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen opacity-0 animate-fade-in">

        <!-- ── Left Branding Panel ── -->
        <div class="hidden lg:flex lg:w-[460px] xl:w-[500px] flex-shrink-0 flex-col border-r border-[#E2E8F0] bg-white px-12 py-10">

            <!-- Logo -->
            <Link :href="route('home')" class="flex items-center gap-3">
                <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto" />
                <div>
                    <div class="text-base font-bold tracking-wider text-[#0F172A]">SIKARA</div>
                    <div class="text-[10px] uppercase tracking-widest text-[#64748B]">Sistem Informasi Karir</div>
                </div>
            </Link>

            <!-- Center content with Illustration -->
            <div class="my-auto">
                <!-- Illustration -->
                <div class="mb-8 flex justify-center">
                    <img src="/images/auth-illustration.png" alt="Ilustrasi SIKARA" class="h-48 w-auto object-contain drop-shadow-sm" />
                </div>

                <h2 class="text-2xl font-bold leading-snug text-[#0F172A]">
                    Satu Platform untuk<br />Karier Anda
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-[#64748B]">
                    Temukan peluang, bangun profil profesional, dan ekspor CV siap kirim — semua dalam SIKARA.
                </p>

                <!-- Feature list -->
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#2563EB]/10">
                            <svg class="h-3 w-3 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-[#0F172A]">Dashboard Profil & Skill Terintegrasi</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#10B981]/10">
                            <svg class="h-3 w-3 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-[#0F172A]">Akses 500+ Lowongan dari Mitra</span>
                    </li>
                </ul>
            </div>

            <!-- Bottom -->
            <div class="text-xs text-[#94A3B8]">© 2026 SIKARA — Sistem Informasi Karir Mahasiswa</div>
        </div>

        <!-- ── Right Form Panel ── -->
        <div class="flex flex-1 flex-col items-center justify-center bg-[#F8FAFC] px-4 py-10 sm:px-8">
            <!-- Mobile logo -->
            <Link :href="route('home')" class="mb-8 flex items-center gap-2.5 lg:hidden">
                <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                <span class="text-sm font-bold tracking-wider text-[#0F172A]">SIKARA</span>
            </Link>

            <div class="w-full max-w-[420px] rounded-2xl border border-[#E2E8F0] bg-white px-8 py-8 shadow-sm animate-slide-up">
                <slot />
            </div>
        </div>
    </div>
</template>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-fade-in {
    animation: fadeIn 0.6s ease-out forwards;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slideUp 0.6s ease-out forwards;
}
</style>
