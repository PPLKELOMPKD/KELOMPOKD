<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ applicant: Object });
const page = usePage();
const showModal = ref(false);
const modalAction = ref('');
const processing = ref(false);

const getColorClass = (color, type) => {
    const map = {
        blue:   { bg: 'bg-blue-50',    text: 'text-blue-600',    border: 'border-blue-200' },
        green:  { bg: 'bg-emerald-50',  text: 'text-emerald-600', border: 'border-emerald-200' },
        purple: { bg: 'bg-purple-50',   text: 'text-purple-600',  border: 'border-purple-200' },
        red:    { bg: 'bg-red-50',      text: 'text-red-600',     border: 'border-red-200' },
    };
    return map[color]?.[type] || map.blue[type];
};

const statusSteps = [
    { key: 'submitted',       label: 'Submitted',       icon: 'file',     color: 'blue' },
    { key: 'menunggu ulasan', label: 'Menunggu Ulasan',  icon: 'clock',    color: 'blue' },
    { key: 'wawancara',       label: 'Wawancara',        icon: 'calendar', color: 'purple' },
    { key: 'lolos',           label: 'Diterima',         icon: 'check',    color: 'green' },
];

const currentStepIndex = computed(() => {
    if (props.applicant.statusRaw === 'tidak lolos') return -1;
    return statusSteps.findIndex(s => s.key === props.applicant.statusRaw);
});

const openModal = (action) => { modalAction.value = action; showModal.value = true; };
const closeModal = () => { showModal.value = false; modalAction.value = ''; };

const confirmAction = () => {
    processing.value = true;
    router.patch(route('perusahaan.applicants.updateStatus', props.applicant.id), {
        status: modalAction.value, redirect: 'show',
    }, {
        preserveScroll: true,
        onFinish: () => { processing.value = false; closeModal(); },
    });
};

