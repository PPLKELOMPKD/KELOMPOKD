<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    profile: Object,
    skills: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isProfileDrawerOpen = ref(false);
const isSkillFormOpen = ref(false);
const profileSuccessMessage = ref('');

const filled = (value) => value !== null && value !== undefined && String(value).trim() !== '';
const fallback = (value, text = 'Belum diisi') => (filled(value) ? value : text);

const profileForm = useForm({
    nim: props.profile?.nim ?? '',
    department: props.profile?.department ?? '',
    study_program: props.profile?.study_program ?? '',
    gpa: props.profile?.gpa ?? '',
    phone: props.profile?.phone ?? '',
    university: props.profile?.university ?? '',
    location: props.profile?.location ?? '',
    bio: props.profile?.bio ?? '',
});

const skillForm = useForm({
    name: '',
    proficiency: 50,
});

const profileFields = computed(() => [
    user.value?.name,
    user.value?.email,
    props.profile?.nim,
    props.profile?.department,
    props.profile?.study_program,
    props.profile?.gpa,
    props.profile?.phone,
    props.profile?.university,
    props.profile?.location,
    props.profile?.bio,
]);

const profileCompletion = computed(() => {
    const totalFilled = profileFields.value.filter(filled).length;
    return Math.round((totalFilled / profileFields.value.length) * 100);
});

const initials = computed(() => {
    const names = (user.value?.name ?? 'SIKARA').trim().split(/\s+/).slice(0, 2);
    return names.map((name) => name.charAt(0).toUpperCase()).join('');
});

const topSkills = computed(() =>
    [...(props.skills ?? [])].sort((a, b) => Number(b.proficiency) - Number(a.proficiency)),
);

const cvFileName = computed(() => `CV_${(user.value?.name ?? 'Mahasiswa').replace(/\s+/g, '')}.pdf`);
const studyLine = computed(() => {
    const studyProgram = props.profile?.study_program;
    const university = props.profile?.university;

    if (filled(studyProgram) && filled(university)) {
        return `${studyProgram}, ${university}`;
    }

    return fallback(studyProgram || university, 'Program studi dan universitas belum diisi');
});

const academicItems = computed(() => [
    { label: 'NIM', value: fallback(props.profile?.nim) },
    { label: 'Fakultas/Jurusan', value: fallback(props.profile?.department) },
    { label: 'Program Studi', value: fallback(props.profile?.study_program) },
    { label: 'Angkatan', value: fallback(props.profile?.cohort) },
    { label: 'IPK Saat Ini', value: filled(props.profile?.gpa) ? `${props.profile.gpa} / 4.00` : 'Belum diisi', accent: 'blue' },
    { label: 'Status', value: fallback(props.profile?.student_status, 'Aktif'), accent: 'green' },
]);

watch(
    () => props.profile,
    (profile) => {
        profileForm.defaults({
            nim: profile?.nim ?? '',
            department: profile?.department ?? '',
            study_program: profile?.study_program ?? '',
            gpa: profile?.gpa ?? '',
            phone: profile?.phone ?? '',
            university: profile?.university ?? '',
            location: profile?.location ?? '',
            bio: profile?.bio ?? '',
        });
        profileForm.reset();
    },
);

const validateGpa = () => {
    profileForm.clearErrors('gpa');

    if (!filled(profileForm.gpa)) {
        return true;
    }

    const value = Number(profileForm.gpa);

    if (Number.isNaN(value) || value < 0 || value > 4) {
        profileForm.setError('gpa', 'IPK harus berada di antara 0.00 - 4.00');
        return false;
    }

    return true;
};

const openProfileDrawer = () => {
    profileSuccessMessage.value = '';
    isProfileDrawerOpen.value = true;
};

const closeProfileDrawer = () => {
    if (profileForm.processing) {
        return;
    }

    profileForm.clearErrors();
    profileForm.reset();
    isProfileDrawerOpen.value = false;
};

const submitProfile = () => {
    profileSuccessMessage.value = '';

    if (!validateGpa()) {
        return;
    }

    profileForm
        .transform((data) => ({
            ...data,
            gpa: filled(data.gpa) ? Number(data.gpa) : data.gpa,
        }))
        .post(route('profile.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isProfileDrawerOpen.value = false;
                profileSuccessMessage.value = 'Profil berhasil diperbarui.';
            },
        });
};

