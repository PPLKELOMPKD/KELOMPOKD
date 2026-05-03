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

        <div class="bg-white min-h-[calc(100vh-130px)] pb-24">
            <div class="mx-auto max-w-6xl px-6 pt-12">
                <!-- Search Card -->
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-8 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                    <Link :href="route('peserta')" class="inline-flex items-center text-sm font-semibold text-[#2563EB] hover:underline mb-8">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali
                    </Link>

                    <h1 class="text-2xl font-bold text-[#0F172A] mb-6">Daftar Event</h1>

                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="flex-1 w-full">
                            <input type="text" placeholder="Cari Event" class="w-full rounded-lg border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none placeholder:text-[#94A3B8] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all" />
                        </div>
                        <div class="w-full md:w-64">
                            <div class="relative">
                                <select class="w-full appearance-none rounded-lg border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all">
                                    <option value="">Lokasi</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-64">
                            <div class="relative">
                                <select class="w-full appearance-none rounded-lg border border-[#E2E8F0] px-4 py-2.5 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition-all">
                                    <option value="">Tipe</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="workshop">Workshop</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <button class="flex h-[42px] w-[42px] items-center justify-center rounded-lg bg-[#0F172A] text-white hover:bg-[#1E293B] transition-colors shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Empty State Area -->
                <div class="mt-24 text-center">
                    <p class="text-[#94A3B8] font-medium text-[15px]">Data Event tidak ditemukan</p>
                </div>
            </div>
            
            <!-- Large Footer like Magenta -->
            <footer class="bg-white border-t border-[#E2E8F0] mt-32">
                <div class="mx-auto max-w-6xl px-6 py-12">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="col-span-1 md:col-span-2">
                            <div class="flex items-center gap-3 mb-6">
                                <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-8 w-auto" />
                                <span class="text-xl font-black tracking-tight text-[#0F172A]">SIKARA</span>
                            </div>
                            <h3 class="text-lg font-bold text-[#0F172A] mb-2">SIKARA Indonesia</h3>
                            <p class="text-sm text-[#64748B]">Email: <a href="mailto:cs@sikara.id" class="text-[#2563EB] hover:underline">cs@sikara.id</a></p>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#0F172A] mb-6">Peserta</h4>
                            <ul class="space-y-4">
                                <li><Link :href="route('lowongan')" class="text-sm text-[#64748B] hover:text-[#2563EB]">Cari Lowongan</Link></li>
                                <li><Link :href="route('event')" class="text-sm text-[#64748B] hover:text-[#2563EB]">Cari Event</Link></li>
                                <li><Link :href="route('perusahaan-list')" class="text-sm text-[#64748B] hover:text-[#2563EB]">Daftar Perusahaan</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#0F172A] mb-6">Tentang SIKARA</h4>
                            <ul class="space-y-4">
                                <li><a href="#" class="text-sm text-[#64748B] hover:text-[#2563EB]">Panduan</a></li>
                                <li><a href="#" class="text-sm text-[#64748B] hover:text-[#2563EB]">Pusat Informasi</a></li>
                                <li><Link :href="route('tentang')" class="text-sm text-[#64748B] hover:text-[#2563EB]">Tentang Kami</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </PortalLayout>
</template>
