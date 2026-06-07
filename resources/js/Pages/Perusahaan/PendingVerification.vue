<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    status: String,
    name:   String,
    email:  String,
});

const logout = () => router.post(route('logout'));
</script>

<template>
    <Head title="Menunggu Verifikasi Admin — SIKARA" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-amber-50/30 to-orange-50/20 px-4">
        <!-- Background decorations -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-orange-400/10 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-96 w-96 rounded-full bg-blue-400/5 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-lg">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link href="/" class="flex items-center gap-3 transition-transform hover:scale-105">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto" />
                    <span class="text-2xl font-black tracking-tight text-slate-900">SIKARA</span>
                </Link>
            </div>

            <div class="rounded-[2rem] border border-white/60 bg-white/80 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-slate-200/50">

                <!-- Status Icon -->
                <div class="mb-6 flex justify-center">
                    <div class="relative">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-orange-50 border-8 border-amber-50/80 shadow-lg shadow-amber-200/40">
                            <svg class="h-12 w-12 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <!-- Pulse ring -->
                        <div class="absolute inset-0 rounded-full border-4 border-amber-300/40 animate-ping"></div>
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                        Akun Menunggu Verifikasi
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Halo, <span class="font-semibold text-slate-700">{{ name }}</span>! Email Anda sudah terverifikasi.
                        Akun perusahaan Anda sedang ditinjau oleh tim administrator SIKARA.
                    </p>
                </div>

                <!-- Status Badge -->
                <div class="mb-6 flex justify-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-4 py-1.5 text-sm font-bold text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Pending Verifikasi Admin
                    </span>
                </div>

                <!-- Info box -->
                <div class="mb-6 rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50/50 border border-slate-100/80 p-5">
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Proses Verifikasi</p>
                    <div class="space-y-4">
                        <!-- Step 1: Done -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 shadow-sm shadow-emerald-200">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Registrasi Akun</p>
                                <p class="text-xs text-emerald-600 font-medium">Selesai</p>
                            </div>
                        </div>
                        <!-- Step 2: Done -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 shadow-sm shadow-emerald-200">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Verifikasi Email</p>
                                <p class="text-xs text-emerald-600 font-medium">Selesai — {{ email }}</p>
                            </div>
                        </div>
                        <!-- Step 3: Pending -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-400 shadow-sm shadow-amber-200 animate-pulse">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Verifikasi oleh Admin</p>
                                <p class="text-xs text-amber-600 font-medium">Sedang diproses...</p>
                            </div>
                        </div>
                        <!-- Step 4: Locked -->
                        <div class="flex items-start gap-3 opacity-40">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-300">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Akses Penuh Portal Perusahaan</p>
                                <p class="text-xs text-slate-500 font-medium">Akan aktif setelah diverifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notice -->
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <p class="text-xs leading-relaxed text-blue-700">
                        Proses verifikasi biasanya memakan waktu <strong>1–2 hari kerja</strong>. 
                        Anda akan mendapat notifikasi email ketika akun telah diverifikasi.
                    </p>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    <Link
                        :href="route('perusahaan.profile.show')"
                        class="group relative flex h-12 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-600/35 focus:outline-none focus:ring-4 focus:ring-indigo-600/30"
                    >
                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                        <svg class="mr-2 h-4 w-4 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span class="relative z-10">Lengkapi Profil Perusahaan</span>
                    </Link>

                    <button
                        @click="logout"
                        class="flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 transition-all hover:bg-slate-50 hover:border-slate-300 hover:text-red-600"
                    >
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                        </svg>
                        Keluar dari Akun
                    </button>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                Ada pertanyaan? Hubungi kami di
                <a href="mailto:cs@sikara.id" class="font-semibold text-blue-600 hover:underline">cs@sikara.id</a>
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes ping {
    75%, 100% { transform: scale(1.4); opacity: 0; }
}
.animate-ping { animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
</style>
