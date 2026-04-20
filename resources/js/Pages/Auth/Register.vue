<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    roles: Array,
});

const roleMeta = {
    mahasiswa: {
        label: 'Mahasiswa',
        description: 'Cari lowongan magang',
    },
    perusahaan: {
        label: 'Perusahaan',
        description: 'Posting lowongan',
    },
};

const passwordVisible = ref(false);
const passwordConfirmationVisible = ref(false);

const form = useForm({
    role: 'mahasiswa',
    name: '',
    email: '',
    phone: '',
    university: '',
    study_program: '',
    nim: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const isMahasiswa = computed(() => form.role === 'mahasiswa');
const availableRoles = computed(() => props.roles.filter((role) => role.value in roleMeta));
const nameLabel = computed(() => (isMahasiswa.value ? 'Nama Lengkap' : 'Nama Perusahaan'));
const namePlaceholder = computed(() => (isMahasiswa.value ? 'Nama lengkap Anda' : 'Nama perusahaan'));

const selectRole = (role) => {
    form.role = role;

    if (role !== 'mahasiswa') {
        form.university = '';
        form.study_program = '';
        form.nim = '';
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        phone: data.phone || null,
        university: isMahasiswa.value ? data.university : null,
        study_program: isMahasiswa.value ? data.study_program : null,
        nim: isMahasiswa.value ? data.nim : null,
    })).post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrasi" />

        <div class="mx-auto w-full max-w-[400px]">
            <div class="mb-6 flex flex-col items-center text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-black text-xl font-bold text-white shadow-sm">
                    SK
                </div>
                <h1 class="mt-5 text-[2rem] font-semibold tracking-[-0.03em] text-[#101828]">
                    Buat Akun SIKARA
                </h1>
                <p class="mt-2 text-sm leading-6 text-[#667085]">
                    Bergabung dengan platform karir mahasiswa
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.08em] text-[#667085]">
                        Daftar Sebagai
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button
                            v-for="role in availableRoles"
                            :key="role.value"
                            type="button"
                            class="rounded-[12px] border px-3 py-3 text-left transition"
                            :class="form.role === role.value ? 'border-black bg-white shadow-[inset_0_0_0_1px_rgba(0,0,0,0.85)]' : 'border-[#e4e7ec] bg-[#fcfcfd] text-[#475467]'"
                            @click="selectRole(role.value)"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-[#101828]">
                                    <svg v-if="role.value === 'mahasiswa'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                                        <path d="M5 20a7 7 0 0 1 14 0" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M6 21h12" />
                                        <path d="M7 21V7l5-3 5 3v14" />
                                        <path d="M10 10h.01M10 13h.01M10 16h.01M14 10h.01M14 13h.01M14 16h.01" />
                                    </svg>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-[#101828]">{{ roleMeta[role.value].label }}</span>
                                    <span class="mt-0.5 block text-[11px] leading-4 text-[#667085]">{{ roleMeta[role.value].description }}</span>
                                </span>
                            </div>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div>
                    <label for="name" class="text-sm font-medium text-[#344054]">{{ nameLabel }}</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                                <path d="M5 20a7 7 0 0 1 14 0" />
                            </svg>
                        </span>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                            :placeholder="namePlaceholder"
                        >
                    </div>
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <label for="email" class="text-sm font-medium text-[#344054]">Email <span class="text-[#f04438]">*</span></label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 6h16v12H4z" />
                                <path d="m4 7 8 6 8-6" />
                            </svg>
                        </span>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                            placeholder="nama@email.com"
                        >
                    </div>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <label for="phone" class="text-sm font-medium text-[#344054]">Nomor Telepon <span class="text-[#f04438]">*</span></label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                            </svg>
                        </span>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            required
                            autocomplete="tel"
                            class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                            placeholder="+62 812-3456-7890"
                        >
                    </div>
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div v-if="isMahasiswa" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="university" class="text-sm font-medium text-[#344054]">Universitas <span class="text-[#f04438]">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m3 9 9-5 9 5-9 5-9-5Z" />
                                    <path d="M5 10.5V15l7 4 7-4v-4.5" />
                                </svg>
                            </span>
                            <input
                                id="university"
                                v-model="form.university"
                                type="text"
                                class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                                placeholder="Nama universitas"
                            >
                        </div>
                        <InputError class="mt-2" :message="form.errors.university" />
                    </div>

                    <div>
                        <label for="study_program" class="text-sm font-medium text-[#344054]">Program Studi <span class="text-[#f04438]">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 5h16" />
                                    <path d="M4 12h16" />
                                    <path d="M4 19h16" />
                                    <path d="M8 5v14" />
                                    <path d="M16 5v14" />
                                </svg>
                            </span>
                            <input
                                id="study_program"
                                v-model="form.study_program"
                                type="text"
                                class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                                placeholder="Teknik Informatika"
                            >
                        </div>
                        <InputError class="mt-2" :message="form.errors.study_program" />
                    </div>
                </div>

                <div v-if="isMahasiswa">
                    <label for="nim" class="text-sm font-medium text-[#344054]">NIM <span class="text-[#f04438]">*</span></label>
                    <input
                        id="nim"
                        v-model="form.nim"
                        type="text"
                        class="mt-2 block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white px-4 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                        placeholder="123456789"
                    >
                    <InputError class="mt-2" :message="form.errors.nim" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="text-sm font-medium text-[#344054]">Kata Sandi <span class="text-[#f04438]">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                                    <rect x="4" y="10" width="16" height="10" rx="2" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-11 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                                placeholder="Min. 8 karakter"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#98a2b3]"
                                @click="passwordVisible = !passwordVisible"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-[#344054]">Konfirmasi Kata Sandi <span class="text-[#f04438]">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#98a2b3]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                                    <rect x="4" y="10" width="16" height="10" rx="2" />
                                </svg>
                            </span>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="passwordConfirmationVisible ? 'text' : 'password'"
                                required
                                autocomplete="new-password"
                                class="block h-12 w-full rounded-xl border border-[#d0d5dd] bg-white pl-10 pr-11 text-sm text-[#101828] placeholder:text-[#98a2b3] focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                                placeholder="Ulangi kata sandi"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#98a2b3]"
                                @click="passwordConfirmationVisible = !passwordConfirmationVisible"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <label class="flex items-start gap-3 rounded-xl py-1 text-sm text-[#475467]">
                    <input
                        v-model="form.terms"
                        type="checkbox"
                        class="mt-1 h-4 w-4 rounded border-[#d0d5dd] text-black focus:ring-black"
                    >
                    <span>Saya setuju dengan <span class="font-semibold text-[#101828]">Syarat &amp; Ketentuan</span> dan <span class="font-semibold text-[#101828]">Kebijakan Privasi</span></span>
                </label>
                <InputError :message="form.errors.terms" />

                <button
                    type="submit"
                    class="flex h-12 w-full items-center justify-center rounded-xl bg-black px-4 text-sm font-semibold text-white transition hover:bg-[#111111] disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="form.processing"
                >
                    Daftar Sekarang
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-[#667085]">
                Sudah punya akun?
                <Link :href="route('login')" class="font-semibold text-[#101828]">Masuk di sini</Link>
            </p>
        </div>
    </GuestLayout>
</template>
