<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const props = defineProps({
    profileSummary: Object,
    stats: Array,
    latestInternships: Array,
    latestNotifications: Array,
});

const activeFaq = ref(null);

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

const faqs = [
    {
        q: 'Apakah saya bisa mendaftar lebih dari satu lowongan magang?',
        a: 'Tentu bisa! Anda bebas mengirimkan lamaran ke berbagai posisi dan perusahaan yang berbeda secara bersamaan. Kami sarankan untuk menyesuaikan keahlian dan profil dengan posisi yang dilamar agar peluang diterima lebih besar.'
    },
    {
        q: 'Apakah LMS dan Sertifikasi di SIKARA berbayar?',
        a: 'Sebagian besar modul LMS dasar disediakan secara gratis sebagai dukungan bagi mahasiswa. Namun, ada beberapa sertifikasi eksklusif dari mitra industri yang mungkin memerlukan biaya administrasi ringan dengan subsidi khusus mahasiswa.'
    },
    {
        q: 'Bagaimana saya tahu jika lamaran magang saya diterima atau ditolak?',
        a: 'Sistem kami sangat transparan. Anda akan menerima notifikasi langsung di dashboard SIKARA serta email setiap kali HRD perusahaan mengubah status lamaran Anda (misal: Direview, Diundang Wawancara, Diterima, atau Ditolak).'
    },
    {
        q: 'Apakah saya bisa memperbarui CV setelah melamar?',
        a: 'Bisa. Sistem Generate CV ATS mengambil data langsung dari profil Anda secara real-time. Jika Anda mengupdate profil, lamaran berikutnya akan otomatis menggunakan CV terbaru.'
    },
    {
        q: 'Bagaimana jika kampus saya belum terdaftar di SIKARA?',
        a: 'Saat ini kami fokus pada kemitraan kampus yang sudah terverifikasi PDDIKTI. Jika kampus Anda belum ada di list pendaftaran, Anda bisa menghubungi admin kampus atau kirimkan request ke tim support kami untuk dihubungkan.'
    }
];
</script>

