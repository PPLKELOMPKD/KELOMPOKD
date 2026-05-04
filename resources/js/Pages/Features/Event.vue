<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    events: { type: Array, default: () => [] },
});

const filters = ref({
    keyword: '',
    lokasi: '',
    tipe: ''
});

const filteredEvents = computed(() => {
    let result = [...props.events];

    if (filters.value.keyword.trim()) {
        const q = filters.value.keyword.toLowerCase();
        result = result.filter(e => 
            (e.title || '').toLowerCase().includes(q) ||
            (e.company?.name || '').toLowerCase().includes(q) ||
            (e.description || '').toLowerCase().includes(q)
        );
    }

    if (filters.value.lokasi && filters.value.lokasi !== '') {
        const loc = filters.value.lokasi.toLowerCase();
        if (loc === 'online') {
            result = result.filter(e => (e.type || '').toLowerCase() === 'online');
        } else if (loc === 'offline') {
            result = result.filter(e => (e.type || '').toLowerCase() === 'offline');
        }
    }

    if (filters.value.tipe && filters.value.tipe !== '') {
        const t = filters.value.tipe.toLowerCase();
        result = result.filter(e => (e.title || '').toLowerCase().includes(t));
    }

    return result;
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};
</script>

<template>
    <Head title="Event — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">

        <div class="bg-[#F8FAFC] min-h-[calc(100vh-130px)] pb-24 relative w-full">
            <!-- Hero Background -->
            <div class="absolute top-0 left-0 right-0 h-[300px] bg-gradient-to-b from-[#E2E8F0] to-[#F8FAFC] z-0"></div>

            <div class="mx-auto w-full max-w-7xl px-6 pt-12 relative z-10">
                <!-- Search Card -->
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 shadow-xl shadow-[#2563EB]/5 mb-12">
                    <Link :href="route('peserta')" class="inline-flex items-center text-sm font-bold text-[#2563EB] hover:text-[#1d4ed8] transition-colors mb-6">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Beranda
                    </Link>

                    <h1 class="text-3xl font-extrabold text-[#0F172A] mb-8">Eksplorasi <span class="text-[#2563EB]">Event</span></h1>

                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="flex-1 w-full relative">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-[#94A3B8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" v-model="filters.keyword" placeholder="Cari Event atau Nama Perusahaan..." class="w-full rounded-xl border border-[#E2E8F0] pl-11 pr-4 py-3 text-sm outline-none placeholder:text-[#94A3B8] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all" />
                        </div>
                        <div class="w-full md:w-56">
                            <div class="relative">
                                <select v-model="filters.lokasi" class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                    <option value="">Semua Lokasi</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-56">
                            <div class="relative">
                                <select v-model="filters.tipe" class="w-full appearance-none rounded-xl border border-[#E2E8F0] px-4 py-3 text-sm outline-none text-[#64748B] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 transition-all cursor-pointer">
                                    <option value="">Semua Tipe</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="seminar">Seminar</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Header -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-[#0F172A]">Event Tersedia <span class="text-[#2563EB]">({{ filteredEvents.length }})</span></h2>
                </div>

                <!-- Event Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="event in filteredEvents" :key="event.id" class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/10 hover:border-[#CBD5E1]">
                        <!-- Card Header -->
                        <div class="relative p-6 pb-4">
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#10B981] to-[#34D399] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            
                            <div class="flex items-start justify-between mb-4">
                                <!-- Company & Type -->
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F1F5F9] font-bold text-[#475569] text-xs uppercase shadow-sm">
                                            {{ event.company?.name ? event.company.name.charAt(0) : 'C' }}
                                        </div>
                                        <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">{{ event.company?.name || 'Perusahaan' }}</p>
                                    </div>
                                </div>
                                <span :class="event.type === 'online' ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-[#F0FDF4] text-[#16A34A] border-[#BBF7D0]'" class="inline-block rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wider">
                                    {{ event.type }}
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors leading-snug line-clamp-2 mb-2">
                                {{ event.title }}
                            </h3>
                            
                            <p class="text-sm text-[#64748B] line-clamp-2 leading-relaxed">{{ event.description }}</p>
                        </div>

                        <!-- Card Info -->
                        <div class="px-6 py-4 bg-[#F8FAFC] border-t border-b border-[#E2E8F0]">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <div class="font-medium">
                                        {{ formatDate(event.date) }}
                                        <div class="text-xs text-[#94A3B8] mt-0.5">{{ formatTime(event.start_time) }} - {{ formatTime(event.end_time) }} WIB</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <span class="font-medium line-clamp-1">{{ event.location }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-[#475569]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-sm border border-[#E2E8F0]">
                                        <svg class="h-4 w-4 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    </div>
                                    <span class="font-medium">Kuota: {{ event.max_participants }} Peserta</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-6">
                            <button class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-3 text-center text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20">
                                Daftar Event
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredEvents.length === 0" class="mt-12 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-white py-16 px-6 text-center shadow-sm">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] mb-4">
                        <svg class="h-8 w-8 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2">Event Tidak Ditemukan</h3>
                    <p class="text-sm text-[#64748B] max-w-md">Tidak ada event yang sesuai dengan pencarian Anda. Coba ubah kata kunci atau filter untuk menemukan event lainnya.</p>
                </div>

            </div>
        </div>
    </PortalLayout>
</template>
