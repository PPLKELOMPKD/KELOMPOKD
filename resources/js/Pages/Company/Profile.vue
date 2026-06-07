<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';

const props = defineProps({
    company: Object,
    isOwner: {
        type: Boolean,
        default: false,
    },
});

const openVisi = ref(false);
const openMisi = ref(false);
const showEditModal = ref(false);
const activeEditSection = ref('identity');

const editSections = {
    identity: {
        title: 'Edit Identitas Perusahaan',
        description: 'Perbarui nama, industri, lokasi, website, logo, dan cover profil.',
    },
    about: {
        title: 'Edit Tentang Perusahaan',
        description: 'Perbarui deskripsi, visi, dan misi perusahaan.',
    },
    companyInfo: {
        title: 'Edit Informasi Perusahaan',
        description: 'Perbarui tahun berdiri, jumlah karyawan, dan spesialisasi.',
    },
    contact: {
        title: 'Edit Kontak & Lokasi',
        description: 'Perbarui alamat kantor perusahaan.',
    },
};

const activeEditConfig = computed(() => editSections[activeEditSection.value] ?? editSections.identity);
const layoutComponent = computed(() => props.isOwner ? SikaraLayout : PortalLayout);
const layoutProps = computed(() => props.isOwner
    ? {
        title: 'Profil Perusahaan',
        subtitle: '',
        showHeader: false,
        contentClass: 'p-0',
    }
    : {
        activeRole: 'peserta',
        loginRole: 'mahasiswa',
    });

const specializationsToText = (specializations) => {
    if (!specializations) return '';
    return Array.isArray(specializations) ? specializations.join(', ') : String(specializations);
};

const form = useForm({
    name: props.company.name || '',
    industry: props.company.profile?.industry || '',
    location: props.company.profile?.location || '',
    website: props.company.profile?.website || '',
    description: props.company.profile?.description || '',
    vision: props.company.profile?.vision || '',
    mission: props.company.profile?.mission || '',
    founded_year: props.company.profile?.founded_year || '',
    employee_count: props.company.profile?.employee_count || '',
    specializations: specializationsToText(props.company.profile?.specializations),
    office_address: props.company.profile?.office_address || '',
    logo: null,
    cover: null,
});

const openEditModal = (section = 'identity') => {
    activeEditSection.value = section;
    window.scrollTo({
        top: Math.max((document.documentElement.scrollHeight - window.innerHeight) / 2, 0),
        behavior: 'auto',
    });
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    form.clearErrors();
    form.logo = null;
    form.cover = null;
};

const submitProfile = () => {
    form.post(route('perusahaan.profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: closeEditModal,
    });
};