<template>
    <Head title="Portal Peserta — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">List Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Generate CV</Link>
        </template>

        <!-- ── Welcome Banner (Authenticated) ── -->
        <section v-if="user" class="bg-gradient-to-r from-[#2563EB] to-[#1E40AF] text-white">
            <div class="mx-auto max-w-6xl px-6 py-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 text-2xl font-black backdrop-blur-sm">
                            {{ user.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white/70">Selamat datang kembali,</p>
                            <h2 class="text-2xl font-bold">{{ user.name }}</h2>
                            <p v-if="profileSummary" class="text-sm text-white/70 mt-0.5">{{ profileSummary.university }} · {{ profileSummary.department }}</p>
                        </div>
                    </div>
                    <div v-if="stats" class="flex gap-6">
                        <div v-for="stat in stats" :key="stat.label" class="text-center bg-white/10 rounded-xl px-5 py-3 backdrop-blur-sm">
                            <p class="text-2xl font-black">{{ stat.value }}</p>
                            <p class="text-xs font-medium text-white/70 mt-1">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Hero Peserta ── -->
        <section class="mx-auto max-w-6xl px-6 pt-24 pb-20 text-center">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-4 py-1.5 shadow-sm">
                <span class="text-xs font-bold text-[#2563EB]">Platform Pengembangan Karier Mahasiswa</span>
            </div>

            <h1 class="mx-auto max-w-4xl text-4xl font-extrabold leading-tight tracking-tight text-[#0F172A] lg:text-6xl">
                Jembatan Menuju<br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2563EB] to-[#60A5FA]">Karier Impian Anda</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-base text-[#64748B] leading-relaxed">
                Tingkatkan kompetensi Anda melalui Pelatihan & LMS, temukan magang yang tepat dari ratusan mitra, dan buat CV profesional standar industri secara instan.
            </p>
        </section>

        <!-- ── Fitur Unggulan ── -->
        <section class="border-t border-[#F1F5F9] bg-[#F8FAFC]">
            <div class="mx-auto max-w-7xl px-6 py-24">
                <div class="mb-14 text-center">
                    <h2 class="text-3xl font-bold text-[#0F172A]">Eksplorasi Fitur Kami</h2>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/5">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#EFF6FF]">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Cari Lowongan</h3>
                        <p class="mt-2 text-sm text-[#64748B]">Temukan pekerjaan dan magang dari startup hingga perusahaan multinasional.</p>
                    </div>
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/5">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#EFF6FF]">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M3 15h6"/><path d="M3 18h6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Generate CV Otomatis</h3>
                        <p class="mt-2 text-sm text-[#64748B]">Cukup isi profil Anda, dan sistem kami akan menghasilkan CV format ATS-friendly secara otomatis.</p>
                    </div>
                    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/5">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#EFF6FF]">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[#0F172A]">LMS & Pelatihan</h3>
                        <p class="mt-2 text-sm text-[#64748B]">Tingkatkan kemampuan teknis dan soft-skill Anda lewat modul pembelajaran eksklusif.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Alur Penggunaan ── -->
        <section class="bg-white py-24 border-t border-[#F1F5F9]">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <span class="text-sm font-black text-[#2563EB] tracking-widest uppercase">Cara Kerja</span>
                <h2 class="mt-2 text-3xl font-black text-[#0F172A] lg:text-4xl">Langkah Mudah Memulai Karier</h2>
                
                <div class="mt-16 grid gap-8 md:grid-cols-4 relative">
                    <div class="hidden md:block absolute top-12 left-24 right-24 h-0.5 bg-gradient-to-r from-[#EFF6FF] via-[#2563EB] to-[#EFF6FF] opacity-30"></div>
                    
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative z-10 transition-transform hover:-translate-y-2">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-md text-2xl font-black text-[#2563EB]">1</div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Lengkapi Profil</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Isi data akademik, pengalaman organisasi, dan skill teknis yang Anda miliki.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative z-10 transition-transform hover:-translate-y-2">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-md text-2xl font-black text-[#2563EB]">2</div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Generate CV ATS</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Sistem akan otomatis mengubah profil Anda menjadi CV berstandar industri.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative z-10 transition-transform hover:-translate-y-2">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-md text-2xl font-black text-[#2563EB]">3</div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Ikuti LMS/Event</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Tingkatkan value Anda dengan mengikuti sertifikasi dan pelatihan mitra.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-[#E2E8F0] relative z-10 transition-transform hover:-translate-y-2">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white border-4 border-[#EFF6FF] shadow-md text-2xl font-black text-[#2563EB]">4</div>
                        <h3 class="text-lg font-bold text-[#0F172A]">Lamar Magang</h3>
                        <p class="mt-3 text-sm text-[#64748B] leading-relaxed">Kirim lamaran ke berbagai perusahaan besar dengan satu klik mudah.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Benefit ── -->
        <section class="bg-[#0F172A] py-24 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-[#2563EB] opacity-10 blur-3xl"></div>
            <div class="mx-auto max-w-7xl px-6 relative z-10">
                <div class="grid gap-12 lg:grid-cols-2 items-center">
                    <div>
                        <span class="text-sm font-black text-[#60A5FA] tracking-widest uppercase">Keunggulan Utama</span>
                        <h2 class="mt-2 text-3xl font-black text-white lg:text-4xl leading-tight">Mengapa Mahasiswa Wajib Menggunakan SIKARA?</h2>
                        <p class="mt-6 text-lg text-[#94A3B8] leading-relaxed">
                            Bukan sekadar papan lowongan kerja biasa. SIKARA adalah ekosistem utuh yang membimbing mahasiswa dari bangku kuliah hingga masuk ke dunia industri.
                        </p>
                        <div class="mt-8 space-y-6">
                            <div class="flex gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10 text-[#60A5FA]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white">100% Lowongan Tervalidasi</h4>
                                    <p class="mt-1 text-sm text-[#94A3B8]">Bebas penipuan rekrutmen. Semua perusahaan mitra telah diverifikasi legalitasnya.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10 text-[#60A5FA]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white">Auto-Generate CV ATS Friendly</h4>
                                    <p class="mt-1 text-sm text-[#94A3B8]">Tidak perlu bingung mengatur layout. Sistem akan mengkonversi profil Anda ke PDF yang lolos screening bot perusahaan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/5 border border-white/10 p-6 rounded-2xl backdrop-blur-sm">
                            <h3 class="text-4xl font-black text-white">200+</h3>
                            <p class="text-sm text-[#94A3B8] mt-2">Mitra Perusahaan</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-2xl backdrop-blur-sm transform translate-y-8">
                            <h3 class="text-4xl font-black text-[#60A5FA]">50+</h3>
                            <p class="text-sm text-[#94A3B8] mt-2">Sertifikasi & LMS</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-2xl backdrop-blur-sm">
                            <h3 class="text-4xl font-black text-[#60A5FA]">1.2k+</h3>
                            <p class="text-sm text-[#94A3B8] mt-2">Mahasiswa Tersalurkan</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-2xl backdrop-blur-sm transform translate-y-8">
                            <h3 class="text-4xl font-black text-white">95%</h3>
                            <p class="text-sm text-[#94A3B8] mt-2">Tingkat Kepuasan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FAQ Peserta ── -->
        <section id="faq" class="bg-white py-24 border-t border-[#F1F5F9]">
            <div class="mx-auto max-w-3xl px-6">
                <div class="mb-12 text-center">
                    <span class="text-sm font-black text-[#2563EB] tracking-widest uppercase">Pusat Bantuan</span>
                    <h2 class="mt-2 text-3xl font-black text-[#0F172A]">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                    <p class="mt-4 text-[#64748B]">Temukan jawaban dari pertanyaan yang sering ditanyakan peserta SIKARA.</p>
                </div>
                
                <div class="space-y-4">
                    <div v-for="(faq, index) in faqs" :key="index" 
                         class="border border-[#E2E8F0] rounded-2xl bg-white overflow-hidden transition-all duration-300"
                         :class="{'border-[#2563EB] shadow-md shadow-[#2563EB]/10 ring-1 ring-[#2563EB]/10': activeFaq === index, 'hover:border-[#CBD5E1]': activeFaq !== index}">
                        <button @click="toggleFaq(index)" class="w-full flex items-center justify-between p-6 text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition-colors duration-300"
                                     :class="activeFaq === index ? 'bg-[#2563EB] text-white' : 'bg-[#EFF6FF] text-[#2563EB]'">
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

        <!-- ── CTA ── -->
        <section class="bg-[#EFF6FF] py-24 border-t border-[#E2E8F0] relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-5"></div>
            <div class="mx-auto max-w-4xl px-6 text-center relative z-10">
                <template v-if="user">
                    <h2 class="text-3xl font-black text-[#0F172A] lg:text-5xl leading-tight">Mulai Eksplorasi Kariermu!</h2>
                    <p class="mt-6 text-lg text-[#64748B] max-w-2xl mx-auto">Temukan lowongan magang impian, tingkatkan skill lewat LMS, dan buat CV profesional untuk melamar sekarang.</p>
                    <div class="mt-10 flex flex-wrap justify-center gap-4">
                        <Link :href="route('lowongan')" class="inline-flex items-center rounded-xl bg-[#2563EB] px-10 py-4 text-base font-bold text-white transition-all hover:bg-[#1d4ed8] hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/30">
                            Cari Lowongan
                            <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </Link>
                        <Link :href="route('profile.show')" class="inline-flex items-center rounded-xl bg-white border border-[#E2E8F0] px-8 py-4 text-base font-bold text-[#0F172A] transition-all hover:bg-[#F8FAFC] hover:-translate-y-1 hover:shadow-lg">
                            Lengkapi Profil
                        </Link>
                    </div>
                </template>
                <template v-else>
                    <h2 class="text-3xl font-black text-[#0F172A] lg:text-5xl leading-tight">Siap Memulai Langkah Pertamamu?</h2>
                    <p class="mt-6 text-lg text-[#64748B] max-w-2xl mx-auto">Ratusan perusahaan top sedang mencari talenta muda seperti Anda. Jangan tunggu lulus untuk membangun profil profesional.</p>
                    <div class="mt-10">
                        <Link :href="route('login', { role: 'mahasiswa' })" class="inline-flex items-center rounded-xl bg-[#2563EB] px-10 py-4 text-base font-bold text-white transition-all hover:bg-[#1d4ed8] hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/30">
                            Buat Akun Peserta Gratis
                            <svg class="ml-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </template>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-[#E2E8F0]">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
                <div class="flex items-center gap-3">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-5 w-auto opacity-70" />
                    <span class="text-xs font-bold text-[#64748B]">SIKARA</span>
                </div>
                <p class="text-xs font-medium text-[#94A3B8]">© 2026 Portal Peserta</p>
            </div>
        </footer>
    </PortalLayout>
</template>