const submitSkill = () => {
    skillForm
        .transform((data) => ({
            ...data,
            proficiency: Number(data.proficiency),
        }))
        .post(route('skills.store'), {
            preserveScroll: true,
            onSuccess: () => {
                skillForm.reset();
                isSkillFormOpen.value = false;
            },
        });
};
</script>

<template>
    <Head title="Profil Mahasiswa" />

    <PortalLayout activeRole="peserta" loginRole="mahasiswa">
        <!-- Navigasi Utama -->
        <template #navigation>
            <Link :href="route('lowongan')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Cari Lowongan</Link>
            <Link :href="route('perusahaan-list')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">List Perusahaan</Link>
            <Link :href="route('lms')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">LMS</Link>
            <Link :href="route('event')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Pelatihan</Link>
            <Link :href="route('generate-cv')" class="text-sm font-semibold text-[#64748B] transition-colors hover:text-[#2563EB]">Generate CV</Link>
        </template>

        <!-- Area Konten Utama -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Pengganti Header Bawaan SikaraLayout -->
            <header class="mb-6 flex flex-col gap-4 rounded-[20px] border border-slate-200 bg-white px-6 py-5 shadow-[0_16px_40px_rgba(15,23,42,0.04)] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Profil Saya</h1>
                    <p class="mt-1 max-w-3xl text-sm leading-6 text-[#64748B]">Kelola identitas akademik, keterampilan, dan dokumen karier Anda.</p>
                </div>
                
                <!-- Tombol Edit Profil dipindah ke sini -->
                <button
                    type="button"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(37,99,235,0.18)] transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60 shrink-0"
                    @click="openProfileDrawer"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                    </svg>
                    Edit Profil
                </button>
            </header>

            <div v-if="profileSuccessMessage" class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ profileSuccessMessage }}
            </div>

            <!-- Konten Profil -->
            <div class="grid gap-6 xl:grid-cols-12">
                <section class="xl:col-span-4">
                    <div class="rounded-[20px] border border-slate-200 bg-white p-6 text-center shadow-[0_16px_40px_rgba(15,23,42,0.04)] md:p-8">
                        <div class="mx-auto flex h-32 w-32 items-center justify-center rounded-full border-4 border-slate-50 bg-gradient-to-br from-blue-50 to-emerald-50 text-4xl font-bold text-[#2563EB]">
                            {{ initials }}
                        </div>

                        <h2 class="mt-6 text-2xl font-bold tracking-tight text-[#0F172A]">{{ user?.name }}</h2>
                        <p class="mt-1 text-base font-semibold text-[#2563EB]">Mahasiswa</p>

                        <div class="mt-8 space-y-5 border-t border-slate-100 pt-6 text-left text-sm text-[#64748B]">
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="m3 8 9-5 9 5-9 5z" />
                                    <path d="M7 11v5c0 1.7 2.2 3 5 3s5-1.3 5-3v-5" />
                                </svg>
                                <span class="leading-6">{{ studyLine }}</span>
                            </div>
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M4 6h16v12H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                                <span class="truncate" :title="user?.email">{{ user?.email }}</span>
                            </div>
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                                </svg>
                                <span>{{ fallback(profile?.phone, 'Nomor telepon belum diisi') }}</span>
                            </div>
                            <div class="flex items-start gap-4">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                                <span>{{ fallback(profile?.location, 'Lokasi belum diisi') }}</span>
                            </div>
                        </div>

                        <div class="mt-8 rounded-2xl bg-slate-50 p-5 text-left">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Kelengkapan Profil</p>
                                    <p class="mt-1 text-xs leading-5 text-[#64748B]">Lengkapi data untuk meningkatkan peluang magang.</p>
                                </div>
                                <span class="text-sm font-bold text-[#2563EB]">{{ profileCompletion }}%</span>
                            </div>
                            <div class="mt-4 h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-gradient-to-r from-[#2563EB] to-[#10B981]" :style="{ width: `${profileCompletion}%` }" />
                            </div>
                        </div>
                    </div>
                </section>

                <div class="space-y-6 xl:col-span-8">
                    <section class="rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.04)] md:p-8">
                        <h3 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-[#0F172A]">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="M7 4h10v16H7z" />
                                <path d="M9.5 8.5a2.5 2.5 0 0 0 5 0" />
                                <path d="M9 15h6" />
                            </svg>
                            Tentang Saya
                        </h3>
                        <p class="mt-5 text-base leading-8 text-[#334155] whitespace-pre-wrap">
                            {{ profile?.bio || 'Lengkapi bio Anda untuk meningkatkan daya tarik profil di mata recruiter.' }}
                        </p>
                    </section>

                    <section class="rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.04)] md:p-8">
                        <h3 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-[#0F172A]">
                            <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="m4 7 8-4 8 4-8 4z" />
                                <path d="M6 10v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5" />
                            </svg>
                            Data Akademik
                        </h3>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="item in academicItems"
                                :key="item.label"
                                class="rounded-2xl border p-4"
                                :class="{
                                    'border-blue-100 bg-blue-50': item.accent === 'blue',
                                    'border-emerald-100 bg-emerald-50': item.accent === 'green',
                                    'border-slate-100 bg-slate-50': !item.accent,
                                }"
                            >
                                <p
                                    class="text-[11px] font-bold uppercase tracking-wider"
                                    :class="{
                                        'text-[#2563EB]': item.accent === 'blue',
                                        'text-[#10B981]': item.accent === 'green',
                                        'text-[#64748B]': !item.accent,
                                    }"
                                >
                                    {{ item.label }}
                                </p>
                                <p
                                    class="mt-2 break-words text-base font-bold"
                                    :class="{
                                        'text-[#2563EB]': item.accent === 'blue',
                                        'text-[#10B981]': item.accent === 'green',
                                        'text-[#0F172A]': !item.accent,
                                    }"
                                >
                                    {{ item.value }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <section class="rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.04)] md:p-8">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-[#0F172A]">
                                    <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                        <path d="m8 9-4 3 4 3" />
                                        <path d="m16 9 4 3-4 3" />
                                        <path d="m14 5-4 14" />
                                    </svg>
                                    Keterampilan Utama
                                </h3>
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-sm font-semibold text-[#2563EB] transition hover:bg-blue-50 shrink-0"
                                    @click="isSkillFormOpen = !isSkillFormOpen"
                                >
                                    {{ isSkillFormOpen ? 'Tutup' : 'Tambah' }}
                                </button>
                            </div>

                            <div v-if="topSkills.length" class="mt-7 space-y-5">
                                <div v-for="skill in topSkills" :key="skill.id ?? skill.name">
                                    <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-[#0F172A]">{{ skill.name }}</span>
                                        <span class="text-[#64748B]">{{ skill.proficiency }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-blue-100">
                                        <div class="h-2 rounded-full bg-[#2563EB]" :style="{ width: `${skill.proficiency}%` }" />
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-7 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-[#64748B]">
                                Belum ada keterampilan. Tambahkan skill pertama Anda.
                            </div>

                            <form v-if="isSkillFormOpen" class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4" @submit.prevent="submitSkill">
                                <div>
                                    <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Nama Skill</label>
                                    <input
                                        v-model="skillForm.name"
                                        type="text"
                                        class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"
                                        placeholder="Contoh: Laravel"
                                    />
                                    <p v-if="skillForm.errors.name" class="mt-2 text-xs text-red-600">{{ skillForm.errors.name }}</p>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Tingkat Keahlian</label>
                                        <span class="text-sm font-bold text-[#2563EB]">{{ skillForm.proficiency }}%</span>
                                    </div>
                                    <input v-model="skillForm.proficiency" type="range" min="1" max="100" class="mt-3 w-full accent-[#2563EB]" />
                                    <p v-if="skillForm.errors.proficiency" class="mt-2 text-xs text-red-600">{{ skillForm.errors.proficiency }}</p>
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#2563EB] px-4 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="skillForm.processing"
                                >
                                    {{ skillForm.processing ? 'Menyimpan...' : 'Simpan Skill' }}
                                </button>
                            </form>
                        </section>

                        <section class="flex flex-col rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.04)] md:p-8">
                            <h3 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-[#0F172A]">
                                <svg class="h-6 w-6 text-[#2563EB]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M6 3h8l4 4v14H6z" />
                                    <path d="M14 3v5h4" />
                                    <path d="M9 13h6" />
                                    <path d="M9 17h4" />
                                </svg>
                                Dokumen CV
                            </h3>

                            <div class="mt-7 flex items-center gap-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                        <path d="M6 3h8l4 4v14H6z" />
                                        <path d="M14 3v5h4" />
                                        <path d="M9 13h6" />
                                        <path d="M9 17h4" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-[#0F172A]">{{ cvFileName }}</p>
                                    <p class="mt-1 text-xs text-[#64748B]">CV dibuat otomatis dari data profil Anda.</p>
                                </div>
                            </div>

                            <div class="mt-auto space-y-4 pt-8">
                                <a
                                    :href="route('cv.download')"
                                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-4 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(37,99,235,0.18)] transition hover:bg-blue-700"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                        <path d="M12 3v3" />
                                        <path d="M12 18v3" />
                                        <path d="M4.2 7.5 6.8 9" />
                                        <path d="m17.2 15 2.6 1.5" />
                                        <path d="m19.8 7.5-2.6 1.5" />
                                        <path d="M6.8 15l-2.6 1.5" />
                                        <path d="m9 12 2 2 4-5" />
                                    </svg>
                                    Generate CV Otomatis
                                </a>
                                <a
                                    :href="route('cv.download')"
                                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-[#0F172A] transition hover:bg-slate-50"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                        <path d="M12 3v12" />
                                        <path d="m7 10 5 5 5-5" />
                                        <path d="M5 21h14" />
                                    </svg>
                                    Unduh CV
                                </a>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drawer Edit Profil -->
        <div v-if="isProfileDrawerOpen" class="fixed inset-0 z-50">
            <button
                type="button"
                class="absolute inset-0 h-full w-full cursor-default bg-slate-900/30 backdrop-blur-[2px]"
                aria-label="Tutup drawer edit profil"
                @click="closeProfileDrawer"
            />

            <aside class="absolute right-0 top-0 flex h-full w-full max-w-xl flex-col bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-bold text-[#0F172A]">Edit Profil</h2>
                        <p class="mt-1 text-sm text-[#64748B]">Perbarui data akademik dan ringkasan diri Anda.</p>
                    </div>
                    <button type="button" class="rounded-full p-2 text-[#64748B] transition hover:bg-slate-100 hover:text-[#0F172A]" @click="closeProfileDrawer">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m6 6 12 12" />
                            <path d="M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <form class="flex-1 overflow-y-auto px-6 py-6" @submit.prevent="submitProfile">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">NIM</label>
                            <input v-model="profileForm.nim" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.nim" class="mt-2 text-xs text-red-600">{{ profileForm.errors.nim }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Jurusan / Fakultas</label>
                            <input v-model="profileForm.department" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.department" class="mt-2 text-xs text-red-600">{{ profileForm.errors.department }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Program Studi</label>
                            <input v-model="profileForm.study_program" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.study_program" class="mt-2 text-xs text-red-600">{{ profileForm.errors.study_program }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">IPK</label>
                            <input v-model="profileForm.gpa" type="number" step="0.01" min="0" max="4" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" @blur="validateGpa" />
                            <p v-if="profileForm.errors.gpa" class="mt-2 text-xs text-red-600">{{ profileForm.errors.gpa }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Nomor Telepon</label>
                            <input v-model="profileForm.phone" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.phone" class="mt-2 text-xs text-red-600">{{ profileForm.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Universitas</label>
                            <input v-model="profileForm.university" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.university" class="mt-2 text-xs text-red-600">{{ profileForm.errors.university }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Lokasi</label>
                            <input v-model="profileForm.location" type="text" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.location" class="mt-2 text-xs text-red-600">{{ profileForm.errors.location }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Bio / Tentang Saya</label>
                            <textarea v-model="profileForm.bio" rows="5" class="mt-2 w-full rounded-xl border-slate-200 text-sm leading-6 focus:border-[#2563EB] focus:ring-[#2563EB]" />
                            <p v-if="profileForm.errors.bio" class="mt-2 text-xs text-red-600">{{ profileForm.errors.bio }}</p>
                        </div>
                    </div>
                </form>

                <div class="border-t border-slate-200 px-6 py-5">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-[#0F172A] transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="profileForm.processing"
                            @click="closeProfileDrawer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="profileForm.processing"
                            @click="submitProfile"
                        >
                            {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </PortalLayout>
</template>