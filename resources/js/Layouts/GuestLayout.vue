<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-slate-50 opacity-0 animate-fade-in font-sans selection:bg-indigo-500/30">

        <!-- ── Left Branding Panel ── -->
        <div class="hidden lg:flex lg:w-[500px] xl:w-[560px] flex-shrink-0 flex-col relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-12 py-12 border-r border-slate-200/60 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-10">
            
            <!-- Abstract background elements -->
            <div class="absolute -top-32 -left-32 h-[500px] w-[500px] rounded-full bg-blue-400/10 blur-[80px]"></div>
            <div class="absolute bottom-0 right-0 h-[400px] w-[400px] rounded-full bg-indigo-400/10 blur-[80px]"></div>
            <div class="absolute top-1/2 left-1/2 h-[300px] w-[300px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-400/5 blur-[60px]"></div>

            <!-- Content -->
            <div class="relative z-10 flex h-full flex-col">
                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center gap-3 transition-transform duration-300 hover:scale-105 origin-left w-max">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto drop-shadow-md" />
                    <div>
                        <div class="text-xl font-black tracking-widest text-slate-900">{{ $page.props.global_settings?.app_name || 'SIKARA' }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-600">Sistem Informasi Karir</div>
                    </div>
                </Link>

                <!-- Center content with Illustration -->
                <div class="my-auto pt-10">
                    <!-- Illustration -->
                    <div class="mb-12 flex justify-center relative group">
                         <!-- Decorative element -->
                         <div class="absolute inset-0 bg-gradient-to-tr from-indigo-100 to-blue-50 rounded-[2.5rem] transform rotate-3 scale-105 transition-all duration-500 group-hover:rotate-6 group-hover:scale-110 opacity-70"></div>
                         <div class="absolute inset-0 bg-white/40 backdrop-blur-sm rounded-[2.5rem] transform rotate-3 scale-105 border border-white"></div>
                        <img src="/images/auth-illustration.png" alt="Ilustrasi SIKARA" class="relative z-10 h-64 w-auto object-contain drop-shadow-xl transition-transform duration-500 group-hover:scale-105 group-hover:-translate-y-2" />
                    </div>

                    <h2 class="text-[2rem] font-extrabold leading-[1.2] text-slate-900 mb-5 tracking-tight">
                        Satu Platform untuk<br /><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">Karier Gemilang Anda</span>
                    </h2>
                    <p class="text-[15px] leading-relaxed text-slate-600 max-w-md font-medium">
                        Temukan peluang tak terbatas, bangun profil profesional yang menonjol, dan ekspor CV siap kirim — semua dalam ekosistem SIKARA.
                    </p>

                    <!-- Feature list -->
                    <ul class="mt-10 space-y-5">
                        <li class="flex items-center gap-4 group">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200/60 transition-all duration-300 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-md group-hover:scale-110">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span class="text-[15px] font-bold text-slate-700 group-hover:text-indigo-900 transition-colors">Dashboard Profil & Skill Terintegrasi</span>
                        </li>
                        <li class="flex items-center gap-4 group">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-sm ring-1 ring-slate-200/60 transition-all duration-300 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-md group-hover:scale-110">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span class="text-[15px] font-bold text-slate-700 group-hover:text-emerald-900 transition-colors">Akses 500+ Lowongan dari Mitra</span>
                        </li>
                    </ul>
                </div>

                <!-- Bottom -->
                <div class="mt-auto text-xs font-semibold text-slate-400">{{ $page.props.global_settings?.footer_text || '© 2026 SIKARA — Sistem Informasi Karir Mahasiswa' }}</div>
            </div>
        </div>

        <!-- ── Right Form Panel ── -->
        <div class="flex flex-1 flex-col items-center justify-center relative overflow-hidden bg-slate-50 px-4 py-10 sm:px-8">
            <!-- Subtle background mesh/gradient on right side -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#f1f5f9_1px,transparent_1px),linear-gradient(to_bottom,#f1f5f9_1px,transparent_1px)] bg-[size:24px_24px] opacity-60"></div>
            
            <!-- Mobile logo -->
            <Link :href="route('home')" class="relative z-10 mb-8 flex items-center gap-3 lg:hidden">
                <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto drop-shadow-md" />
                <div>
                    <div class="text-xl font-black tracking-widest text-slate-900">{{ $page.props.global_settings?.app_name || 'SIKARA' }}</div>
                </div>
            </Link>

            <div class="relative z-10 w-full max-w-[480px] animate-slide-up">
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