watch(showEditModal, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <Head :title="company.name + ' — SIKARA'" />

    <component :is="layoutComponent" v-bind="layoutProps">
        <!-- Hero Section -->
        <section class="relative w-full h-[450px] overflow-hidden">
            <img class="w-full h-full object-cover" :src="company.profile?.cover_path || 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop'" :alt="company.name" />
            <div class="absolute inset-0 bg-gradient-to-t from-[#0b1c30]/90 via-[#0b1c30]/40 to-transparent"></div>
            
            <div class="absolute top-6 left-0 w-full z-20">
                <div class="max-w-7xl mx-auto px-6">
                    <Link :href="isOwner ? route('perusahaan.dashboard') : route('perusahaan-list')" class="inline-flex bg-white/10 backdrop-blur-md text-white px-4 py-2 rounded-xl items-center gap-2 hover:bg-white/20 transition-all font-semibold border border-white/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        {{ isOwner ? 'Kembali ke Dashboard' : 'Kembali ke Daftar Perusahaan' }}
                    </Link>
                </div>
            </div>
            <button
                v-if="isOwner"
                @click="openEditModal('identity')"
                type="button"
                class="absolute right-6 top-6 z-30 flex h-12 w-12 items-center justify-center rounded-full border border-white/30 bg-white/15 text-white shadow-lg backdrop-blur-md transition-all hover:bg-white hover:text-[#004ac6]"
                aria-label="Edit profil perusahaan"
                title="Edit profil perusahaan"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </button>

            <div class="absolute bottom-0 left-0 w-full pb-10">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="flex items-end gap-6">
                            <div class="bg-white p-2 rounded-full shadow-xl shrink-0">
                                <div class="w-32 h-32 rounded-full bg-[#e5eeff] flex items-center justify-center overflow-hidden border-4 border-white">
                                    <img v-if="company.profile?.logo_path" class="w-full h-full object-cover" :src="company.profile.logo_path" :alt="company.name" />
                                    <div v-else class="text-4xl font-bold text-[#2563eb]">{{ company.name.substring(0, 2).toUpperCase() }}</div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <h1 class="text-4xl font-bold text-white mb-2">{{ company.name }}</h1>
                                <div class="flex flex-wrap gap-4 items-center text-white/90">
                                    <div class="flex items-center gap-1">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                                        <span>{{ company.profile?.industry || 'Industri Perusahaan' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span>{{ company.profile?.location || 'Lokasi belum diatur' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 shrink-0">
                            <a v-if="company.profile?.website" :href="company.profile.website" target="_blank" class="bg-[#6cf8bb] text-[#00714d] px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2 hover:bg-[#4edea3] transition-colors duration-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                Kunjungi Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-8 space-y-12">
                    
                    <!-- Tentang Perusahaan -->
                    <section class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="mb-6 flex items-center justify-between gap-4 border-l-4 border-[#004ac6] pl-4">
                            <h2 class="text-2xl font-bold text-[#0b1c30]">Tentang Perusahaan</h2>
                            <button v-if="isOwner" @click="openEditModal('about')" type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#eff4ff] text-[#004ac6] transition-colors hover:bg-[#d3e4fe]" aria-label="Edit tentang perusahaan" title="Edit tentang perusahaan">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                        </div>
                        <div class="space-y-4 text-[#434655] text-lg leading-relaxed">
                            <p>{{ company.profile?.description || 'Belum ada deskripsi perusahaan.' }}</p>

                            <div class="space-y-3 mt-8">
                                <div class="border border-[#c3c6d7] rounded-xl overflow-hidden group">
                                    <button @click="openVisi = !openVisi" class="w-full flex items-center justify-between p-4 bg-[#eff4ff] hover:bg-[#d3e4fe] transition-colors text-left">
                                        <div class="flex items-center gap-3">
                                            <svg class="h-5 w-5 text-[#004ac6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <span class="font-bold text-[#0b1c30]">Visi</span>
                                        </div>
                                        <svg class="h-5 w-5 text-[#004ac6] transition-transform duration-300" :class="{'rotate-180': openVisi}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                    <div v-show="openVisi" class="p-4 bg-white border-t border-[#c3c6d7] animate-fade-in">
                                        <p class="text-sm text-[#434655]">{{ company.profile?.vision || 'Visi belum diatur.' }}</p>
                                    </div>
                                </div>
                                <div class="border border-[#c3c6d7] rounded-xl overflow-hidden group">
                                    <button @click="openMisi = !openMisi" class="w-full flex items-center justify-between p-4 bg-[#eff4ff] hover:bg-[#d3e4fe] transition-colors text-left">
                                        <div class="flex items-center gap-3">
                                            <svg class="h-5 w-5 text-[#004ac6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>
                                            <span class="font-bold text-[#0b1c30]">Misi</span>
                                        </div>
                                        <svg class="h-5 w-5 text-[#004ac6] transition-transform duration-300" :class="{'rotate-180': openMisi}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                    <div v-show="openMisi" class="p-4 bg-white border-t border-[#c3c6d7] animate-fade-in">
                                        <p class="text-sm text-[#434655]">{{ company.profile?.mission || 'Misi belum diatur.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Galeri Kantor -->
                    <section>
                        <h2 class="text-2xl font-bold text-[#0b1c30] mb-6">Galeri Kantor</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="h-64 rounded-2xl overflow-hidden shadow-md group bg-slate-100">
                                <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2069&auto=format&fit=crop" />
                            </div>
                            <div class="h-64 rounded-2xl overflow-hidden shadow-md group bg-slate-100">
                                <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1556761175-5973dc0f32b7?q=80&w=1932&auto=format&fit=crop" />
                            </div>
                            <div class="h-64 rounded-2xl overflow-hidden shadow-md group bg-slate-100">
                                <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=2070&auto=format&fit=crop" />
                            </div>
                        </div>
                    </section>

                    <!-- Lowongan Magang Aktif -->
                    <section>
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-[#0b1c30]">Lowongan Magang Aktif</h2>
                            <Link :href="route('lowongan')" class="text-[#004ac6] font-bold hover:underline">Lihat Semua</Link>
                        </div>
                        
                        <div v-if="company.internships && company.internships.length > 0" class="grid gap-4">
                            <div v-for="job in company.internships" :key="job.id" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-center hover:shadow-md transition-shadow duration-300 group">
                                <div class="flex items-center gap-6 w-full md:w-auto">
                                    <div class="w-16 h-16 bg-blue-50 rounded-xl flex items-center justify-center text-[#004ac6]">
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">{{ job.title }}</h3>
                                        <p class="text-[#434655] text-sm flex items-center gap-1 mt-1">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> 
                                            {{ job.location || 'Remote' }} • {{ job.quota }} Kuota
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 md:mt-0 flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                                    <span class="bg-[#006c49]/10 text-[#006c49] px-4 py-1 rounded-full text-xs font-semibold">Aktif</span>
                                    <Link :href="route('internships.show', job.id)" class="bg-[#004ac6] text-white px-6 py-2 rounded-lg font-bold hover:bg-[#003ea8] transition-all group-hover:translate-x-1">
                                        Lihat Detail
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-white p-8 rounded-2xl border border-slate-100 text-center text-slate-500">
                            Saat ini belum ada lowongan magang aktif.
                        </div>
                    </section>
                </div>

                <!-- Right Column -->
                <aside class="lg:col-span-4 space-y-8">
                    <!-- Informasi Perusahaan Card -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <h3 class="text-lg font-bold text-[#0b1c30]">Informasi Perusahaan</h3>
                            <button v-if="isOwner" @click="openEditModal('companyInfo')" type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#eff4ff] text-[#004ac6] transition-colors hover:bg-[#d3e4fe]" aria-label="Edit informasi perusahaan" title="Edit informasi perusahaan">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                        </div>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <svg class="h-6 w-6 text-[#004ac6] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <div>
                                    <p class="text-xs text-slate-500 tracking-wider font-semibold mb-1">TAHUN BERDIRI</p>
                                    <p class="font-bold text-[#0b1c30]">{{ company.profile?.founded_year || '-' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <svg class="h-6 w-6 text-[#004ac6] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <div>
                                    <p class="text-xs text-slate-500 tracking-wider font-semibold mb-1">JUMLAH KARYAWAN</p>
                                    <p class="font-bold text-[#0b1c30]">{{ company.profile?.employee_count || '-' }} Karyawan</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <svg class="h-6 w-6 text-[#004ac6] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <div>
                                    <p class="text-xs text-slate-500 tracking-wider font-semibold mb-1">SPESIALISASI</p>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <template v-if="company.profile?.specializations">
                                            <span v-for="spec in company.profile.specializations" :key="spec" class="bg-[#004ac6]/10 text-[#004ac6] text-[11px] px-2.5 py-1 rounded-md font-bold border border-[#004ac6]/20">{{ spec }}</span>
                                        </template>
                                        <span v-else class="text-sm text-slate-500">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kontak & Lokasi Card -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <h3 class="text-lg font-bold text-[#0b1c30]">Kontak &amp; Lokasi</h3>
                            <button v-if="isOwner" @click="openEditModal('contact')" type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#eff4ff] text-[#004ac6] transition-colors hover:bg-[#d3e4fe]" aria-label="Edit kontak dan lokasi" title="Edit kontak dan lokasi">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            </button>
                        </div>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <svg class="h-6 w-6 text-[#004ac6] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <div>
                                    <p class="text-xs text-slate-500 tracking-wider font-semibold mb-1">KANTOR PUSAT</p>
                                    <p class="text-[#434655] text-sm">{{ company.profile?.office_address || 'Alamat belum diatur' }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 tracking-wider font-semibold mb-3">MEDIA SOSIAL</p>
                                <div class="flex gap-3">
                                    <a class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-[#004ac6] hover:text-white transition-all duration-300" href="#">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                                    </a>
                                    <a class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-[#004ac6] hover:text-white transition-all duration-300" :href="'mailto:' + company.email">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicator Widget -->
                    <div class="bg-gradient-to-br from-[#004ac6] to-[#006c49] p-8 rounded-2xl shadow-xl text-white">
                        <h4 class="text-lg font-bold mb-2">Build Your Future</h4>
                        <p class="text-white/80 text-sm mb-6">90% of our interns join our full-time graduate program. Start your journey today.</p>
                        <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                            <div class="bg-white h-full w-3/4 rounded-full"></div>
                        </div>
                        <p class="mt-2 text-[10px] tracking-wider font-semibold text-right">75% EMPLOYMENT RATE</p>
                    </div>
                </aside>
            </div>
        </main>

        <div v-if="isOwner && showEditModal" class="fixed inset-0 z-[80] flex items-center justify-center overflow-hidden bg-[#0b1c30]/70 px-4 py-8 backdrop-blur-sm">
            <div class="max-h-[calc(100vh-4rem)] w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-bold text-[#0b1c30]">{{ activeEditConfig.title }}</h2>
                        <p class="text-sm text-slate-500">{{ activeEditConfig.description }}</p>
                    </div>
                    <button @click="closeEditModal" type="button" class="flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900" aria-label="Tutup form">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitProfile" class="max-h-[calc(100vh-11rem)] space-y-6 overflow-y-auto px-6 py-6">
                    <div v-if="activeEditSection === 'identity'" class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Nama Perusahaan</label>
                            <input v-model="form.name" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Bidang Industri</label>
                            <input v-model="form.industry" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.industry" class="mt-1 text-sm text-red-600">{{ form.errors.industry }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Lokasi</label>
                            <input v-model="form.location" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Website</label>
                            <input v-model="form.website" type="url" placeholder="https://contoh.com" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
                        </div>
                    </div>

                    <div v-if="activeEditSection === 'about'">
                        <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Deskripsi Perusahaan</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]"></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div v-if="activeEditSection === 'about'" class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Visi</label>
                            <textarea v-model="form.vision" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]"></textarea>
                            <p v-if="form.errors.vision" class="mt-1 text-sm text-red-600">{{ form.errors.vision }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Misi</label>
                            <textarea v-model="form.mission" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]"></textarea>
                            <p v-if="form.errors.mission" class="mt-1 text-sm text-red-600">{{ form.errors.mission }}</p>
                        </div>
                    </div>

                    <div v-if="activeEditSection === 'companyInfo'" class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Tahun Berdiri</label>
                            <input v-model="form.founded_year" type="number" min="1800" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.founded_year" class="mt-1 text-sm text-red-600">{{ form.errors.founded_year }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Jumlah Karyawan</label>
                            <input v-model="form.employee_count" type="text" placeholder="50-100" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                            <p v-if="form.errors.employee_count" class="mt-1 text-sm text-red-600">{{ form.errors.employee_count }}</p>
                        </div>
                    </div>

                    <div v-if="activeEditSection === 'contact'">
                        <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Alamat Kantor</label>
                        <textarea v-model="form.office_address" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]"></textarea>
                        <p v-if="form.errors.office_address" class="mt-1 text-sm text-red-600">{{ form.errors.office_address }}</p>
                    </div>

                    <div v-if="activeEditSection === 'companyInfo'">
                        <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Spesialisasi</label>
                        <input v-model="form.specializations" type="text" placeholder="Software, Data, UI/UX" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#004ac6] focus:ring-[#004ac6]" />
                        <p v-if="form.errors.specializations" class="mt-1 text-sm text-red-600">{{ form.errors.specializations }}</p>
                    </div>

                    <div v-if="activeEditSection === 'identity'" class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Logo Perusahaan</label>
                            <input @input="form.logo = $event.target.files[0]" type="file" accept="image/png,image/jpeg" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#eff4ff] file:px-4 file:py-2 file:font-bold file:text-[#004ac6]" />
                            <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0b1c30]">Cover Profil</label>
                            <input @input="form.cover = $event.target.files[0]" type="file" accept="image/png,image/jpeg" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#eff4ff] file:px-4 file:py-2 file:font-bold file:text-[#004ac6]" />
                            <p v-if="form.errors.cover" class="mt-1 text-sm text-red-600">{{ form.errors.cover }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                        <button @click="closeEditModal" type="button" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-[#004ac6] px-6 py-3 text-sm font-bold text-white transition-colors hover:bg-[#003ea8] disabled:cursor-not-allowed disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>

<style>
/* Any custom styles could go here, but using tailwind classes from the user's CSS */
</style>
