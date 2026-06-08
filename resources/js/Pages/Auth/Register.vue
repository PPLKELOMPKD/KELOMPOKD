<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ roles: Array });
const page = usePage();
const showDisabledModal = ref(false);

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
    legal_document: null,
    password: '', password_confirmation: '', terms: false,
});

const isMahasiswa = computed(() => form.role === 'mahasiswa');
const availableRoles = computed(() => props.roles.filter((r) => r.value in roleMeta));
const nameLabel = computed(() => isMahasiswa.value ? 'Nama Lengkap' : 'Nama Perusahaan');
const namePlaceholder = computed(() => isMahasiswa.value ? 'Nama lengkap Anda' : 'Nama perusahaan');

const selectRole = (role) => {
    form.role = role;
    if (role !== 'mahasiswa') { form.university = ''; form.study_program = ''; form.nim = ''; }
    if (role !== 'perusahaan') { form.legal_document = null; }
};

const submit = () => {
    if (page.props.global_settings?.registration_enabled === 'false') {
        showDisabledModal.value = true;
        return;
    }

    form.transform((data) => ({
        ...data,
        phone: data.phone || null,
        university: isMahasiswa.value ? data.university : null,
        study_program: isMahasiswa.value ? data.study_program : null,
        nim: isMahasiswa.value ? data.nim : null,
        legal_document: !isMahasiswa.value ? data.legal_document : null,
    })).post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar — SIKARA" />

        <div class="rounded-[2rem] border border-white/60 bg-white/80 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-slate-200/50">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-[28px] font-extrabold tracking-tight text-slate-900">Buat Akun Baru</h1>
                <p class="mt-2 text-[15px] text-slate-500">Bergabunglah dengan platform karir terdepan</p>
            </div>

            <form class="space-y-6" @submit.prevent="submit">

                <!-- Role selector -->
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Daftar Sebagai</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button v-for="role in availableRoles" :key="role.value" type="button"
                            class="group relative flex flex-col items-start gap-3 overflow-hidden rounded-2xl border p-4 text-left transition-all duration-300 ease-out hover:-translate-y-1 focus:outline-none"
                            :class="form.role === role.value
                                ? 'border-indigo-600 bg-indigo-50/50 shadow-md shadow-indigo-100 ring-1 ring-indigo-600'
                                : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-md hover:shadow-slate-100'"
                            @click="selectRole(role.value)">
                            
                            <!-- Role Icon -->
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300"
                                :class="form.role === role.value ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600'">
                                <svg v-if="role.value === 'mahasiswa'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                                    <path d="M5 20a7 7 0 0 1 14 0" />
                                </svg>
                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 21h12M7 21V7l5-3 5 3v14" />
                                    <path d="M10 10h.01M10 13h.01M14 10h.01M14 13h.01" />
                                </svg>
                            </div>
                            
                            <div>
                                <div class="text-[15px] font-bold" :class="form.role === role.value ? 'text-indigo-900' : 'text-slate-700'">
                                    {{ roleMeta[role.value].label }}
                                </div>
                                <div class="mt-0.5 text-[11px] font-medium" :class="form.role === role.value ? 'text-indigo-600' : 'text-slate-400'">
                                    {{ roleMeta[role.value].description }}
                                </div>
                            </div>

                            <!-- Active Indicator -->
                            <div v-if="form.role === role.value" class="absolute right-3 top-3 flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white animate-fade-in shadow-sm">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <path d="M20 6L9 17l-5-5" />
                                </svg>
                            </div>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <!-- Input Group Styles -->
                <div class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700">{{ nameLabel }}</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" /><path d="M5 20a7 7 0 0 1 14 0" />
                                </svg>
                            </div>
                            <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name"
                                :placeholder="namePlaceholder"
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 6h16v12H4z" /><path d="m4 7 8 6 8-6" />
                                </svg>
                            </div>
                            <input id="email" v-model="form.email" type="email" required autocomplete="username"
                                placeholder="nama@email.com"
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700">Nomor WhatsApp / Telepon</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                                </svg>
                            </div>
                            <input id="phone" v-model="form.phone" type="tel" required autocomplete="tel"
                                placeholder="+62 812-3456-7890"
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                </div>

                <!-- Mahasiswa fields -->
                <div v-show="isMahasiswa" class="space-y-5 overflow-hidden transition-all duration-300">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="university" class="block text-sm font-semibold text-slate-700">Universitas</label>
                            <div class="relative mt-2">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m3 9 9-5 9 5-9 5-9-5Z" /><path d="M5 10.5V15l7 4 7-4v-4.5" />
                                    </svg>
                                </div>
                                <input id="university" v-model="form.university" type="text" placeholder="Universitas"
                                    class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.university" />
                        </div>
                        <div>
                            <label for="study_program" class="block text-sm font-semibold text-slate-700">Program Studi</label>
                            <div class="relative mt-2">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 5h16M4 12h16M4 19h16M8 5v14M16 5v14" />
                                    </svg>
                                </div>
                                <input id="study_program" v-model="form.study_program" type="text" placeholder="Jurusan"
                                    class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.study_program" />
                        </div>
                    </div>
                    <div>
                        <label for="nim" class="block text-sm font-semibold text-slate-700">Nomor Induk Mahasiswa (NIM)</label>
                        <input id="nim" v-model="form.nim" type="text" placeholder="123456789"
                            class="mt-2 block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 px-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                        <InputError class="mt-2" :message="form.errors.nim" />
                    </div>
                </div>

                <!-- Perusahaan fields -->
                <div v-show="!isMahasiswa" class="space-y-5 overflow-hidden transition-all duration-300">
                    <div>
                        <label for="legal_document" class="block text-sm font-semibold text-slate-700">Dokumen Legalitas (PDF)</label>
                        <div class="mt-2 flex justify-center rounded-xl border border-dashed border-slate-300 px-6 py-8 hover:border-indigo-500 hover:bg-indigo-50/50 transition-colors">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 4v12M12 4l-4 4M12 4l4 4M4 20h16" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                    <label for="legal_document" class="relative cursor-pointer rounded-md bg-transparent font-bold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                        <span>Unggah file PDF</span>
                                        <input id="legal_document" type="file" accept=".pdf" @change="e => form.legal_document = e.target.files[0]" :required="!isMahasiswa" class="sr-only" />
                                    </label>
                                    <p class="pl-1">atau tarik dan lepas</p>
                                </div>
                                <p class="text-xs leading-5 text-slate-500 mt-2">Wajib diunggah (Maks. 5MB).</p>
                                <p v-if="form.legal_document" class="text-xs leading-5 font-semibold text-indigo-600 mt-1 break-all">
                                    File dipilih: {{ form.legal_document.name }}
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.legal_document" />
                    </div>
                </div>

                <!-- Password fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V8a4 4 0 1 1 8 0v2" />
                                </svg>
                            </div>
                            <input id="password" v-model="form.password" :type="passwordVisible ? 'text' : 'password'"
                                required autocomplete="new-password" placeholder="Min. 8 karakter"
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-11 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                            <button type="button" @click="passwordVisible = !passwordVisible"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" /><circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V8a4 4 0 1 1 8 0v2" />
                                </svg>
                            </div>
                            <input id="password_confirmation" v-model="form.password_confirmation"
                                :type="passwordConfirmationVisible ? 'text' : 'password'"
                                required autocomplete="new-password" placeholder="Ulangi sandi"
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-11 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10" />
                            <button type="button" @click="passwordConfirmationVisible = !passwordConfirmationVisible"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" /><circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <!-- Terms -->
                <div class="pt-2">
                    <label class="flex cursor-pointer items-start gap-3 group">
                        <div class="relative flex items-center mt-0.5">
                            <input v-model="form.terms" type="checkbox" class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-slate-300 bg-white transition-all checked:border-indigo-600 checked:bg-indigo-600 hover:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-600/20" />
                            <svg class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white opacity-0 transition-opacity peer-checked:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-[13px] text-slate-600 leading-relaxed">
                            Saya setuju dengan <a href="#" class="font-bold text-indigo-600 hover:text-indigo-500 hover:underline transition-colors">Syarat & Ketentuan</a> serta <a href="#" class="font-bold text-indigo-600 hover:text-indigo-500 hover:underline transition-colors">Kebijakan Privasi</a> SIKARA
                        </span>
                    </label>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </div>

                <!-- Submit -->
                <div class="pt-2">
                    <button type="submit"
                        class="group relative flex h-14 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-[15px] font-bold text-white shadow-lg shadow-indigo-600/30 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-600/40 focus:outline-none focus:ring-4 focus:ring-indigo-600/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100"
                        :disabled="form.processing">
                        
                        <!-- Shine effect -->
                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                        
                        <svg v-if="form.processing" class="mr-2 h-5 w-5 animate-spin text-white relative z-10" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="relative z-10 tracking-wide">Daftar Sekarang</span>
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-sm font-medium text-slate-500">
                Sudah punya akun?
                <Link :href="route('login')" class="font-bold text-indigo-600 transition-all hover:text-indigo-500 hover:underline">Masuk di sini</Link>
            </div>
        </div>

        <!-- Registration Disabled Modal -->
        <Modal :show="showDisabledModal" @close="showDisabledModal = false" maxWidth="md">
            <div class="relative overflow-hidden bg-white p-8 text-center rounded-[2rem]">
                <!-- Decorative Elements -->
                <div class="absolute -right-24 -top-24 h-48 w-48 rounded-full bg-red-500 opacity-5 blur-2xl"></div>
                <div class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-orange-500 opacity-5 blur-2xl"></div>
                
                <div class="relative z-10">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-50 border-8 border-red-50/50">
                        <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    
                    <h2 class="mb-3 text-2xl font-extrabold text-slate-900">Pendaftaran Ditutup</h2>
                    <p class="mb-8 text-[15px] leading-relaxed text-slate-500">
                        Mohon maaf, pendaftaran akun baru saat ini sedang dinonaktifkan oleh Administrator. Silakan coba lagi nanti.
                    </p>
                    
                    <button @click="showDisabledModal = false" type="button" class="flex h-12 w-full items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-900 transition-colors hover:bg-slate-200">
                        Mengerti, Kembali
                    </button>
                </div>
            </div>
        </Modal>
    </GuestLayout>
</template>
