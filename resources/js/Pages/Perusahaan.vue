<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const activeFaq = ref(null);

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

const faqs = [
    {
        q: 'Apakah ada batasan jumlah lowongan yang bisa di-posting?',
        a: 'Tidak ada batasan. Anda dapat mempublikasikan sebanyak mungkin kebutuhan magang perusahaan Anda secara gratis dan tanpa kuota maksimum.'
    },
    {
        q: 'Bagaimana SIKARA memverifikasi keaslian data mahasiswa?',
        a: 'Setiap akun peserta terintegrasi langsung dengan Pangkalan Data Pendidikan Tinggi (PDDikti) dan sistem akademik kampus. Kami menjamin 100% kandidat yang melamar adalah mahasiswa aktif dan terverifikasi.'
    },
    {
        q: 'Bisakah saya menyeleksi kandidat langsung dari dashboard?',
        a: 'Tentu bisa. SIKARA dilengkapi dengan fitur ATS (Applicant Tracking System) internal. Anda dapat memfilter, mengubah status lamaran (Review, Wawancara, Diterima, Ditolak), dan mengatur jadwal secara terpusat.'
    },
    {
        q: 'Apakah ada biaya tambahan jika perusahaan saya ingin mengadakan tes kompetensi?',
        a: 'Tidak ada biaya tersembunyi. Platform kami memfasilitasi penggunaan LMS bagi kandidat secara gratis, dan jika ada modul tes premium dari mitra industri, biaya akan sangat transparan di awal.'
    },
    {
        q: 'Berapa lama rata-rata waktu yang dibutuhkan untuk menemukan kandidat yang cocok?',
        a: 'Berkat filter ATS dan database ribuan mahasiswa aktif kami, rata-rata mitra perusahaan berhasil mendapatkan kandidat yang sesuai spesifikasi dalam waktu kurang dari 72 jam sejak lowongan diposting.'
    }
];
</script>

