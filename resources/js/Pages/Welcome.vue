<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 20;
    });
});
</script>

<template>
    <Head title="SIKARA — Sistem Informasi Karir Mahasiswa" />

    <div class="min-h-screen bg-white font-sans text-[#0F172A]">

        <!-- ── Sticky Navbar ── -->
        <header :class="['fixed top-0 left-0 right-0 z-50 transition-all duration-300', isScrolled ? 'bg-white/95 backdrop-blur-md shadow-sm border-b border-[#F1F5F9]' : 'bg-transparent']">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <!-- Logo -->
                <div class="flex items-center gap-2.5">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA Logo" class="h-9 w-auto" />
                    <div>
                        <div class="text-sm font-bold tracking-wider text-[#0F172A]">SIKARA</div>
                        <div class="text-[9px] uppercase tracking-widest text-[#64748B]">Karir Mahasiswa</div>
                    </div>
                </div>

                <!-- Removed standard nav links to make it denser -->

                <!-- Auth Group -->
                <div class="flex items-center gap-4">
                    <div class="hidden items-center gap-3 md:flex border-r border-[#E2E8F0] pr-4">
                        <Link :href="route('peserta')" class="text-sm font-medium text-[#64748B] transition-colors hover:text-[#2563EB]">
                            Peserta
                        </Link>
                        <span class="text-[#CBD5E1]">/</span>
                        <Link :href="route('perusahaan')" class="text-sm font-medium text-[#64748B] transition-colors hover:text-[#10B981]">
                            Perusahaan
                        </Link>
                    </div>
                    <Link :href="route('register')" class="rounded-lg bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-500/20">
                        Daftar Gratis
                    </Link>
                </div>
            </div>
        </header>

        <!-- ── Hero ── -->
        <section class="mx-auto max-w-6xl px-6 pt-36 pb-20 text-center animate-fade-in-up">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-4 py-1.5 shadow-sm">
                <img src="/images/Logo-SIKARA.png" alt="Logo SIKARA" class="h-4 w-auto" />
                <span class="text-xs font-extrabold tracking-wider text-[#0F172A]">SIKARA</span>
            </div>

            <h1 class="mx-auto max-w-4xl text-5xl font-extrabold leading-tight tracking-tight text-[#0F172A] lg:text-6xl">
                Temukan Magang, Bangun Karier<br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2563EB] to-[#10B981]">Mulai dari Sini</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-base text-[#64748B] leading-relaxed">
                SIKARA menjembatani mahasiswa dengan perusahaan impian. Temukan lowongan, kelola profil, 
                dan hasilkan CV profesional secara instan dalam satu platform terpadu.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <Link :href="route('peserta')" class="rounded-xl bg-[#2563EB] px-8 py-3.5 text-sm font-bold text-white transition-all hover:bg-[#1d4ed8] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-500/30">
                    Masuk Portal Peserta
                </Link>
                <Link :href="route('perusahaan')" class="rounded-xl border border-[#E2E8F0] px-8 py-3.5 text-sm font-bold text-[#64748B] transition-all hover:border-[#CBD5E1] hover:text-[#0F172A] hover:-translate-y-0.5">
                    Masuk Portal Perusahaan
                </Link>
            </div>

            <!-- Stats -->
            <div class="mt-20 flex flex-wrap items-center justify-center gap-12 border-t border-[#F1F5F9] pt-12 animate-fade-in delay-200">
                <div class="text-center transform transition-transform hover:scale-105">
                    <div class="text-3xl font-extrabold text-[#0F172A]">500+</div>
                    <div class="mt-1 text-sm font-medium text-[#64748B]">Lowongan Aktif</div>
                </div>
                <div class="h-10 w-px bg-[#E2E8F0]"></div>
                <div class="text-center transform transition-transform hover:scale-105">
                    <div class="text-3xl font-extrabold text-[#0F172A]">1.200+</div>
                    <div class="mt-1 text-sm font-medium text-[#64748B]">Mahasiswa Terdaftar</div>
                </div>
                <div class="h-10 w-px bg-[#E2E8F0]"></div>
                <div class="text-center transform transition-transform hover:scale-105">
                    <div class="text-3xl font-extrabold text-[#0F172A]">200+</div>
                    <div class="mt-1 text-sm font-medium text-[#64748B]">Perusahaan Mitra</div>
                </div>
            </div>
        </section>

        <!-- ── Modul Section ── -->
        <section id="modul" class="border-t border-[#F1F5F9] bg-[#F8FAFC]">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="mb-14 text-center animate-fade-in-up">
                    <p class="mb-2 text-xs font-bold uppercase tracking-widest text-[#2563EB]">Platform Integrasi</p>
                    <h2 class="text-3xl font-bold text-[#0F172A] lg:text-4xl">Modul SIKARA</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm text-[#64748B] leading-relaxed">
                        Dirancang khusus untuk memenuhi kebutuhan masing-masing pihak dalam ekosistem karir kampus.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Mahasiswa -->
                    <div class="group relative rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/5">
                        <div class="absolute top-0 left-0 h-1 w-full rounded-t-2xl bg-[#2563EB] opacity-0 transition-opacity group-hover:opacity-100"></div>
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#EFF6FF] transition-transform group-hover:scale-110">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /><path d="M5 20a7 7 0 0 1 14 0" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Portal Peserta</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#64748B]">
                            Bangun profil kompetitif Anda, temukan ratusan lowongan magang, dan ekspor CV PDF profesional hanya dengan satu klik.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('peserta')" class="inline-flex items-center text-sm font-bold text-[#2563EB] hover:underline">
                                Lihat Portal Peserta
                                <svg class="ml-1.5 h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Perusahaan -->
                    <div class="group relative rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#10B981]/5">
                        <div class="absolute top-0 left-0 h-1 w-full rounded-t-2xl bg-[#10B981] opacity-0 transition-opacity group-hover:opacity-100"></div>
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#ECFDF5] transition-transform group-hover:scale-110">
                            <svg class="h-6 w-6 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 20h16" /><path d="M7 20V6l5-2 5 2v14" />
                                <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Portal Perusahaan</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#64748B]">
                            Akses talenta terbaik kampus. Posting lowongan magang dengan mudah, kelola kandidat, dan lacak progres rekrutmen.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('perusahaan')" class="inline-flex items-center text-sm font-bold text-[#10B981] hover:underline">
                                Lihat Portal Perusahaan
                                <svg class="ml-1.5 h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Admin -->
                    <div class="group relative rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#64748B]/5">
                        <div class="absolute top-0 left-0 h-1 w-full rounded-t-2xl bg-[#64748B] opacity-0 transition-opacity group-hover:opacity-100"></div>
                        <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F1F5F9] transition-transform group-hover:scale-110">
                            <svg class="h-6 w-6 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Administrator</h3>
                        <p class="mt-3 text-sm leading-relaxed text-[#64748B]">
                            Pusat kendali ekosistem. Kelola data kampus, verifikasi kemitraan, dan hasilkan laporan analitik komprehensif.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('login', { role: 'admin' })" class="inline-flex items-center text-sm font-bold text-[#64748B] hover:underline">
                                Masuk sebagai Admin
                                <svg class="ml-1.5 h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Cara Kerja ── -->
        <section id="cara-kerja" class="border-t border-[#F1F5F9]">
            <div class="mx-auto max-w-5xl px-6 py-24">
                <div class="mb-14 text-center">
                    <h2 class="text-3xl font-bold text-[#0F172A]">Proses yang Sangat Mudah</h2>
                </div>
                <div class="grid gap-12 md:grid-cols-3 relative">
                    <div class="hidden md:block absolute top-10 left-[20%] right-[20%] h-0.5 bg-gradient-to-r from-[#E2E8F0] via-[#2563EB] to-[#E2E8F0] opacity-30"></div>
                    
                    <div class="text-center relative z-10 animate-fade-in-up" style="animation-delay: 100ms;">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-lg">
                            <span class="text-xl font-black text-[#2563EB]">1</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Registrasi Akun</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Pilih peran Anda dan daftar secara gratis. Verifikasi email Anda untuk mulai.</p>
                    </div>
                    <div class="text-center relative z-10 animate-fade-in-up" style="animation-delay: 200ms;">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-lg">
                            <span class="text-xl font-black text-[#2563EB]">2</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Bangun Profil</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Lengkapi data akademik, keahlian, dan portofolio Anda agar dilirik perusahaan.</p>
                    </div>
                    <div class="text-center relative z-10 animate-fade-in-up" style="animation-delay: 300ms;">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-lg">
                            <span class="text-xl font-black text-[#2563EB]">3</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Lamar Magang</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Eksplorasi lowongan yang sesuai dan kirimkan lamaran langsung via platform.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA Banner ── -->
        <section class="border-t border-[#F1F5F9] bg-[#0F172A] relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-[#2563EB] opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#10B981] opacity-20 blur-3xl"></div>
            
            <div class="mx-auto max-w-4xl px-6 py-20 text-center relative z-10">
                <h2 class="text-3xl font-extrabold text-white lg:text-4xl">Siap Memulai Karier Anda Bersama SIKARA?</h2>
                <p class="mx-auto mt-5 max-w-xl text-base text-[#94A3B8] leading-relaxed">
                    Bergabung dengan ribuan mahasiswa dan ratusan perusahaan yang telah membuktikan kemudahan rekrutmen di ekosistem kami.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <Link :href="route('register')" class="rounded-xl bg-[#2563EB] px-8 py-3.5 text-sm font-bold text-white transition-all hover:bg-[#1d4ed8] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-500/20">
                        Daftar Gratis Sekarang
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── Footer ── -->
        <footer class="bg-white border-t border-[#F1F5F9]">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between px-6 py-8 md:flex-row">
                <div class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto opacity-80" />
                    <span class="text-sm font-bold tracking-wider text-[#0F172A]">SIKARA</span>
                </div>
                <p class="mt-4 text-xs font-medium text-[#94A3B8] md:mt-0">© 2026 Sistem Informasi Karir Mahasiswa. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </div>
</template>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>
