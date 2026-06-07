<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;

const props = defineProps({
    status: String,
});

const form = useForm({});
const resendSent = ref(false);

const submit = () => {
    form.post(route('verification.send'), {
        onSuccess: () => { resendSent.value = true; },
    });
};
</script>

<template>
    <Head title="Verifikasi Email — SIKARA" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20 px-4">
        <!-- Background decorations -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-blue-400/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-indigo-400/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <Link href="/" class="flex items-center gap-3 transition-transform hover:scale-105">
                    <img src="/images/Logo-SIKARA.png" alt="SIKARA" class="h-10 w-auto" />
                    <span class="text-2xl font-black tracking-tight text-slate-900">SIKARA</span>
                </Link>
            </div>

            <div class="rounded-[2rem] border border-white/60 bg-white/80 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-slate-200/50">
                <!-- Icon -->
                <div class="mb-6 flex justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 border-8 border-blue-50/50">
                        <svg class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                </div>

                <!-- Header -->
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
                        Verifikasi Email Anda
                    </h1>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                        Terima kasih telah mendaftar! Kami telah mengirim tautan verifikasi ke
                        <span class="font-semibold text-slate-700">{{ user?.email }}</span>.
                        Silakan cek inbox atau folder spam Anda.
                    </p>
                </div>

                <!-- Success message -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="resendSent || status === 'verification-link-sent'"
                        class="mb-5 flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-emerald-700">
                            Email verifikasi baru berhasil dikirim! Silakan cek inbox Anda.
                        </p>
                    </div>
                </Transition>

                <!-- Steps info -->
                <div class="mb-6 rounded-xl bg-slate-50 border border-slate-100 p-4 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Langkah Selanjutnya</p>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">1</span>
                        <p class="text-sm text-slate-600">Cek email di <span class="font-semibold">{{ user?.email }}</span></p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</span>
                        <p class="text-sm text-slate-600">Klik tautan "Verifikasi Email" dalam pesan yang dikirim</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-300 text-xs font-bold text-white">3</span>
                        <p class="text-sm text-slate-500">Setelah email terverifikasi, tunggu konfirmasi admin untuk mengakses semua fitur</p>
                    </div>
                </div>

                <!-- Resend button -->
                <form @submit.prevent="submit">
                    <button
                        type="submit"
                        class="group relative flex h-12 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-600/35 focus:outline-none focus:ring-4 focus:ring-indigo-600/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100"
                        :disabled="form.processing"
                    >
                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white relative z-10" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="relative z-10">Kirim Ulang Email Verifikasi</span>
                    </button>
                </form>

                <!-- Logout link -->
                <div class="mt-4 text-center">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm font-medium text-slate-500 transition-colors hover:text-red-600 hover:underline"
                    >
                        Keluar dari akun
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
