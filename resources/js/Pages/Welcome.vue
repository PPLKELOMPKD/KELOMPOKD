<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="SIKARA" />

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_#e7dcc8,_#f5f1e8_35%,_#efe9dc_70%)] text-stone-900">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col justify-between px-6 py-8">
            <header class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.45em] text-stone-500">SIKARA</div>
                    <div class="mt-2 text-lg font-semibold">Sistem Informasi Karir Mahasiswa</div>
                </div>
                <Link :href="route('login')" class="rounded-full bg-black px-5 py-3 text-sm font-medium text-white">
                    Masuk
                </Link>
            </header>

            <main class="grid items-center gap-10 py-12 lg:grid-cols-[1.2fr_0.8fr]">
                <section>
                    <p class="text-sm uppercase tracking-[0.4em] text-stone-500">Sprint 1</p>
                    <h1 class="mt-5 max-w-4xl text-5xl font-semibold leading-tight lg:text-7xl">
                        Bangun karier mahasiswa dari profil, skill, dan peluang magang yang terkelola.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-600">
                        SIKARA menyatukan dashboard mahasiswa, lowongan magang, notifikasi, dan CV otomatis
                        dalam satu pengalaman web yang rapi untuk kampus dan mitra.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link :href="route('login')" class="rounded-full bg-black px-6 py-3 text-sm font-medium text-white">
                            Buka Login
                        </Link>
                        <a href="#modul" class="rounded-full border border-stone-300 bg-white px-6 py-3 text-sm font-medium text-stone-700">
                            Lihat Modul
                        </a>
                    </div>
                </section>

                <section class="rounded-[36px] bg-stone-900 p-6 text-white shadow-2xl">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
                        <div class="rounded-[28px] bg-white/10 p-5">
                            <div class="text-sm text-stone-300">Dashboard Mahasiswa</div>
                            <div class="mt-4 text-3xl font-semibold">Profil + Lamaran</div>
                        </div>
                        <div class="rounded-[28px] bg-white p-5 text-stone-900">
                            <div class="text-sm text-stone-500">Lowongan Aktif</div>
                            <div class="mt-4 text-3xl font-semibold">Internship Ready</div>
                        </div>
                        <div class="rounded-[28px] bg-white/10 p-5">
                            <div class="text-sm text-stone-300">CV Otomatis</div>
                            <div class="mt-4 text-3xl font-semibold">PDF Generated</div>
                        </div>
                    </div>
                </section>
            </main>

            <section id="modul" class="grid gap-4 pb-8 md:grid-cols-3">
                <div class="rounded-[28px] bg-white p-6 shadow-sm">
                    <div class="text-sm uppercase tracking-[0.3em] text-stone-400">Mahasiswa</div>
                    <p class="mt-4 text-lg font-semibold">Profil, skill, lowongan, lamaran, dan CV otomatis.</p>
                </div>
                <div class="rounded-[28px] bg-white p-6 shadow-sm">
                    <div class="text-sm uppercase tracking-[0.3em] text-stone-400">Perusahaan</div>
                    <p class="mt-4 text-lg font-semibold">Akses login dan dashboard stub untuk ekspansi sprint berikutnya.</p>
                </div>
                <div class="rounded-[28px] bg-white p-6 shadow-sm">
                    <div class="text-sm uppercase tracking-[0.3em] text-stone-400">Admin</div>
                    <p class="mt-4 text-lg font-semibold">Akses login dan dashboard stub untuk modul kampus dan analitik.</p>
                </div>
            </section>
        </div>
    </div>
</template>
