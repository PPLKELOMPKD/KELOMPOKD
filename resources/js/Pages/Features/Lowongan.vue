<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({
    internships: { type: Array, default: () => [] },
    filterOptions: { type: Object, default: () => ({ companies: [], locations: [], workTypes: [], educationLevels: [], salaryRanges: [] }) },
});

const activeTab = ref('Semua Lowongan');

const filters = ref({
    posisi: '',
    lokasi: '',
    jenis: '',
    perusahaan: '',
    pendidikan: '',
    gaji: '',
    sort: 'terbaru'
});

// Dynamic filter options from backend data
const opsiLokasi = computed(() => props.filterOptions.locations);
const opsiJenis = computed(() => ['Semua Jenis', ...props.filterOptions.workTypes]);
const opsiPerusahaan = computed(() => ['Semua Perusahaan', ...props.filterOptions.companies]);
const opsiPendidikan = computed(() => ['Semua Jenjang', ...props.filterOptions.educationLevels]);
const opsiGaji = computed(() => ['Semua Gaji', ...props.filterOptions.salaryRanges]);

// Color map for work types
const typeColors = {
    'Magang': { bg: 'bg-[#ECFDF5]', text: 'text-[#059669]', dot: 'bg-[#10B981]' },
    'Magang WFO': { bg: 'bg-[#EFF6FF]', text: 'text-[#2563EB]', dot: 'bg-[#3B82F6]' },
    'Magang WFH': { bg: 'bg-[#F0FDF4]', text: 'text-[#16A34A]', dot: 'bg-[#22C55E]' },
    'Magang Hybrid': { bg: 'bg-[#FDF4FF]', text: 'text-[#9333EA]', dot: 'bg-[#A855F7]' },
    'Full-time': { bg: 'bg-[#EEF2FF]', text: 'text-[#4F46E5]', dot: 'bg-[#6366F1]' },
    'Part-time': { bg: 'bg-[#FFF7ED]', text: 'text-[#EA580C]', dot: 'bg-[#F97316]' },
    'Freelance': { bg: 'bg-[#FDF4FF]', text: 'text-[#9333EA]', dot: 'bg-[#A855F7]' },
};
const getTypeColor = (type) => typeColors[type] || typeColors['Magang'];

// Filtered + sorted internships
const filteredInternships = computed(() => {
    let result = [...props.internships];

    // Tab filter
    if (activeTab.value === 'Lowongan Magang') {
        result = result.filter(i => (i.work_type || 'Magang') === 'Magang');
    } else if (activeTab.value === 'Lowongan Kerja') {
        result = result.filter(i => (i.work_type || 'Magang') !== 'Magang');
    }

    // Search keyword
    if (filters.value.posisi.trim()) {
        const q = filters.value.posisi.toLowerCase();
        result = result.filter(i =>
            (i.title || '').toLowerCase().includes(q) ||
            (i.company_name || '').toLowerCase().includes(q) ||
            (i.description || '').toLowerCase().includes(q) ||
            (i.requirements || '').toLowerCase().includes(q)
        );
    }

    // Location filter
    if (filters.value.lokasi && filters.value.lokasi !== '') {
        const loc = filters.value.lokasi.toLowerCase();
        result = result.filter(i => (i.location || '').toLowerCase().includes(loc));
    }

    // Work type filter
    if (filters.value.jenis && filters.value.jenis !== '' && filters.value.jenis !== 'Semua Jenis') {
        result = result.filter(i => (i.work_type || 'Magang') === filters.value.jenis);
    }

    // Company filter
    if (filters.value.perusahaan && filters.value.perusahaan !== '' && filters.value.perusahaan !== 'Semua Perusahaan') {
        result = result.filter(i => i.company_name === filters.value.perusahaan);
    }

    // Pendidikan filter
    if (filters.value.pendidikan && filters.value.pendidikan !== '' && filters.value.pendidikan !== 'Semua Jenjang') {
        result = result.filter(i => i.education_level === filters.value.pendidikan);
    }

    // Gaji filter
    if (filters.value.gaji && filters.value.gaji !== '' && filters.value.gaji !== 'Semua Gaji') {
        result = result.filter(i => i.salary_range === filters.value.gaji);
    }

    // Sort
    if (filters.value.sort === 'terbaru') {
        result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    } else if (filters.value.sort === 'deadline') {
        result.sort((a, b) => new Date(a.deadline_at) - new Date(b.deadline_at));
    } else if (filters.value.sort === 'az') {
        result.sort((a, b) => a.title.localeCompare(b.title));
    } else if (filters.value.sort === 'za') {
        result.sort((a, b) => b.title.localeCompare(a.title));
    }

    return result;
});