const modalConfig = computed(() => {
    const c = {
        lolos:          { title: 'Terima Pelamar',     msg: `Terima ${props.applicant.name} untuk posisi ${props.applicant.position}?`, icon: 'check',    btnClass: 'bg-emerald-600 hover:bg-emerald-700', btnText: 'Ya, Terima',  iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600' },
        'tidak lolos':  { title: 'Tolak Pelamar',      msg: `Tolak ${props.applicant.name} untuk posisi ${props.applicant.position}?`,  icon: 'x',        btnClass: 'bg-red-600 hover:bg-red-700',         btnText: 'Ya, Tolak',   iconBg: 'bg-red-100',     iconColor: 'text-red-600' },
        wawancara:      { title: 'Undang Wawancara',   msg: `Undang ${props.applicant.name} untuk wawancara posisi ${props.applicant.position}?`, icon: 'calendar', btnClass: 'bg-purple-600 hover:bg-purple-700', btnText: 'Ya, Undang',  iconBg: 'bg-purple-100',  iconColor: 'text-purple-600' },
        'menunggu ulasan': { title: 'Reset ke Menunggu', msg: `Reset status ${props.applicant.name} kembali ke Menunggu Ulasan?`, icon: 'clock', btnClass: 'bg-blue-600 hover:bg-blue-700', btnText: 'Ya, Reset', iconBg: 'bg-blue-100', iconColor: 'text-blue-600' },
    };
    return c[modalAction.value] || c.lolos;
});
</script>

<template>
    <Head :title="`Detail Pelamar — ${applicant.name}`" />
    <SikaraLayout title="Detail Pelamar" subtitle="Informasi lengkap pelamar dan riwayat lamaran.">
        <template #headerAction>
            <Link :href="route('perusahaan.applicants.index')" class="flex h-11 items-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-slate-50 transition-all">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </Link>
        </template>

        <!-- Flash Message -->
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="$page.props.flash?.success" class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ $page.props.flash.success }}
            </div>
        </Transition>

        <div class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
            <!-- Left Column: Profile -->
            <div class="space-y-6">
                <section class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full text-3xl font-bold" :class="[getColorClass(applicant.statusColor, 'bg'), getColorClass(applicant.statusColor, 'text')]">
                            {{ applicant.initials }}
                        </div>
                        <h3 class="mt-4 text-2xl font-bold text-[#101828]">{{ applicant.name }}</h3>
                        <p class="mt-1 text-sm text-[#667085]">{{ applicant.major }}</p>
                        <p class="text-sm text-[#667085]">{{ applicant.university }}</p>
                        <div class="mt-4 w-full">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border" :class="[getColorClass(applicant.statusColor, 'bg'), getColorClass(applicant.statusColor, 'text'), getColorClass(applicant.statusColor, 'border')]">
                                {{ applicant.status }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-[#f2f4f7] pt-5 text-sm text-[#344054] space-y-3">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                            <span>{{ applicant.email }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z"/></svg>
                            <span>{{ applicant.phone }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            <span>{{ applicant.location }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-[#98a2b3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            <span>GPA: <strong>{{ applicant.gpa }}</strong></span>
                        </div>
                    </div>
                </section>

                <!-- Skills -->
                <section v-if="applicant.skills && applicant.skills.length > 0" class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-lg font-semibold text-[#101828]">Keahlian</h4>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span v-for="skill in applicant.skills" :key="skill.id" class="rounded-lg bg-[#f1f5f9] px-3 py-1.5 text-xs font-medium text-[#344054]">
                            {{ skill.name }}
                        </span>
                    </div>
                </section>

                <!-- Bio -->
                <section v-if="applicant.bio && applicant.bio !== '-'" class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-lg font-semibold text-[#101828]">Tentang</h4>
                    <p class="mt-3 text-sm leading-7 text-[#475467]">{{ applicant.bio }}</p>
                </section>
            </div>

            <!-- Right Column: Application Details -->
            <div class="space-y-6">
                <!-- Application Info -->
                <section class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-xl font-semibold text-[#101828]">Informasi Lamaran</h4>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-[#f9fafb] p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">Posisi Dilamar</p>
                            <p class="mt-2 text-base font-bold text-[#101828]">{{ applicant.position }}</p>
                        </div>
                        <div class="rounded-xl bg-[#f9fafb] p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">Tanggal Melamar</p>
                            <p class="mt-2 text-base font-bold text-[#101828]">{{ applicant.date }}</p>
                            <p class="text-xs text-[#98a2b3]">{{ applicant.dateRelative }}</p>
                        </div>
                        <div class="rounded-xl bg-[#f9fafb] p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">Program Studi</p>
                            <p class="mt-2 text-base font-bold text-[#101828]">{{ applicant.studyProgram }}</p>
                        </div>
                        <div class="rounded-xl bg-[#f9fafb] p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[#667085]">Terakhir Diperbarui</p>
                            <p class="mt-2 text-base font-bold text-[#101828]">{{ applicant.updatedAt }}</p>
                        </div>
                    </div>
                </section>

                <!-- Status Timeline -->
                <section class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-xl font-semibold text-[#101828]">Status Pipeline</h4>
                    <div v-if="applicant.statusRaw === 'tidak lolos'" class="mt-6 flex items-center gap-4 rounded-xl bg-red-50 border border-red-200 p-5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-red-700">Tidak Lolos</p>
                            <p class="text-sm text-red-600">Pelamar ini tidak lolos seleksi.</p>
                        </div>
                    </div>
                    <div v-else class="mt-8 flex items-center justify-between relative px-4">
                        <div class="absolute left-10 right-10 top-6 h-0.5 bg-[#eaecf0] z-0"></div>
                        <div v-for="(step, index) in statusSteps" :key="step.key" class="relative z-10 flex flex-col items-center flex-1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border-4 border-white shadow-sm ring-1 ring-[#eaecf0]" :class="index <= currentStepIndex ? [getColorClass(step.color, 'bg'), getColorClass(step.color, 'text')] : 'bg-[#f9fafb] text-[#d0d5dd]'">
                                <svg v-if="step.icon === 'file'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <svg v-if="step.icon === 'clock'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <svg v-if="step.icon === 'calendar'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <svg v-if="step.icon === 'check'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold" :class="index <= currentStepIndex ? 'text-[#101828]' : 'text-[#d0d5dd]'">{{ step.label }}</p>
                        </div>
                    </div>
                </section>

                <!-- Action Buttons -->
                <section class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-xl font-semibold text-[#101828]">Aksi</h4>
                    <p class="mt-1 text-sm text-[#667085]">Ubah status pelamar. Notifikasi akan otomatis dikirim ke peserta.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button v-if="applicant.statusRaw !== 'lolos'" @click="openModal('lolos')" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Terima Pelamar
                        </button>
                        <button v-if="applicant.statusRaw !== 'tidak lolos'" @click="openModal('tidak lolos')" class="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Tolak Pelamar
                        </button>
                        <button v-if="applicant.statusRaw !== 'wawancara' && applicant.statusRaw !== 'lolos' && applicant.statusRaw !== 'tidak lolos'" @click="openModal('wawancara')" class="flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 active:scale-95">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Undang Wawancara
                        </button>
                        <button v-if="applicant.statusRaw === 'lolos' || applicant.statusRaw === 'tidak lolos'" @click="openModal('menunggu ulasan')" class="flex items-center gap-2 rounded-xl border border-[#d0d5dd] bg-white px-6 py-3 text-sm font-semibold text-[#344054] shadow-sm hover:bg-[#f9fafb] transition-all">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            Reset Status
                        </button>
                    </div>
                </section>

                <!-- Other Applications -->
                <section v-if="applicant.otherApplications && applicant.otherApplications.length > 0" class="rounded-2xl border border-[#eaecf0] bg-white p-6 shadow-sm">
                    <h4 class="text-xl font-semibold text-[#101828]">Lamaran Lain di Perusahaan Ini</h4>
                    <div class="mt-4 space-y-3">
                        <Link v-for="app in applicant.otherApplications" :key="app.id" :href="route('perusahaan.applicants.show', app.id)" class="flex items-center justify-between rounded-xl border border-[#eaecf0] p-4 hover:bg-[#f9fafb] transition-all group">
                            <div>
                                <p class="font-semibold text-[#101828] group-hover:text-[#2563EB] transition-colors">{{ app.position }}</p>
                                <p class="text-xs text-[#667085]">Dilamar {{ app.date }}</p>
                            </div>
                            <span class="text-xs font-semibold text-[#667085]">{{ app.status }}</span>
                        </Link>
                    </div>
                </section>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
                        <div class="text-center">
                            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full" :class="modalConfig.iconBg">
                                <svg v-if="modalConfig.icon === 'check'" :class="['h-8 w-8', modalConfig.iconColor]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <svg v-if="modalConfig.icon === 'x'" :class="['h-8 w-8', modalConfig.iconColor]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                <svg v-if="modalConfig.icon === 'calendar'" :class="['h-8 w-8', modalConfig.iconColor]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <svg v-if="modalConfig.icon === 'clock'" :class="['h-8 w-8', modalConfig.iconColor]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-[#101828]">{{ modalConfig.title }}</h3>
                            <p class="mt-3 text-sm text-[#667085] leading-relaxed">{{ modalConfig.msg }}</p>
                            <p class="mt-2 text-xs text-[#98a2b3]">Notifikasi akan otomatis dikirim ke pelamar.</p>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button @click="closeModal" class="flex-1 rounded-xl border border-[#d0d5dd] bg-white py-3 text-sm font-semibold text-[#344054] hover:bg-[#f9fafb] transition-all">Batal</button>
                            <button @click="confirmAction" :disabled="processing" :class="['flex-1 rounded-xl py-3 text-sm font-semibold text-white transition-all shadow-sm disabled:opacity-50', modalConfig.btnClass]">
                                <span v-if="processing" class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Memproses...
                                </span>
                                <span v-else>{{ modalConfig.btnText }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SikaraLayout>
</template>
