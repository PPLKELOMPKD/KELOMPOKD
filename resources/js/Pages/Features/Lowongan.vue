<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import locationsData from '@/Data/locations.json';

// 1. TAMBAHKAN PROPS UNTUK MENERIMA DATA DARI DATABASE
const props = defineProps({
    internships: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('Semua Lowongan');

const filters = ref({
    posisi: '',
    lokasi: '',
    jenis: '',
    perusahaan: '',
    jurusan: '',
    pendidikan: '',
    sort: 'relevan'
});

const opsiJenis = ['Semua Lowongan', 'Magang', 'Full Time', 'Part Time', 'Project Base'];
const opsiPerusahaan = ['Semua Perusahaan', 'PT Telekomunikasi Indonesia', 'PT Kimia Farma Tbk', 'GoTo Group', 'Shopee Indonesia', 'Tokopedia', 'Traveloka', 'BCA', 'Bank Mandiri'];
const opsiJurusan = ['Semua Jurusan', 'Teknik Informatika', 'Sistem Informasi', 'Ilmu Komputer', 'Desain Komunikasi Visual', 'Manajemen', 'Akuntansi', 'Ilmu Komunikasi'];
const opsiPendidikan = ['Semua Jenjang', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];
</script>

<template>
    <Head title="Cari Lowongan — SIKARA" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">List Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Generate CV</Link>
        </template>

        <!-- Fresh SIKARA Header (Tetap Sama) -->
        <div class="bg-gradient-to-b from-[#F1F5F9] to-white pb-12 pt-20 relative z-30">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-10">
                    <h1 class="text-4xl font-extrabold text-[#0F172A] tracking-tight">Temukan <span class="text-[#2563EB]">Pekerjaan Impianmu</span></h1>
                    <p class="mt-4 text-[#64748B] max-w-2xl mx-auto">Jelajahi ribuan kesempatan magang dan kerja dari berbagai perusahaan ternama yang telah mempercayai talenta SIKARA.</p>
                </div>
                
                <!-- Modern Filter Card (Tetap Sama) -->
                <div class="w-full bg-white p-6 rounded-2xl shadow-xl shadow-[#2563EB]/5 border border-[#E2E8F0]">
                    <!-- Tabs -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button 
                            @click="activeTab = 'Semua Lowongan'"
                            class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="activeTab === 'Semua Lowongan' ? 'bg-[#0F172A] text-white shadow-md' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:border-[#CBD5E1] hover:text-[#0F172A]'"
                        >
                            Semua Lowongan
                        </button>
                        <button 
                            @click="activeTab = 'Lowongan Magang'"
                            class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="activeTab === 'Lowongan Magang' ? 'bg-[#0F172A] text-white shadow-md' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:border-[#CBD5E1] hover:text-[#0F172A]'"
                        >
                            Lowongan Magang
                        </button>
                        <button 
                            @click="activeTab = 'Lowongan Kerja'"
                            class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="activeTab === 'Lowongan Kerja' ? 'bg-[#0F172A] text-white shadow-md' : 'bg-white border border-[#E2E8F0] text-[#64748B] hover:border-[#CBD5E1] hover:text-[#0F172A]'"
                        >
                            Lowongan Kerja
                        </button>
                    </div>

                    <!-- Main Search Row -->
                    <div class="flex flex-col md:flex-row gap-3 mb-3 relative z-30">
                        <div class="flex-grow flex items-center w-full bg-white border border-[#E2E8F0] hover:border-[#CBD5E1] transition-all duration-300 rounded-xl py-3 px-4 outline-none focus-within:ring-2 focus-within:ring-[#2563EB]/20 focus-within:border-[#2563EB]">
                            <input 
                                type="text" 
                                v-model="filters.posisi"
                                placeholder="Posisi, kata kunci, atau perusahaan..." 
                                class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm text-[#0F172A] placeholder-[#94A3B8]" 
                            />
                        </div>
                        <div class="w-full md:w-[300px]">
                            <SearchableSelect v-model="filters.lokasi" :options="locationsData" placeholder="Lokasi" />
                        </div>
                        <button class="bg-[#2563EB] hover:bg-[#1d4ed8] text-white p-3 rounded-xl transition-all shadow-md shadow-[#2563EB]/20 flex items-center justify-center shrink-0 w-full md:w-auto px-8 md:px-5">
                            <svg class="h-5 w-5 mr-2 md:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <span class="md:hidden font-bold">Cari</span>
                            <svg class="h-6 w-6 hidden md:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                    </div>

                    <!-- Secondary Filters Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 relative z-20">
                        <SearchableSelect v-model="filters.jenis" :options="opsiJenis" placeholder="Jenis Lowongan" />
                        <SearchableSelect v-model="filters.perusahaan" :options="opsiPerusahaan" placeholder="Perusahaan" />
                        <SearchableSelect v-model="filters.jurusan" :options="opsiJurusan" placeholder="Jurusan" />
                        <SearchableSelect v-model="filters.pendidikan" :options="opsiPendidikan" placeholder="Jenjang Pendidikan" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Listings Content -->
        <div class="mx-auto max-w-7xl px-6 py-16 relative z-10">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <!-- 2. TOTAL LOWONGAN DINAMIS -->
                <h2 class="text-xl font-bold text-[#0F172A]">Lowongan Terbaru ({{ internships.length }})</h2>
                <div class="flex items-center gap-2 text-sm font-medium text-[#64748B]">
                    <span>Urutkan:</span>
                    <div class="relative flex items-center">
                        <select v-model="filters.sort" class="!bg-none bg-transparent text-[#2563EB] font-bold border-none py-1 pl-0 pr-5 focus:ring-0 cursor-pointer appearance-none outline-none leading-none">
                            <option value="relevan">Paling Relevan</option>
                            <option value="terbaru">Terbaru</option>
                            <option value="terpopuler">Terpopuler</option>
                            <option value="gaji_tertinggi">Gaji Tertinggi</option>
                        </select>
                        <svg class="h-4 w-4 text-[#2563EB] absolute right-0 pointer-events-none" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                
                <!-- 3. LOOPING DATA DATABASE -->
                <div 
                    v-for="internship in internships" 
                    :key="internship.id"
                    class="group flex flex-col justify-between rounded-2xl border border-[#E2E8F0] bg-white p-6 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2563EB]/10"
                >
                    <div>
                        <div class="flex items-start justify-between mb-4">
                            <!-- Inisial Nama Perusahaan -->
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#F1F5F9] font-black text-[#2563EB] text-xl uppercase">
                                {{ internship.company_name ? internship.company_name.charAt(0) : 'C' }}
                            </div>
                            <!-- Tipe Pekerjaan -->
                            <span class="rounded-full bg-[#ECFDF5] px-3 py-1 text-xs font-bold text-[#10B981]">
                                {{ internship.work_type || 'Magang' }}
                            </span>
                        </div>
                        <!-- Judul dan Nama Perusahaan -->
                        <h3 class="text-lg font-bold text-[#0F172A] group-hover:text-[#2563EB] transition-colors line-clamp-1">
                            {{ internship.title }}
                        </h3>
                        <p class="mt-1 text-sm font-medium text-[#64748B] line-clamp-1">{{ internship.company_name }}</p>
                        
                        <div class="mt-4 flex flex-wrap gap-2">
                            <!-- Lokasi -->
                            <span class="inline-flex items-center gap-1 rounded bg-[#F8FAFC] px-2 py-1 text-xs text-[#64748B] border border-[#E2E8F0]">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/></svg>
                                {{ internship.location || 'Lokasi tidak disebutkan' }}
                            </span>
                            <!-- Durasi -->
                            <span v-if="internship.duration" class="inline-flex items-center gap-1 rounded bg-[#F8FAFC] px-2 py-1 text-xs text-[#64748B] border border-[#E2E8F0]">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ internship.duration }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-8">
                        <!-- 4. PERBAIKAN LOGIKA TAUTAN LOGIN / DETAIL -->
                        <Link 
                            :href="$page.props.auth.user ? route('internships.show', internship.id) : route('login', { role: 'mahasiswa' })" 
                            class="block w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] py-2.5 text-center text-sm font-bold text-[#0F172A] transition-colors hover:bg-[#0F172A] hover:text-white"
                        >
                            Lihat Detail & Daftar
                        </Link>
                    </div>
                </div>

                <!-- 5. KONDISI JIKA DATA KOSONG -->
                <div v-if="internships.length === 0" class="col-span-full flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#E2E8F0] bg-[#F8FAFC] py-12 px-6 text-center opacity-70">
                    <svg class="h-10 w-10 text-[#94A3B8] mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <p class="text-sm font-medium text-[#64748B]">Lowongan belum tersedia saat ini.</p>
                </div>

            </div>
        </div>
    </PortalLayout>
</template>