const resetFilters = () => {
    filters.value = { posisi: '', lokasi: '', jenis: '', perusahaan: '', pendidikan: '', gaji: '', sort: 'terbaru' };
    activeTab.value = 'Semua Lowongan';
};

const hasActiveFilters = computed(() => {
    return filters.value.posisi || filters.value.lokasi || filters.value.jenis || filters.value.perusahaan || filters.value.pendidikan || filters.value.gaji || activeTab.value !== 'Semua Lowongan';
});

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const daysLeft = (d) => {
    if (!d) return null;
    const diff = Math.ceil((new Date(d) - new Date()) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
};
</script>

<template>
    <Head title="Cari Lowongan — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">


        <!-- Hero Header -->
        <div class="bg-gradient-to-b from-[#F1F5F9] to-white pb-12 pt-20 relative z-30 w-full">
            <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-extrabold text-[#0F172A] tracking-tight">Temukan <span class="text-[#2563EB]">Pekerjaan Impianmu</span></h1>
                    <p class="mt-4 text-[#64748B] max-w-2xl mx-auto">Jelajahi ribuan kesempatan magang dan kerja dari berbagai perusahaan ternama yang telah mempercayai talenta SIKARA.</p>
                </div>
                
                <!-- Filter Card -->
                <div class="w-full bg-white p-6 rounded-2xl shadow-xl shadow-[#2563EB]/5 border border-[#E2E8F0]">
                    <!-- Tabs -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button v-for="tab in ['Semua Lowongan', 'Lowongan Magang', 'Lowongan Kerja']" :key="tab"
                            @click="activeTab = tab"
                            class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="activeTab === tab ? 'bg-[#0F172A] text-white shadow-md' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:border-[#CBD5E1] hover:text-[#0F172A]'"
                        >{{ tab }}</button>
                    </div>

                    <!-- Main Search Row -->
                    <div class="flex flex-col md:flex-row gap-3 mb-3 relative z-30">
                        <div class="flex-grow flex items-center w-full bg-white border border-[#E2E8F0] hover:border-[#CBD5E1] transition-all duration-300 rounded-xl py-3 px-4 outline-none focus-within:ring-2 focus-within:ring-[#2563EB]/20 focus-within:border-[#2563EB]">
                            <svg class="h-5 w-5 text-[#94A3B8] mr-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" v-model="filters.posisi" placeholder="Cari posisi, kata kunci, atau perusahaan..." class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm text-[#0F172A] placeholder-[#94A3B8]" />
                        </div>
                        <div class="w-full md:w-[300px] relative z-30">
                            <SearchableSelect v-model="filters.lokasi" :options="opsiLokasi" placeholder="Lokasi" />
                        </div>
                        <button v-if="hasActiveFilters" @click="resetFilters" class="bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#64748B] p-3 rounded-xl transition-all flex items-center justify-center shrink-0" title="Reset Filter">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </div>

                    <!-- Secondary Filters Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 relative z-20">
                        <SearchableSelect v-model="filters.jenis" :options="opsiJenis" placeholder="Jenis Lowongan" />
                        <SearchableSelect v-model="filters.perusahaan" :options="opsiPerusahaan" placeholder="Perusahaan" />
                        <SearchableSelect v-model="filters.pendidikan" :options="opsiPendidikan" placeholder="Jenjang Pendidikan" />
                        <SearchableSelect v-model="filters.gaji" :options="opsiGaji" placeholder="Rentang Gaji" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Listings -->
        <div class="mx-auto w-full max-w-7xl px-6 lg:px-8 py-16 relative z-10">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">
                        {{ activeTab === 'Semua Lowongan' ? 'Lowongan Terbaru' : activeTab }}
                        <span class="text-[#2563EB]">({{ filteredInternships.length }})</span>
                    </h2>
                    <p v-if="hasActiveFilters" class="text-sm text-[#64748B] mt-1">
                        Menampilkan hasil filter dari {{ internships.length }} lowongan
                    </p>
                </div>
                <div class="flex items-center gap-2 text-sm font-medium text-[#64748B]">
                    <span>Urutkan:</span>
                    <div class="relative flex items-center">
                        <select v-model="filters.sort" class="!bg-none bg-transparent text-[#2563EB] font-bold border-none py-1 pl-0 pr-5 focus:ring-0 cursor-pointer appearance-none outline-none leading-none">
                            <option value="terbaru">Terbaru</option>
                            <option value="deadline">Deadline Terdekat</option>
                            <option value="az">A - Z</option>
                            <option value="za">Z - A</option>
                        </select>
                        <svg class="h-4 w-4 text-[#2563EB] absolute right-0 pointer-events-none" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="internship in filteredInternships" :key="internship.id"
                    class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-0 overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/10 hover:border-[#CBD5E1]"
                >
                    <!-- Card Header with gradient accent -->
                    <div class="relative">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#2563EB] to-[#60A5FA] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="p-6 pb-0">
                            <div class="flex items-start justify-between mb-4">
                                <!-- Company Logo -->
                                <div class="flex items-center gap-3">
                                    <div v-if="internship.company_logo" class="h-12 w-12 rounded-xl border border-[#E2E8F0] overflow-hidden shadow-sm bg-white flex items-center justify-center">
                                        <img :src="internship.company_logo" :alt="internship.company_name" class="h-full w-full object-contain" />
                                    </div>
                                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#2563EB] to-[#60A5FA] font-black text-white text-lg uppercase shadow-md shadow-[#2563EB]/20">
                                        {{ internship.company_name ? internship.company_name.charAt(0) : 'C' }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-[#64748B] line-clamp-1">{{ internship.company_name }}</p>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span :class="[getTypeColor(internship.work_type || 'Magang').bg, getTypeColor(internship.work_type || 'Magang').text]" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                                                <span :class="getTypeColor(internship.work_type || 'Magang').dot" class="h-1.5 w-1.5 rounded-full"></span>
                                                {{ internship.work_type || 'Magang' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Deadline Badge -->
                                <div v-if="daysLeft(internship.deadline_at) !== null" class="text-right shrink-0">
                                    <span :class="daysLeft(internship.deadline_at) <= 7 ? 'text-[#E11D48] bg-[#FFF1F2]' : 'text-[#64748B] bg-[#F1F5F9]'" class="inline-block rounded-lg px-2 py-1 text-[10px] font-bold">
                                        {{ daysLeft(internship.deadline_at) }} hari lagi
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-base font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors line-clamp-2 leading-snug">
                                {{ internship.title }}
                            </h3>

                            <!-- Description preview -->
                            <p class="mt-2 text-xs text-[#64748B] line-clamp-2 leading-relaxed">{{ internship.description }}</p>
                        </div>

                        <!-- Tags -->
                        <div class="px-6 pt-3 pb-4">
                            <div class="flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#475569] border border-[#E2E8F0]">
                                    <svg class="h-3 w-3 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ internship.location || 'Lokasi belum ditentukan' }}
                                </span>
                                <span v-if="internship.duration" class="inline-flex items-center gap-1 rounded-lg bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#475569] border border-[#E2E8F0]">
                                    <svg class="h-3 w-3 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ internship.duration }}
                                </span>
                                <span v-if="internship.salary" class="inline-flex items-center gap-1 rounded-lg bg-[#F8FAFC] px-2.5 py-1 text-[11px] font-medium text-[#475569] border border-[#E2E8F0]">
                                    <svg class="h-3 w-3 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    {{ internship.salary }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 pb-5 mt-auto">
                        <div class="flex items-center gap-2">
                            <Link 
                                :href="$page.props.auth.user ? route('internships.show', internship.id) : route('login', { role: 'mahasiswa' })" 
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-[#0F172A] py-2.5 text-center text-sm font-bold text-white transition-all hover:bg-[#2563EB] hover:shadow-lg hover:shadow-[#2563EB]/20"
                            >
                                Lihat Detail
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredInternships.length === 0" class="col-span-full flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-[#F8FAFC] py-16 px-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EFF6FF] mb-4">
                        <svg class="h-8 w-8 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2">Lowongan Tidak Ditemukan</h3>
                    <p class="text-sm text-[#64748B] max-w-md mb-6">Tidak ada lowongan yang sesuai dengan filter Anda. Coba ubah kata kunci atau reset filter untuk melihat semua lowongan.</p>
                    <button @click="resetFilters" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#1d4ed8] transition-all shadow-md shadow-[#2563EB]/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Reset Semua Filter
                    </button>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
