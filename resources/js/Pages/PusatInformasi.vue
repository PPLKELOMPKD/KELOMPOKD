<script setup>
import { Head } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { onMounted, ref } from 'vue';

const isVisible = ref(false);

onMounted(() => {
    isVisible.value = true;
});

const activeFaq = ref(null);

const faqs = [
    {
        question: 'Apa itu SIKARA?',
        answer: 'SIKARA (Sistem Informasi Karir Mahasiswa) adalah platform ekosistem terpadu yang dirancang untuk menjembatani kesenjangan antara dunia akademik dan profesional. SIKARA membantu mahasiswa menemukan lowongan magang, mengembangkan keterampilan melalui LMS, dan memfasilitasi perusahaan dalam mencari talenta muda berbakat.'
    },
    {
        question: 'Apakah SIKARA gratis untuk mahasiswa?',
        answer: 'Ya, seluruh layanan utama di SIKARA sepenuhnya GRATIS untuk mahasiswa. Ini termasuk pembuatan CV, mengakses materi pembelajaran di LMS, hingga melamar berbagai lowongan pekerjaan dan magang yang tersedia di platform kami.'
    },
    {
        question: 'Bagaimana cara perusahaan mendaftar?',
        answer: 'Perusahaan dapat mendaftar dengan mengklik tombol "Daftar" pada halaman utama, lalu memilih opsi "Daftar sebagai Perusahaan". Anda akan diminta untuk mengisi profil perusahaan, dokumen legalitas, dan informasi PIC sebelum akun diverifikasi oleh tim SIKARA.'
    },
    {
        question: 'Apakah sertifikat dari LMS SIKARA diakui?',
        answer: 'Sertifikat yang diterbitkan setelah menyelesaikan kursus di LMS SIKARA diakui oleh seluruh perusahaan mitra yang terdaftar dalam ekosistem SIKARA. Sertifikat ini juga akan secara otomatis terintegrasi ke dalam profil dan CV Anda di platform.'
    },
    {
        question: 'Bagaimana cara menghubungi tim dukungan SIKARA?',
        answer: 'Anda dapat menghubungi tim dukungan kami melalui email di cs@sikara.id, atau menggunakan formulir kontak yang tersedia di bagian bawah halaman Pusat Informasi ini. Tim kami beroperasi pada hari kerja, pukul 08:00 - 17:00 WIB.'
    }
];

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

const contacts = [
    {
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        title: 'Email',
        info: 'cs@sikara.id',
        desc: 'Untuk pertanyaan umum dan bantuan teknis'
    },
    {
        icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        title: 'Telepon',
        info: '+62 811 1234 5678',
        desc: 'Senin - Jumat, 08:00 - 17:00 WIB'
    },
    {
        icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        title: 'Kantor Pusat',
        info: 'Gedung SIKARA, Lt. 5',
        desc: 'Jl. Pendidikan No. 123, Jakarta Selatan'
    }
];
</script>

<template>
    <Head title="Pusat Informasi | SIKARA" />

    <PortalLayout>
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-b from-[#0F172A] to-[#1E293B] overflow-hidden pt-24 pb-32">
            <div class="absolute inset-0 bg-[url('/images/pattern-grid.svg')] opacity-5"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#2563EB] rounded-full blur-[128px] opacity-20"></div>
            
            <div class="mx-auto max-w-4xl px-6 relative z-10 text-center" :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'" style="transition: all 1s ease-out;">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 backdrop-blur-md border border-white/20">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-xs font-bold text-white tracking-wider uppercase">Bantuan & Informasi</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-6 tracking-tight">
                    Pusat <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#3B82F6] to-[#60A5FA]">Informasi</span> SIKARA
                </h1>
                <p class="text-lg text-[#94A3B8] leading-relaxed mb-8">
                    Temukan jawaban atas pertanyaan umum Anda dan pelajari lebih lanjut bagaimana SIKARA dapat membantu mewujudkan tujuan karir Anda.
                </p>
                
                <!-- Search Bar Placeholder -->
                <div class="relative max-w-xl mx-auto">
                    <input type="text" placeholder="Cari topik bantuan atau pertanyaan..." class="w-full pl-12 pr-4 py-4 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-white/50 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-[#3B82F6] focus:border-transparent transition-all" />
                    <svg class="w-6 h-6 text-white/50 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>
            
            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block w-[calc(100%+1.3px)] h-[50px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,111.47,192.59,96.5,238.13,85.36,281.44,71.62,321.39,56.44Z" class="fill-[#F8FAFC]"></path>
                </svg>
            </div>
        </section>

        <section class="bg-[#F8FAFC] py-20 pb-32">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid lg:grid-cols-12 gap-12">
                    
                    <!-- Contact Info Cards -->
                    <div class="lg:col-span-4 space-y-6" :class="isVisible ? 'translate-x-0 opacity-100' : '-translate-x-12 opacity-0'" style="transition: all 1s ease-out 0.2s;">
                        <h2 class="text-2xl font-bold text-[#0F172A] mb-6">Hubungi Kami</h2>
                        
                        <div v-for="(contact, index) in contacts" :key="index" class="bg-white p-6 rounded-2xl shadow-sm border border-[#E2E8F0] flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-[#2563EB]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="contact.icon" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-[#0F172A] mb-1">{{ contact.title }}</h4>
                                <p class="text-[#2563EB] font-medium mb-1">{{ contact.info }}</p>
                                <p class="text-xs text-[#64748B]">{{ contact.desc }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="lg:col-span-8" :class="isVisible ? 'translate-x-0 opacity-100' : 'translate-x-12 opacity-0'" style="transition: all 1s ease-out 0.4s;">
                        <h2 class="text-3xl font-black text-[#0F172A] mb-8">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                        
                        <div class="space-y-4">
                            <div v-for="(faq, index) in faqs" :key="index" 
                                 class="bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden transition-all duration-300"
                                 :class="{'shadow-md border-blue-200': activeFaq === index}">
                                <button 
                                    @click="toggleFaq(index)" 
                                    class="w-full px-6 py-5 text-left flex justify-between items-center focus:outline-none"
                                >
                                    <span class="font-bold text-lg" :class="activeFaq === index ? 'text-[#2563EB]' : 'text-[#0F172A]'">
                                        {{ faq.question }}
                                    </span>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors" :class="activeFaq === index ? 'bg-blue-50 text-[#2563EB]' : 'bg-[#F8FAFC] text-[#94A3B8]'">
                                        <svg class="w-5 h-5 transform transition-transform duration-300" :class="{'rotate-180': activeFaq === index}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>
                                
                                <div 
                                    class="overflow-hidden transition-all duration-300 ease-in-out"
                                    :style="{ maxHeight: activeFaq === index ? '500px' : '0' }"
                                >
                                    <div class="px-6 pb-6 pt-0 text-[#64748B] leading-relaxed border-t border-transparent" :class="{'border-gray-100 mt-2 pt-4': activeFaq === index}">
                                        {{ faq.answer }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
    </PortalLayout>
</template>
