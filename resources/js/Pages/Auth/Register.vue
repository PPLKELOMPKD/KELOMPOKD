<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ roles: Array });

const roleMeta = {
    mahasiswa: { label: 'Mahasiswa', description: 'Cari lowongan magang' },
    perusahaan: { label: 'Perusahaan', description: 'Posting lowongan' },
};

const passwordVisible = ref(false);
const passwordConfirmationVisible = ref(false);

const form = useForm({
    role: 'mahasiswa',
    name: '', email: '', phone: '',
    university: '', study_program: '', nim: '',
    password: '', password_confirmation: '', terms: false,
});

const isMahasiswa = computed(() => form.role === 'mahasiswa');
const availableRoles = computed(() => props.roles.filter((r) => r.value in roleMeta));
const nameLabel = computed(() => isMahasiswa.value ? 'Nama Lengkap' : 'Nama Perusahaan');
const namePlaceholder = computed(() => isMahasiswa.value ? 'Nama lengkap Anda' : 'Nama perusahaan');

const selectRole = (role) => {
    form.role = role;
    if (role !== 'mahasiswa') { form.university = ''; form.study_program = ''; form.nim = ''; }
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
        <Head title="Daftar — SIKARA" />

        <!-- Header -->
        <div class="mb-7">
            <h1 class="text-xl font-bold text-[#0F172A]">Buat Akun</h1>
            <p class="mt-1 text-sm text-[#64748B]">Bergabung dengan platform karir mahasiswa</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">

            <!-- Role selector -->
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Daftar sebagai</p>
                <div class="grid grid-cols-2 gap-2">
                    <button v-for="role in availableRoles" :key="role.value" type="button"
                        class="flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all duration-150"
                        :class="form.role === role.value
                            ? 'border-[#2563EB] bg-[#EFF6FF]'
                            : 'border-[#E2E8F0] bg-white hover:border-[#2563EB]/40 hover:bg-[#F8FAFC]'"
                        @click="selectRole(role.value)">
                        <svg v-if="role.value === 'mahasiswa'" class="h-4 w-4 flex-shrink-0"
                             :class="form.role === role.value ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                            <path d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                        <svg v-else class="h-4 w-4 flex-shrink-0"
                             :class="form.role === role.value ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M6 21h12M7 21V7l5-3 5 3v14" />
                            <path d="M10 10h.01M10 13h.01M14 10h.01M14 13h.01" />
                        </svg>
                        <div>
                            <div class="text-sm font-semibold" :class="form.role === role.value ? 'text-[#0F172A]' : 'text-[#64748B]'">
                                {{ roleMeta[role.value].label }}
                            </div>
                            <div class="text-xs text-[#94A3B8]">{{ roleMeta[role.value].description }}</div>
                        </div>
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.role" />
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-[#0F172A]">{{ nameLabel }}</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" /><path d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                    </div>
                    <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name"
                        :placeholder="namePlaceholder"
                        class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-[#0F172A]">Email</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 6h16v12H4z" /><path d="m4 7 8 6 8-6" />
                        </svg>
                    </div>
                    <input id="email" v-model="form.email" type="email" required autocomplete="username"
                        placeholder="nama@email.com"
                        class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-[#0F172A]">Nomor Telepon</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                        </svg>
                    </div>
                    <input id="phone" v-model="form.phone" type="tel" required autocomplete="tel"
                        placeholder="+62 812-3456-7890"
                        class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.phone" />
            </div>

            <!-- Mahasiswa fields -->
            <template v-if="isMahasiswa">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="university" class="block text-sm font-medium text-[#0F172A]">Universitas</label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m3 9 9-5 9 5-9 5-9-5Z" /><path d="M5 10.5V15l7 4 7-4v-4.5" />
                                </svg>
                            </div>
                            <input id="university" v-model="form.university" type="text" placeholder="Universitas"
                                class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.university" />
                    </div>
                    <div>
                        <label for="study_program" class="block text-sm font-medium text-[#0F172A]">Program Studi</label>
                        <div class="relative mt-1.5">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 5h16M4 12h16M4 19h16M8 5v14M16 5v14" />
                                </svg>
                            </div>
                            <input id="study_program" v-model="form.study_program" type="text" placeholder="Teknik Informatika"
                                class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.study_program" />
                    </div>
                </div>
                <div>
                    <label for="nim" class="block text-sm font-medium text-[#0F172A]">NIM</label>
                    <input id="nim" v-model="form.nim" type="text" placeholder="123456789"
                        class="mt-1.5 block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white px-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                    <InputError class="mt-1.5" :message="form.errors.nim" />
                </div>
            </template>

            <!-- Password fields -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-sm font-medium text-[#0F172A]">Kata Sandi</label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V8a4 4 0 1 1 8 0v2" />
                            </svg>
                        </div>
                        <input id="password" v-model="form.password" :type="passwordVisible ? 'text' : 'password'"
                            required autocomplete="new-password" placeholder="Min. 8 karakter"
                            class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-9 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                        <button type="button" @click="passwordVisible = !passwordVisible"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#94A3B8] hover:text-[#64748B]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" /><circle cx="12" cy="12" r="2.5" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-[#0F172A]">Konfirmasi</label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V8a4 4 0 1 1 8 0v2" />
                            </svg>
                        </div>
                        <input id="password_confirmation" v-model="form.password_confirmation"
                            :type="passwordConfirmationVisible ? 'text' : 'password'"
                            required autocomplete="new-password" placeholder="Ulangi sandi"
                            class="block h-10 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-9 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]/10" />
                        <button type="button" @click="passwordConfirmationVisible = !passwordConfirmationVisible"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#94A3B8] hover:text-[#64748B]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" /><circle cx="12" cy="12" r="2.5" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <!-- Terms -->
            <label class="flex cursor-pointer items-start gap-2.5">
                <input v-model="form.terms" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-[#2563EB]/20" />
                <span class="text-sm text-[#64748B]">
                    Saya setuju dengan <span class="font-medium text-[#0F172A]">Syarat &amp; Ketentuan</span>
                </span>
            </label>
            <InputError :message="form.errors.terms" />

            <!-- Submit -->
            <button type="submit"
                class="flex h-10 w-full items-center justify-center rounded-lg bg-[#2563EB] text-sm font-semibold text-white transition-colors hover:bg-[#1d4ed8] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing">
                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-[#64748B]">
            Sudah punya akun?
            <Link :href="route('login')" class="font-semibold text-[#2563EB] hover:underline">Masuk di sini</Link>
        </p>
    </GuestLayout>
</template>
