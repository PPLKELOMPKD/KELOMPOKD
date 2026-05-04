<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { onMounted, ref } from 'vue';

const isVisible = ref(false);
const activeGuide = ref(null);

onMounted(() => {
    isVisible.value = true;
});

const openGuide = (guide) => {
    activeGuide.value = guide;
    document.body.style.overflow = 'hidden';
};

const closeGuide = () => {
    activeGuide.value = null;
    document.body.style.overflow = 'auto';
};

const guides = [
    {
        title: 'Panduan Mahasiswa',
        description: 'Langkah-langkah komprehensif untuk mendaftar, melengkapi profil, membuat CV ATS-friendly secara instan, dan melamar ke lowongan impian Anda.',
        icon: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222',
        colorClass: 'from-[#2563EB] to-[#3B82F6]',
        bgClass: 'bg-blue-50',
        shadowClass: 'shadow-blue-500/30',
        meta: { time: '10 Menit', level: 'Mudah', for: 'Mahasiswa / Lulusan Baru' },
        details: [
            { 
                step: 1, 
                title: 'Registrasi & Verifikasi Akun', 
                content: 'Proses awal untuk bergabung dengan ekosistem SIKARA.',
                subItems: [
                    'Klik tombol "Masuk" di pojok kanan atas portal.',
                    'Pilih opsi "Daftar sebagai Mahasiswa".',
                    'Isi formulir dengan nama lengkap, email aktif, dan password yang kuat.',
                    'Periksa kotak masuk email Anda dan klik tautan verifikasi yang dikirimkan.'
                ]
            },
            { 
                step: 2, 
                title: 'Lengkapi Profil & Buat CV', 
                content: 'Profil yang lengkap akan meningkatkan peluang Anda dilirik oleh perusahaan.',
                subItems: [
                    'Masuk ke menu "Profil Saya" dari dropdown menu pengguna di pojok kanan atas.',
                    'Isi data pendidikan (universitas, jurusan, periode studi).',
                    'Tambahkan pengalaman organisasi, pengalaman kerja (jika ada), dan daftar keahlian.',
                    'Sistem akan otomatis men-generate CV berstandar ATS. Anda bisa mengunduhnya di menu "Unduh CV".'
                ]
            },
            { 
                step: 3, 
                title: 'Eksplorasi Lowongan & Event', 
                content: 'Cari peluang magang dan pelatihan yang sesuai dengan kualifikasi Anda.',
                subItems: [
                    'Akses menu "Cari Lowongan" pada navigasi utama.',
                    'Gunakan filter pencarian untuk menemukan posisi spesifik berdasarkan lokasi atau tipe pekerjaan.',
                    'Kunjungi menu "Pelatihan" untuk mendaftar event dan webinar guna meningkatkan kompetensi Anda.'
                ]
            },
            { 
                step: 4, 
                title: 'Lamar & Lacak Progres', 
                content: 'Langkah krusial untuk menggapai karir impian Anda.',
                subItems: [
                    'Klik "Lamar" pada lowongan yang diminati. CV SIKARA Anda akan langsung dikirimkan ke HRD perusahaan.',
                    'Akses menu "Lamaran Saya" untuk melihat status lamaran.',
                    'Status lamaran akan diperbarui secara real-time (Menunggu, Wawancara, Diterima, atau Ditolak).'
                ]
            }
        ]
    },
    {
        title: 'Panduan Perusahaan',
        description: 'Cara mudah untuk memposting lowongan, menyaring kandidat terbaik, dan mengelola proses rekrutmen melalui dashboard SIKARA yang efisien.',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        colorClass: 'from-[#10B981] to-[#34D399]',
        bgClass: 'bg-emerald-50',
        shadowClass: 'shadow-emerald-500/30',
        meta: { time: '15 Menit', level: 'Menengah', for: 'HRD / Perusahaan' },
        details: [
            { 
                step: 1, 
                title: 'Pendaftaran & Verifikasi Legalitas', 
                content: 'Buat akun representatif perusahaan dan verifikasi legalitas perusahaan Anda.',
                subItems: [
                    'Pilih "Daftar sebagai Perusahaan" pada halaman pendaftaran.',
                    'Isi detail perusahaan secara akurat beserta dokumen pendukung (NIB/SIUP/Profil Perusahaan).',
                    'Tunggu proses verifikasi manual dari tim admin SIKARA maksimal 2x24 jam kerja.'
                ]
            },
            { 
                step: 2, 
                title: 'Manajemen Lowongan Terpusat', 
                content: 'Posting peluang magang atau pekerjaan dengan jangkauan ribuan mahasiswa.',
                subItems: [
                    'Login dan masuk ke "Dashboard Perusahaan".',
                    'Navigasi ke menu "Lowongan", lalu klik "Tambah Lowongan Baru".',
                    'Tulis deskripsi pekerjaan yang jelas, kualifikasi yang dibutuhkan, dan batas waktu pendaftaran.'
                ]
            },
            { 
                step: 3, 
                title: 'Seleksi & Pengelolaan Kandidat', 
                content: 'Proses screening lamaran menjadi lebih cepat dan terstruktur.',
                subItems: [
                    'Pantau pelamar masuk pada tab "Kandidat" di masing-masing lowongan.',
                    'Gunakan fitur preview CV untuk melihat kualifikasi tanpa harus mengunduhnya satu per satu.',
                    'Manfaatkan fitur "Unduh Massal" untuk menyimpan berkas kandidat yang terpilih ke perangkat Anda.'
                ]
            },
            { 
                step: 4, 
                title: 'Pembaruan Status & Komunikasi', 
                content: 'Berikan pengalaman rekrutmen yang transparan kepada kandidat.',
                subItems: [
                    'Ubah status pelamar (Diterima, Wawancara, Ditolak) secara langsung di dashboard.',
                    'Sistem akan otomatis mengirimkan notifikasi kepada kandidat mengenai perubahan status tersebut.',
                    'Gunakan analitik dasar di dashboard untuk melihat tren pelamar pada lowongan Anda.'
                ]
            }
        ]
    },
    {
        title: 'Panduan Learning Management System',
        description: 'Eksplorasi cara mengakses berbagai materi pembelajaran terkini, mengikuti kursus intensif, dan mendapatkan sertifikat keterampilan untuk meningkatkan nilai profesional Anda.',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        colorClass: 'from-[#F59E0B] to-[#FBBF24]',
        bgClass: 'bg-amber-50',
        shadowClass: 'shadow-amber-500/30',
        meta: { time: 'Fleksibel', level: 'Semua Level', for: 'Mahasiswa' },
        details: [
            { 
                step: 1, 
                title: 'Akses Portal & Katalog Kelas', 
                content: 'Mulai perjalanan peningkatan skill Anda di portal pembelajaran khusus.',
                subItems: [
                    'Masuk menggunakan akun mahasiswa yang telah terdaftar.',
                    'Klik navigasi "LMS" di bagian atas halaman untuk masuk ke ruang kelas virtual SIKARA.',
                    'Telusuri katalog kelas berdasarkan kategori seperti Teknologi, Bisnis, Desain, atau Soft Skills.'
                ]
            },
            { 
                step: 2, 
                title: 'Proses Pembelajaran Mandiri', 
                content: 'Ikuti alur belajar komprehensif dengan materi yang divalidasi oleh pakar industri.',
                subItems: [
                    'Klik "Ikuti Kelas" pada topik yang Anda minati.',
                    'Tonton materi berupa Video Interaktif, dan pelajari modul teks yang disediakan.',
                    'Progres belajar Anda akan disimpan otomatis. Anda bisa melanjutkannya kapan saja.'
                ]
            },
            { 
                step: 3, 
                title: 'Evaluasi & Kuis Penilaian', 
                content: 'Uji pemahaman Anda melalui evaluasi pada setiap akhir silabus.',
                subItems: [
                    'Kerjakan kuis pilihan ganda untuk mengukur penyerapan materi.',
                    'Raih skor minimum kelulusan (passing grade) yang ditentukan pada masing-masing kelas.',
                    'Anda dapat melakukan retake (mengulang) kuis jika nilai belum mencapai standar kelulusan.'
                ]
            },
            { 
                step: 4, 
                title: 'Klaim Sertifikat & Portofolio', 
                content: 'Dapatkan bukti sah kompetensi untuk memperkuat nilai jual Anda di mata rekruter.',
                subItems: [
                    'Setelah mencapai progres 100% dan lulus ujian, tombol "Klaim Sertifikat" akan aktif.',
                    'Unduh Sertifikat Digital (PDF) ber-QR code validasi resmi dari SIKARA.',
                    'Keahlian yang Anda pelajari akan otomatis terintegrasi sebagai Verified Skill di profil Anda.'
                ]
            }
        ]
    }
];
</script>

