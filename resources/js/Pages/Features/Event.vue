<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
</script>

<template>
    <Head title="Pelatihan & Event — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">List Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Generate CV</Link>
        </template>

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)]">
            <!-- Header section -->
            <div class="bg-white border-b border-[#E2E8F0]">
                <div class="mx-auto max-w-6xl px-6 py-16 text-center animate-fade-in-up">
                    <h1 class="text-3xl font-extrabold text-[#0F172A]">Kalender <span class="text-[#2563EB]">Pelatihan & Event</span></h1>
                    <p class="mt-4 text-[#64748B] max-w-2xl mx-auto">Tingkatkan wawasan Anda melalui <strong>webinar</strong>, <strong>workshop</strong>, dan <strong>sesi pelatihan</strong> langsung dari para pakar industri di jaringan mitra SIKARA.</p>
                </div>
            </div>

            <!-- Search Area -->
            <div class="mx-auto max-w-5xl px-6 -mt-8 relative z-10 animate-fade-in-up" style="animation-delay: 100ms;">
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3 shadow-lg shadow-[#0F172A]/5 flex flex-col md:flex-row gap-2">
                    <div class="flex-1 flex items-center px-4 bg-[#F8FAFC] rounded-xl border border-transparent focus-within:border-[#2563EB]/30 transition-colors">
                        <svg class="h-5 w-5 text-[#94A3B8] mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" placeholder="Cari nama event atau topik..." class="w-full bg-transparent border-none py-3 outline-none text-[#0F172A] text-sm" />
                    </div>
                    <div class="md:w-48">
                        <select class="w-full h-full bg-[#F8FAFC] border border-transparent rounded-xl px-4 py-3 outline-none text-sm text-[#0F172A] appearance-none focus:border-[#2563EB]/30 transition-colors">
                            <option value="">Semua Lokasi</option>
                            <option value="online">Online (Zoom/Meet)</option>
                            <option value="offline">Offline (Onsite)</option>
                        </select>
                    </div>
                    <div class="md:w-48">
                        <select class="w-full h-full bg-[#F8FAFC] border border-transparent rounded-xl px-4 py-3 outline-none text-sm text-[#0F172A] appearance-none focus:border-[#2563EB]/30 transition-colors">
                            <option value="">Semua Tipe</option>
                            <option value="webinar">Webinar</option>
                            <option value="workshop">Workshop</option>
                        </select>
                    </div>
                    <button class="bg-[#0F172A] hover:bg-[#1E293B] text-white font-bold py-3 px-8 rounded-xl transition-colors text-sm w-full md:w-auto flex items-center justify-center gap-2">
                        Cari Event
                    </button>
                </div>
            </div>

            <!-- Empty State Area -->
            <div class="mx-auto max-w-6xl px-6 py-24 animate-fade-in text-center">
                <div class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-[#F1F5F9] mb-6">
                    <svg class="h-10 w-10 text-[#CBD5E1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                </div>
                <h3 class="text-xl font-bold text-[#0F172A]">Belum Ada Event Terjadwal</h3>
                <p class="mt-2 text-[#64748B] max-w-md mx-auto">Saat ini belum ada event atau pelatihan baru yang dibuka oleh mitra kami. Silakan cek kembali beberapa saat lagi.</p>
            </div>
            
            <!-- Minimal Footer -->
            <footer class="bg-white border-t border-[#E2E8F0] mt-auto">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
                    <div class="flex items-center gap-3">
                        <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-5 w-auto opacity-70" />
                        <span class="text-xs font-bold text-[#64748B]">SIKARA EVENT</span>
                    </div>
                    <p class="text-xs font-medium text-[#94A3B8]">© 2026 Portal Peserta</p>
                </div>
            </footer>
        </div>
    </PortalLayout>
</template>