<template>
    <Head title="Portal Perusahaan — SIKARA" />

    <PortalLayout activeRole="perusahaan" loginRole="perusahaan">
        <template #navigation>
            <a href="#cara-merekrut" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#10B981]">Cara Merekrut</a>
            <a href="#mengapa-sikara" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#10B981]">Mengapa SIKARA</a>
            <a href="#testimoni" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#10B981]">Testimoni Mitra</a>
        </template>

        <!-- ── Hero Perusahaan (Distinct Dark/Split Design) ── -->
        <section class="bg-[#0F172A] relative overflow-hidden">
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#10B981] opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-[#2563EB] opacity-10 blur-3xl"></div>
            
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-12 px-6 py-24 lg:flex-row lg:py-32">
                <div class="max-w-2xl z-10">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-[#10B981] animate-pulse"></span>
                        <span class="text-xs font-bold text-white tracking-wider">UNTUK PEREKRUT & HR</span>
                    </div>

                    <h1 class="text-4xl font-black leading-tight text-white lg:text-6xl">
                        Rekrut Talenta Gen-Z<br />
                        <span class="text-[#10B981]">Lebih Cepat & Akurat</span>
                    </h1>

                    <p class="mt-6 text-lg text-[#94A3B8] leading-relaxed">
                        SIKARA memberikan akses eksklusif ke ribuan mahasiswa terverifikasi. Kurangi biaya <em>headhunting</em>, filter kandidat secara presisi, dan kelola proses rekrutmen magang Anda tanpa hambatan.
                    </p>
                    
                    <div class="mt-8">
                        <Link :href="route('login', { role: 'perusahaan' })" class="inline-flex rounded-xl bg-[#10B981] px-8 py-3.5 text-sm font-bold text-white transition-all hover:bg-[#059669] hover:shadow-lg hover:shadow-[#10B981]/20">
                            Posting Lowongan Sekarang
                        </Link>
                    </div>
                </div>

                <div class="z-10 w-full max-w-md rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur-md shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6">Metrik Kinerja SIKARA</h3>
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between text-sm text-[#94A3B8] mb-2">
                                <span>Waktu Mencari Kandidat</span>
                                <span class="text-[#10B981] font-bold">-60%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full bg-[#10B981] w-[60%] rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm text-[#94A3B8] mb-2">
                                <span>Kesesuaian Skill Kandidat</span>
                                <span class="text-[#10B981] font-bold">+85%</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full bg-[#10B981] w-[85%] rounded-full"></div>
                            </div>
                        </div>
                        <div class="pt-4 mt-4 border-t border-white/10 flex justify-between items-center">
                            <span class="text-white font-bold text-3xl">200+</span>
                            <span class="text-xs text-[#94A3B8] text-right uppercase tracking-wider">Perusahaan<br/>Telah Bergabung</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Cara Merekrut ── -->
        <section id="cara-merekrut" class="bg-[#F8FAFC] py-24 border-b border-[#E2E8F0]">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <span class="text-sm font-black text-[#10B981] tracking-widest uppercase">Proses Mudah</span>
                <h2 class="mt-2 text-3xl font-black text-[#0F172A] lg:text-4xl">Cara Merekrut di SIKARA</h2>
                
                <div class="mt-16 grid gap-8 md:grid-cols-4">
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 rounded-full bg-[#10B981] text-white flex items-center justify-center font-black text-xl shadow-lg border-4 border-[#F8FAFC]">1</div>
                        <h3 class="mt-4 text-lg font-bold text-[#0F172A]">Buat Akun HR</h3>
                        <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Daftarkan profil perusahaan Anda dengan verifikasi legalitas.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 rounded-full bg-[#10B981] text-white flex items-center justify-center font-black text-xl shadow-lg border-4 border-[#F8FAFC]">2</div>
                        <h3 class="mt-4 text-lg font-bold text-[#0F172A]">Posting Posisi</h3>
                        <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Tuliskan kebutuhan skill spesifik untuk menemukan <strong>talent</strong> yang pas.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 rounded-full bg-[#10B981] text-white flex items-center justify-center font-black text-xl shadow-lg border-4 border-[#F8FAFC]">3</div>
                        <h3 class="mt-4 text-lg font-bold text-[#0F172A]">Filter CV (ATS)</h3>
                        <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Sistem menyaring CV mahasiswa secara otomatis berdasarkan relevansi.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 rounded-full bg-[#10B981] text-white flex items-center justify-center font-black text-xl shadow-lg border-4 border-[#F8FAFC]">4</div>
                        <h3 class="mt-4 text-lg font-bold text-[#0F172A]">Terima Kandidat</h3>
                        <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Jadwalkan <strong>interview</strong> dan rekrut mahasiswa terbaik pilihan Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Mengapa Memilih Kami ── -->
        <section id="mengapa-sikara" class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-16">
                    <span class="text-sm font-black text-[#10B981] tracking-widest uppercase">Keunggulan Platform</span>
                    <h2 class="mt-2 text-3xl font-black text-[#0F172A] border-l-4 border-[#10B981] pl-4">Alasan SIKARA adalah Pilihan Terbaik</h2>
                    <p class="mt-3 text-[#64748B] pl-5 max-w-2xl">Platform kami dirancang khusus untuk memenuhi standar rekrutmen B2B modern.</p>
                </div>

                <div class="grid gap-x-12 gap-y-16 md:grid-cols-2 lg:grid-cols-3">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5] text-[#10B981]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#0F172A]">Kandidat Tervalidasi</h3>
                            <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Semua mahasiswa yang terdaftar di SIKARA telah diverifikasi keaktifannya melalui sistem akademik kampus, memastikan Anda hanya merekrut talenta asli.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5] text-[#10B981]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 14v1"/><path d="M9 19v2"/><path d="M9 3v2"/><path d="M9 9v1"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#0F172A]">Manajemen Terpusat</h3>
                            <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Posting lowongan, terima lamaran, jadwalkan wawancara, dan evaluasi performa magang semua dalam satu dashboard canggih tanpa pindah tab.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#ECFDF5] text-[#10B981]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#0F172A]">Skema Bebas Biaya</h3>
                            <p class="mt-2 text-sm text-[#64748B] leading-relaxed">Berbeda dengan platform rekrutmen lain, memposting lowongan magang dasar di SIKARA sepenuhnya gratis sebagai bentuk kolaborasi pendidikan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Testimoni Mitra ── -->
        <section id="testimoni" class="bg-[#F8FAFC] py-24 border-t border-[#E2E8F0]">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <span class="text-sm font-black text-[#10B981] tracking-widest uppercase">Kisah Sukses</span>
                <h2 class="mt-2 text-3xl font-black text-[#0F172A]">Testimoni Mitra Kami</h2>
                
                <div class="mt-16 grid gap-8 md:grid-cols-3">
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#E2E8F0] text-left">
                        <div class="flex items-center gap-1 text-[#F59E0B] mb-4">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </div>
                        <p class="text-[#0F172A] italic mb-6">"Proses rekrutmen magang kami menjadi 3x lebih cepat berkat CV ATS dari SIKARA. Mahasiswanya sangat relevan dengan kebutuhan kami."</p>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-[#E2E8F0]"></div>
                            <div>
                                <h4 class="font-bold text-[#0F172A] text-sm">Budi Santoso</h4>
                                <span class="text-xs text-[#64748B]">HR Manager, TechIndo</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#E2E8F0] text-left">
                        <div class="flex items-center gap-1 text-[#F59E0B] mb-4">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </div>
                        <p class="text-[#0F172A] italic mb-6">"LMS SIKARA sungguh brilian. Anak magang yang kami terima sudah memiliki basic skill yang kuat karena pelatihan di platform tersebut."</p>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-[#E2E8F0]"></div>
                            <div>
                                <h4 class="font-bold text-[#0F172A] text-sm">Siti Rahma</h4>
                                <span class="text-xs text-[#64748B]">Talent Acquisition, Kreatifa</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-8 rounded-2xl shadow-sm border border-[#E2E8F0] text-left">
                        <div class="flex items-center gap-1 text-[#F59E0B] mb-4">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        </div>
                        <p class="text-[#0F172A] italic mb-6">"Sangat mudah dioperasikan. Kami bisa posting banyak posisi sekaligus dan semuanya terpantau rapi dalam satu dashboard HR."</p>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-[#E2E8F0]"></div>
                            <div>
                                <h4 class="font-bold text-[#0F172A] text-sm">Andi Wijaya</h4>
                                <span class="text-xs text-[#64748B]">CEO, Startup Nusantara</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FAQ Perusahaan ── -->
        <section id="faq" class="bg-white py-24 border-t border-[#E2E8F0]">
            <div class="mx-auto max-w-3xl px-6">
                <div class="mb-12 text-center">
                    <span class="text-sm font-black text-[#10B981] tracking-widest uppercase">Pusat Bantuan</span>
                    <h2 class="mt-2 text-3xl font-black text-[#0F172A]">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                    <p class="mt-4 text-[#64748B]">Jawaban cepat untuk pertanyaan seputar proses rekrutmen di SIKARA.</p>
                </div>
                
                <div class="space-y-4">
                    <div v-for="(faq, index) in faqs" :key="index" 
                         class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden transition-all duration-300"
                         :class="{'border-[#10B981] shadow-md shadow-[#10B981]/10 ring-1 ring-[#10B981]/10': activeFaq === index, 'hover:border-[#CBD5E1]': activeFaq !== index}">
                        <button @click="toggleFaq(index)" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-colors duration-300"
                                     :class="activeFaq === index ? 'bg-[#10B981] text-white' : 'bg-[#ECFDF5] text-[#10B981]'">
                                    <span class="font-bold text-sm">Q</span>
                                </div>
                                <h3 class="text-lg font-bold text-[#0F172A] pr-4">{{ faq.q }}</h3>
                            </div>
                            <div class="shrink-0 transition-transform duration-300" :class="{'rotate-180': activeFaq === index}">
                                <svg class="h-5 w-5 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </button>
                        <div class="grid transition-all duration-300 ease-in-out" 
                             :style="activeFaq === index ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'">
                            <div class="overflow-hidden">
                                <p class="pb-6 pr-6 pl-[4.5rem] text-[#64748B] leading-relaxed">
                                    {{ faq.a }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#0F172A] border-t border-white/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-8">
                <div class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-6 w-auto grayscale brightness-200" />
                    <span class="text-sm font-black text-white tracking-widest">SIKARA</span>
                </div>
                <p class="text-xs font-medium text-[#64748B]">© 2026 Portal Perusahaan SIKARA</p>
            </div>
        </footer>
    </PortalLayout>
</template>
