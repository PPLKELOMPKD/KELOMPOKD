<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

defineProps({
    companies: Array
});
</script>

<template>
    <Head title="Direktori Perusahaan — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <!-- Elegant Hero Banner -->
        <div class="bg-[#0F172A] relative overflow-hidden py-24">
            <!-- Decorative Elements -->
            <div class="absolute left-0 top-0 h-full w-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
            <div class="absolute -right-32 top-0 h-[40rem] w-[40rem] rounded-full border border-white/5 bg-gradient-to-tr from-[#2563EB]/20 to-[#10B981]/20 blur-3xl"></div>

            <div class="mx-auto max-w-4xl px-6 relative z-10 text-center animate-fade-in-up">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Direktori <span class="text-[#10B981]">Mitra Perusahaan</span></h1>
                <p class="mt-4 text-[#94A3B8]">Jelajahi profil lengkap dari 200+ perusahaan terkemuka yang siap menjadi tempat Anda mengembangkan karier.</p>
                
                <div class="mt-10 mx-auto max-w-2xl bg-white/10 backdrop-blur-md p-2 rounded-2xl border border-white/20 flex gap-2">
                    <div class="flex-1 flex items-center px-4">
                        <svg class="h-5 w-5 text-white/70 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" placeholder="Cari nama perusahaan atau industri..." class="w-full bg-transparent border-none py-3 outline-none text-white placeholder-white/50" />
                    </div>
                    <button class="bg-[#10B981] hover:bg-[#059669] text-white font-bold py-3 px-8 rounded-xl transition-colors">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-6 py-16 animate-fade-in">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="company in companies" :key="company.id" class="rounded-2xl border border-[#E2E8F0] bg-white p-6 transition-all hover:-translate-y-1 hover:border-[#10B981] hover:shadow-xl hover:shadow-[#10B981]/10">
                    <div class="flex items-center justify-between border-b border-[#F1F5F9] pb-4 mb-4">
                        <div class="h-12 w-24 bg-[#F8FAFC] rounded-lg flex items-center justify-center text-sm font-black text-[#2563EB] truncate px-2">
                            {{ company.name.substring(0, 10).toUpperCase() }}
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-[#64748B] block uppercase">Rating</span>
                            <span class="text-sm font-bold text-[#F59E0B] flex items-center gap-1 justify-end"><svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg> {{ company.rating }}</span>
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold text-[#0F172A]">{{ company.name }}</h3>
                    <p class="mt-1 text-sm text-[#64748B]">{{ company.industry }}</p>
                    
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-xs text-[#94A3B8]">Lowongan Aktif</span>
                            <span class="text-lg font-black text-[#0F172A]">{{ company.active_jobs }}</span>
                        </div>
                        <Link :href="route('perusahaan.profile', company.id)" class="text-sm font-bold text-[#10B981] hover:text-[#059669] flex items-center gap-1">
                            Kunjungi Profil
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
