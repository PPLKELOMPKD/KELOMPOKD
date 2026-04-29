<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { onMounted, ref } from 'vue';

const isVisible = ref(false);

onMounted(() => {
    isVisible.value = true;
});
</script>

<template>
    <Head title="Tentang SIKARA | Mendukung SDG 4 Pendidikan Indonesia" />

    <PortalLayout activeRole="tentang">
        <!-- ── Hero Section ── -->
        <section class="relative bg-[#0F172A] overflow-hidden min-h-[90vh] flex items-center">
            <!-- Animated Background Elements -->
            <div class="absolute -top-40 -right-40 h-[600px] w-[600px] rounded-full bg-[#2563EB] opacity-20 blur-[120px] animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 h-[600px] w-[600px] rounded-full bg-[#10B981] opacity-20 blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
            
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-10"></div>

            <div class="mx-auto max-w-7xl px-6 py-24 relative z-10 w-full transition-all duration-1000 transform" :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'">
                <div class="flex flex-col lg:flex-row items-center gap-16">
                    <div class="lg:w-[55%] text-center lg:text-left">
                        <div class="mb-8 inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-5 py-2 backdrop-blur-md shadow-lg shadow-blue-500/10 hover:bg-white/20 transition-all cursor-default">
                            <img src="/images/Logo-SIKARA.png" alt="Logo SIKARA" class="h-5 w-auto brightness-200 drop-shadow-md" />
                            <div class="w-1 h-1 rounded-full bg-white/50"></div>
                            <span class="text-xs font-bold text-white tracking-widest uppercase">Platform Karir Mahasiswa</span>
                        </div>
                        
                        <h1 class="text-5xl font-black leading-[1.1] text-white lg:text-6xl xl:text-7xl mb-8 tracking-tight">
                            Akselerasi Karir, <br/>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#3B82F6] via-[#60A5FA] to-[#34D399] drop-shadow-sm">
                                Bangun Masa Depan Indonesia
                            </span>
                        </h1>
                        
                        <p class="text-lg text-[#94A3B8] leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0">
                            SIKARA lebih dari sekadar portal lowongan. Kami adalah ekosistem terpadu yang menjembatani kesenjangan antara dunia akademik dan profesional, mempersiapkan talenta muda Indonesia untuk bersaing di panggung global.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link :href="route('register')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#2563EB] to-[#3B82F6] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:shadow-blue-500/50 hover:-translate-y-1">
                                Bergabung Sekarang
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>
                            <a href="#sdg4" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-8 py-4 text-sm font-bold text-white backdrop-blur-sm transition-all hover:bg-white/10 hover:-translate-y-1">
                                Pelajari Visi Kami
                            </a>
                        </div>
                    </div>
                    
                    <div class="lg:w-[45%] relative hidden md:block">
                        <!-- Decorative ring -->
                        <div class="absolute inset-0 border-2 border-dashed border-white/20 rounded-full animate-spin-slow scale-110"></div>
                        
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl p-6 group transform transition-transform hover:scale-[1.02] duration-500">
                             <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A] to-transparent opacity-60 z-10"></div>
                             <img src="/images/about-hero-illustration.png" alt="Hero Illustration" class="w-full h-auto rounded-xl object-cover relative z-0 group-hover:scale-105 transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1000&auto=format&fit=crop'" />
                             
                             <div class="absolute bottom-6 left-6 right-6 z-20 flex justify-between items-end">
                                 <div>
                                     <div class="text-white font-black text-2xl">10k+</div>
                                     <div class="text-blue-200 text-sm font-medium">Mahasiswa Terdaftar</div>
                                 </div>
                                 <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                                     <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                 </div>
                             </div>
                        </div>
                        
                        <!-- Floating badges -->
                        <div class="absolute -right-8 top-12 bg-white rounded-xl shadow-xl p-4 flex items-center gap-4 animate-bounce-slow">
                            <div class="w-10 h-10 rounded-lg bg-[#ECFDF5] flex items-center justify-center text-[#10B981]">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-[#0F172A]">Lowongan Aktif</div>
                                <div class="text-xs text-[#64748B]">Update setiap hari</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block w-[calc(100%+1.3px)] h-[80px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,111.47,192.59,96.5,238.13,85.36,281.44,71.62,321.39,56.44Z" class="fill-white"></path>
                </svg>
            </div>
        </section>

        <!-- ── SDG 4 Section ── -->
        <section id="sdg4" class="bg-white py-24 relative">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EFF6FF] text-[#2563EB] text-sm font-bold tracking-wide uppercase mb-4 border border-blue-100">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                        Komitmen Global
                    </div>
                    <h2 class="text-4xl font-black text-[#0F172A] lg:text-5xl leading-tight mb-6">
                        Mewujudkan <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#10B981] to-[#059669]">SDG 4</span> di Indonesia
                    </h2>
                    <p class="text-lg text-[#64748B] leading-relaxed">
                        Sustainable Development Goal 4 (Pendidikan Berkualitas) bertujuan memastikan pendidikan yang inklusif dan merata serta mempromosikan kesempatan belajar sepanjang hayat. SIKARA hadir sebagai katalisator untuk mewujudkan visi tersebut di Indonesia.
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#2563EB] to-[#10B981] rounded-[2.5rem] transform rotate-2 group-hover:rotate-6 transition-transform duration-500 opacity-20 blur-lg"></div>
                        <div class="absolute inset-0 bg-gradient-to-tr from-[#2563EB] to-[#10B981] rounded-[2.5rem] transform -rotate-2 group-hover:-rotate-3 transition-transform duration-500 opacity-10"></div>
                        
                        <div class="relative bg-white p-2 rounded-[2.5rem] shadow-xl border border-[#E2E8F0]">
                            <img src="/images/sdg4-illustration.png" alt="SDG 4 Quality Education" class="w-full h-auto rounded-[2rem] object-cover" onerror="this.src='https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=1000&auto=format&fit=crop'" />
                            
                            <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl border border-[#F1F5F9] max-w-xs animate-float hidden md:block">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="w-12 h-12 rounded-full bg-[#E53D3A] flex items-center justify-center text-white font-bold text-xl">4</div>
                                    <div class="font-bold text-[#0F172A] leading-tight text-sm">Quality<br/>Education</div>
                                </div>
                                <div class="h-1 w-full bg-gradient-to-r from-[#E53D3A] to-[#C41C1A] rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-8">
                        <div class="flex flex-col sm:flex-row gap-6 p-6 rounded-2xl bg-[#F8FAFC] border border-[#F1F5F9] hover:border-blue-200 hover:shadow-md transition-all group">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm text-[#2563EB] group-hover:bg-[#2563EB] group-hover:text-white transition-colors duration-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-[#0F172A] mb-2">Akses Inklusif & Merata</h4>
                                <p class="text-[#64748B] leading-relaxed">Meruntuhkan batasan geografis dan finansial. SIKARA membuka peluang bagi mahasiswa dari seluruh pelosok negeri untuk mengakses lowongan magang dan materi pembelajaran berkualitas secara gratis.</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-6 p-6 rounded-2xl bg-[#F8FAFC] border border-[#F1F5F9] hover:border-emerald-200 hover:shadow-md transition-all group">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm text-[#10B981] group-hover:bg-[#10B981] group-hover:text-white transition-colors duration-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-[#0F172A] mb-2">Peningkatan Kompetensi Relevan</h4>
                                <p class="text-[#64748B] leading-relaxed">Menyediakan Learning Management System (LMS) berbasis industri untuk memastikan keterampilan yang dipelajari di kampus relevan dengan kebutuhan dunia kerja masa kini.</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-6 p-6 rounded-2xl bg-[#F8FAFC] border border-[#F1F5F9] hover:border-amber-200 hover:shadow-md transition-all group">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm text-[#F59E0B] group-hover:bg-[#F59E0B] group-hover:text-white transition-colors duration-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-[#0F172A] mb-2">Kemitraan Multisektor</h4>
                                <p class="text-[#64748B] leading-relaxed">Mendorong kolaborasi nyata antara perguruan tinggi, industri, dan pemerintah. Data yang terkumpul di SIKARA menjadi landasan bagi perbaikan kurikulum pendidikan nasional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Vision Mission ── -->
        <section class="bg-[#F8FAFC] py-32 border-y border-[#E2E8F0] relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-[0.03]"></div>
            <div class="mx-auto max-w-7xl px-6 relative z-10">
                <div class="text-center mb-20">
                    <h2 class="text-3xl font-black text-[#0F172A] lg:text-4xl mb-4">Ekosistem SIKARA</h2>
                    <p class="text-lg text-[#64748B] max-w-2xl mx-auto">Sinergi antara semua pemangku kepentingan untuk menciptakan sumber daya manusia yang unggul.</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Card 1 -->
                    <div class="bg-white p-10 rounded-[2rem] shadow-sm border border-[#E2E8F0] hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-[100px] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-gradient-to-br from-[#2563EB] to-[#3B82F6] rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg shadow-blue-500/30 transform group-hover:rotate-6 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0F172A] mb-4">Mahasiswa</h3>
                        <p class="text-[#64748B] leading-relaxed">Mendapatkan panduan karir, akses pelatihan eksklusif, pembuatan CV ATS-friendly instan, dan kesempatan magang di perusahaan terkemuka secara terpusat.</p>
                    </div>
                    
                    <!-- Card 2 -->
                    <div class="bg-white p-10 rounded-[2rem] shadow-sm border border-[#E2E8F0] hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden md:-translate-y-8">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-[100px] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-gradient-to-br from-[#10B981] to-[#34D399] rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg shadow-emerald-500/30 transform group-hover:rotate-6 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0F172A] mb-4">Perusahaan</h3>
                        <p class="text-[#64748B] leading-relaxed">Memangkas biaya dan waktu rekrutmen. Akses langsung ke database talenta muda yang telah tervalidasi kompetensinya melalui sistem LMS SIKARA.</p>
                    </div>
                    
                    <!-- Card 3 -->
                    <div class="bg-white p-10 rounded-[2rem] shadow-sm border border-[#E2E8F0] hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-slate-100 rounded-bl-[100px] -z-10 group-hover:scale-110 transition-transform"></div>
                        <div class="w-16 h-16 bg-gradient-to-br from-[#475569] to-[#64748B] rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg shadow-slate-500/30 transform group-hover:rotate-6 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0F172A] mb-4">Institusi</h3>
                        <p class="text-[#64748B] leading-relaxed">Menyediakan analitik performa lulusan secara real-time yang krusial untuk penyesuaian kurikulum dan pemenuhan Indikator Kinerja Utama (IKU) Kampus.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA Section ── -->
        <section class="relative overflow-hidden py-32 bg-[#0F172A]">
            <div class="absolute inset-0 bg-gradient-to-br from-[#2563EB] to-[#1E3A8A] opacity-90"></div>
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-20 mix-blend-overlay"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-4xl bg-gradient-to-b from-white/10 to-transparent blur-3xl rounded-full"></div>
            
            <div class="mx-auto max-w-4xl px-6 text-center relative z-10 flex flex-col items-center">
                <div class="bg-white/10 backdrop-blur-md p-4 rounded-3xl mb-10 border border-white/20 shadow-2xl">
                    <img src="/images/Logo-SIKARA.png" alt="Logo SIKARA" class="h-16 w-auto brightness-200 drop-shadow-lg" />
                </div>
                
                <h2 class="text-4xl font-black text-white lg:text-6xl mb-6 tracking-tight">Mari Wujudkan Perubahan Bersama</h2>
                <p class="mt-4 max-w-2xl text-xl text-blue-100 leading-relaxed font-light mb-12">
                    Jadilah bagian dari revolusi pendidikan dan karir di Indonesia. Bergabunglah dengan puluhan ribu mahasiswa dan ratusan perusahaan di SIKARA hari ini.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-5 w-full sm:w-auto">
                    <Link :href="route('register')" class="rounded-2xl bg-white px-10 py-5 text-base font-bold text-[#2563EB] transition-all hover:bg-gray-50 hover:shadow-xl hover:shadow-white/20 hover:-translate-y-1 w-full sm:w-auto">
                        Daftar sebagai Mahasiswa
                    </Link>
                    <Link :href="route('register')" class="rounded-2xl bg-[#1E293B] border border-white/20 px-10 py-5 text-base font-bold text-white transition-all hover:bg-[#0F172A] hover:shadow-xl hover:border-white/40 hover:-translate-y-1 w-full sm:w-auto">
                        Daftar sebagai Perusahaan
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-[#F1F5F9] py-12">
            <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto opacity-80 grayscale hover:grayscale-0 transition-all duration-300" />
                    <div>
                        <span class="block text-lg font-black tracking-tight text-[#0F172A]">SIKARA</span>
                        <span class="block text-xs font-medium text-[#64748B]">Sistem Informasi Karir Mahasiswa</span>
                    </div>
                </div>
                <div class="flex gap-6 text-sm font-medium text-[#64748B]">
                    <a href="#" class="hover:text-[#2563EB] transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-[#2563EB] transition-colors">Syarat & Ketentuan</a>
                    <a href="#" class="hover:text-[#2563EB] transition-colors">Bantuan</a>
                </div>
                <p class="text-sm font-medium text-[#94A3B8]">© {{ new Date().getFullYear() }} SIKARA. Hak Cipta Dilindungi.</p>
            </div>
        </footer>
    </PortalLayout>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(1.05); }
}
.animate-pulse {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}
.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
    50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
}
.animate-bounce-slow {
    animation: bounce-slow 3s infinite;
}
</style>