<template>
    <Head title="Panduan | SIKARA" />

    <PortalLayout>
        <!-- Hero Section -->
        <section class="relative bg-[#0F172A] overflow-hidden pt-24 pb-32">
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-10"></div>
            <div class="absolute -top-40 -right-40 h-[600px] w-[600px] rounded-full bg-[#2563EB] opacity-20 blur-[120px] animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 h-[600px] w-[600px] rounded-full bg-[#10B981] opacity-20 blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>

            <div class="mx-auto max-w-7xl px-6 relative z-10 text-center" :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'" style="transition: all 1s ease-out;">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-6 tracking-tight">
                    Panduan <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#3B82F6] to-[#34D399]">Penggunaan SIKARA</span>
                </h1>
                <p class="text-lg text-[#94A3B8] max-w-2xl mx-auto leading-relaxed mb-10">
                    Temukan panduan langkah demi langkah untuk memaksimalkan pengalaman Anda di platform SIKARA, baik sebagai mahasiswa maupun perusahaan.
                </p>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-sm font-medium">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Pilih kategori panduan di bawah ini
                </div>
            </div>
            
            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block w-[calc(100%+1.3px)] h-[80px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,111.47,192.59,96.5,238.13,85.36,281.44,71.62,321.39,56.44Z" class="fill-[#F8FAFC]"></path>
                </svg>
            </div>
        </section>

        <!-- Main Content -->
        <section class="bg-[#F8FAFC] py-24 pb-32">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(guide, index) in guides" :key="index" 
                         class="bg-white rounded-[2rem] p-10 shadow-sm border border-[#E2E8F0] hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden"
                         :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                         :style="`transition-delay: ${index * 150}ms`">
                        <div class="absolute top-0 right-0 w-32 h-32 rounded-bl-[100px] -z-10 group-hover:scale-110 transition-transform" :class="guide.bgClass"></div>
                        <div class="w-16 h-16 bg-gradient-to-br rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg transform group-hover:rotate-6 transition-transform" :class="[guide.colorClass, guide.shadowClass]">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="guide.icon" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0F172A] mb-4 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-blue-600 group-hover:to-emerald-500 transition-colors">{{ guide.title }}</h3>
                        <p class="text-[#64748B] leading-relaxed mb-8">{{ guide.description }}</p>
                        <button @click="openGuide(guide)" class="inline-flex items-center gap-2 text-sm font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors group/link">
                            Pelajari Selengkapnya
                            <svg class="w-4 h-4 transform group-hover/link:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Video Tutorial Section -->
                <div class="mt-32 relative rounded-[2.5rem] bg-white p-8 md:p-12 shadow-xl border border-[#E2E8F0] overflow-hidden" :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'" style="transition: all 1s ease-out 0.5s;">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-bl-full -z-10"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-50 rounded-tr-full -z-10"></div>
                    
                    <div class="flex flex-col lg:flex-row items-center gap-12 relative z-10">
                        <div class="lg:w-1/2">
                            <h2 class="text-3xl font-black text-[#0F172A] mb-6">Video Tutorial Interaktif</h2>
                            <p class="text-lg text-[#64748B] leading-relaxed mb-8">
                                Lebih suka belajar secara visual? Tonton seri video tutorial kami yang mencakup segala hal mulai dari pendaftaran akun hingga tips sukses wawancara untuk lowongan yang Anda lamar.
                            </p>
                            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#2563EB] to-[#3B82F6] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-blue-500/30 transition-all hover:shadow-blue-500/50 hover:-translate-y-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Tonton Playlist Video
                            </button>
                        </div>
                        <div class="lg:w-1/2 w-full">
                            <div class="relative rounded-2xl overflow-hidden shadow-2xl group cursor-pointer aspect-video bg-slate-800">
                                <img src="/images/pattern-grid.svg" class="absolute inset-0 opacity-20 w-full h-full object-cover" alt="Video Placeholder" />
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0F172A]/80 to-transparent flex items-center justify-center">
                                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border-2 border-white/50 group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-10 h-10 text-white ml-2" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Modal Detail Panduan (Native Scroll) -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="activeGuide" class="fixed inset-0 z-[9999] overflow-y-auto custom-scrollbar">
                <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-12">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-[#0F172A]/60 backdrop-blur-sm transition-opacity" @click="closeGuide"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative w-full max-w-4xl bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col my-auto transition-all transform">
                        <!-- Header -->
                        <div class="px-8 md:px-12 py-10 border-b border-[#E2E8F0] relative overflow-hidden" :class="activeGuide.bgClass">
                            <!-- Background abstract shapes -->
                            <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-20 transform translate-x-1/3 -translate-y-1/3 bg-gradient-to-br" :class="activeGuide.colorClass"></div>
                            <div class="absolute bottom-0 left-0 w-40 h-40 rounded-full opacity-10 transform -translate-x-1/2 translate-y-1/2 bg-gradient-to-br" :class="activeGuide.colorClass"></div>
                            
                            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex items-start gap-6">
                                    <div class="w-16 h-16 rounded-[1.25rem] flex items-center justify-center text-white shrink-0 shadow-lg bg-gradient-to-br" :class="activeGuide.colorClass">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activeGuide.icon" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-3xl md:text-4xl font-black text-[#0F172A] mb-3 tracking-tight">{{ activeGuide.title }}</h3>
                                        <div class="flex flex-wrap gap-2 md:gap-3">
                                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/70 backdrop-blur-md border border-white/50 text-xs font-bold text-[#475569] shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ activeGuide.meta.time }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-white/70 backdrop-blur-md border border-white/50 text-xs font-bold text-[#475569] shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ activeGuide.meta.level }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="closeGuide" class="absolute top-6 right-6 md:static text-[#64748B] hover:text-[#0F172A] transition-colors p-3 rounded-full hover:bg-white/80 backdrop-blur-sm shadow-sm border border-transparent hover:border-white/50">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Body -->
                        <div class="px-8 md:px-12 py-10 bg-white">
                            <p class="text-[#475569] text-lg md:text-xl mb-12 leading-relaxed border-l-4 pl-6 border-[#CBD5E1]">{{ activeGuide.description }}</p>
                            
                            <div class="relative space-y-12 before:absolute before:inset-0 before:ml-[1.15rem] before:w-0.5 before:bg-gradient-to-b before:from-[#E2E8F0] before:to-transparent">
                                <div v-for="(detail, idx) in activeGuide.details" :key="idx" class="relative flex gap-6 md:gap-10 group">
                                    <div class="flex flex-col items-center mt-1">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-white shadow-md ring-[6px] ring-white z-10 transition-transform duration-300 group-hover:scale-110 shrink-0 bg-gradient-to-br" :class="activeGuide.colorClass">
                                            {{ detail.step }}
                                        </div>
                                    </div>
                                    <div class="pb-2 flex-1">
                                        <h4 class="text-2xl font-bold text-[#0F172A] mb-3">{{ detail.title }}</h4>
                                        <p class="text-base md:text-lg text-[#64748B] leading-relaxed mb-6">{{ detail.content }}</p>
                                        
                                        <div v-if="detail.subItems && detail.subItems.length > 0" class="space-y-4 bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                                            <div v-for="(sub, sidx) in detail.subItems" :key="sidx" class="flex items-start gap-4 text-base text-[#475569]">
                                                <div class="mt-0.5 shrink-0 w-6 h-6 rounded-full flex items-center justify-center bg-white shadow-sm border border-slate-200" :class="activeGuide.colorClass.includes('emerald') ? 'text-emerald-500' : (activeGuide.colorClass.includes('amber') ? 'text-amber-500' : 'text-blue-500')">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <span class="leading-relaxed pt-0.5">{{ sub }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-8 md:px-12 py-8 border-t border-[#E2E8F0] bg-[#F8FAFC] flex justify-end gap-4">
                            <button @click="closeGuide" class="px-8 py-3.5 rounded-xl bg-white border border-[#E2E8F0] text-[#0F172A] font-bold shadow-sm hover:bg-gray-50 hover:shadow-md transition-all text-base">
                                Mengerti, Tutup Panduan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </Transition>
        </Teleport>
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

/* Modal Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.fade-enter-active .relative,
.fade-leave-active .relative {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-enter-from .relative,
.fade-leave-to .relative {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}

/* Custom Scrollbar for Modal */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #CBD5E1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94A3B8;
}
</style>
