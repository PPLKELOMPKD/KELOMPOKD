<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
</script>

<template>
    <Head title="LMS — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">


        <div class="mx-auto flex min-h-[calc(100vh-76px)] max-w-7xl flex-col items-center justify-between gap-12 px-6 py-12 lg:flex-row lg:py-16 relative">
            <div class="max-w-xl animate-fade-in-up">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#2563EB]/20 bg-[#EFF6FF] px-4 py-1.5 shadow-sm">
                    <span class="text-xs font-black text-[#2563EB] tracking-widest uppercase">Sistem Manajemen Pembelajaran</span>
                </div>

                <h1 class="text-4xl font-black leading-tight text-[#0F172A] lg:text-6xl">
                    Tingkatkan <strong>Skill</strong><br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#2563EB] to-[#10B981]">Sebelum Terjun ke Industri</span>
                </h1>

                <p class="mt-6 text-base text-[#64748B] leading-relaxed">
                    Akses ratusan materi pembelajaran interaktif, tugas praktikal, dan kuis evaluasi. SIKARA LMS dirancang bersama pakar industri agar keahlian yang Anda pelajari 100% relevan dengan kebutuhan pasar saat ini.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <Link :href="route('login', { role: 'mahasiswa' })" class="rounded-xl bg-[#0F172A] px-8 py-4 text-sm font-bold text-white transition-all hover:bg-[#1E293B] hover:-translate-y-0.5 hover:shadow-lg flex items-center gap-2">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Mulai Belajar
                    </Link>
                </div>
                
                <div class="mt-10 flex gap-8 border-t border-[#E2E8F0] pt-6">
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">250+</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Materi</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">50+</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Mentor</div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-[#0F172A]">100%</div>
                        <div class="text-xs font-semibold text-[#64748B] uppercase tracking-wider mt-1">Gratis</div>
                    </div>
                </div>
            </div>

            <!-- Distinctive SIKARA Visual Representation -->
            <div class="relative w-full max-w-lg lg:max-w-xl animate-fade-in-up" style="animation-delay: 200ms;">
                <div class="absolute -inset-4 rounded-[3rem] bg-gradient-to-tr from-[#2563EB] to-[#10B981] opacity-20 blur-2xl"></div>
                
                <div class="relative w-full overflow-hidden rounded-[2rem] border border-[#E2E8F0] bg-white shadow-2xl">
                    <!-- Fake browser header -->
                    <div class="bg-[#F8FAFC] border-b border-[#E2E8F0] px-4 py-3 flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-red-400"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                        <div class="h-3 w-3 rounded-full bg-green-400"></div>
                    </div>
                    <!-- Fake LMS Content -->
                    <div class="p-8">
                        <div class="h-8 w-48 rounded bg-[#F1F5F9] mb-8"></div>
                        
                        <div class="flex gap-6 mb-6 items-center p-4 rounded-xl border border-[#E2E8F0]">
                            <div class="h-16 w-16 shrink-0 rounded-lg bg-[#2563EB] flex items-center justify-center">
                                <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="w-full">
                                <div class="h-4 w-3/4 rounded bg-[#0F172A] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                                <div class="h-2 w-1/2 rounded bg-[#10B981] mt-1"></div>
                            </div>
                        </div>
                        <div class="flex gap-6 mb-6 items-center p-4 rounded-xl border border-[#E2E8F0]">
                            <div class="h-16 w-16 shrink-0 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center">
                                <svg class="h-8 w-8 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            </div>
                            <div class="w-full">
                                <div class="h-4 w-2/3 rounded bg-[#94A3B8] mb-2"></div>
                                <div class="h-2 w-full rounded bg-[#E2E8F0]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
