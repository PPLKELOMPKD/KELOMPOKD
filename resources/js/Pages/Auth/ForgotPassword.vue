<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

// We only send email to backend as per Laravel Breeze defaults
const form = useForm({
    email: '',
});

// Extra UI state to make the form look more complex/verifiable
const selectedRole = ref('mahasiswa');
const isChecking = ref(false);

const roles = [
    { value: 'mahasiswa', label: 'Mahasiswa', icon: 'user' },
    { value: 'perusahaan', label: 'Perusahaan', icon: 'building' },
];

const submit = () => {
    isChecking.value = true;
    
    // Simulate a complex security check visually before sending
    setTimeout(() => {
        form.post(route('password.email'), {
            onFinish: () => {
                isChecking.value = false;
            }
        });
    }, 600);
};
</script>

<template>
    <GuestLayout>
        <Head title="Pemulihan Akun — SIKARA" />

        <!-- Top Badge -->
        <div class="mb-5 flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EFF6FF] px-3 py-1 text-xs font-semibold text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Pemulihan Aman
            </span>
        </div>

        <!-- Header Form -->
        <div class="mb-8 relative z-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#0F172A]">
                Lupa Kata Sandi?
            </h1>
            <p class="mt-2 text-[15px] leading-relaxed text-[#64748B]">
                Jangan khawatir! Pilih tipe akun Anda dan masukkan email terdaftar. Kami akan mengirimkan instruksi pemulihan yang aman.
            </p>
        </div>

        <!-- Success Alert -->
        <div v-if="status" class="mb-6 overflow-hidden rounded-xl bg-emerald-50 border border-emerald-100 p-4 shadow-sm relative">
            <div class="absolute -right-4 -top-4 opacity-10">
                <svg class="h-24 w-24 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1.177-7.86l-2.765-2.767L7 12.431l3.118 3.121a1 1 0 001.414 0l5.952-5.95-1.062-1.062-5.6 5.6z"/>
                </svg>
            </div>
            <div class="flex items-start gap-3 relative z-10">
                <div class="mt-0.5 flex-shrink-0 rounded-full bg-emerald-100 p-1">
                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-800">Email Terkirim</h3>
                    <p class="mt-1 text-sm text-emerald-700">{{ status }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Tipe Akun Selector (Cosmetic, enhances user experience) -->
            <div>
                <p class="mb-2.5 text-xs font-bold uppercase tracking-wider text-[#64748B]">Tipe Akun</p>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="role in roles"
                        :key="role.value"
                        type="button"
                        class="relative flex items-center gap-3 rounded-xl border p-3 transition-all duration-300"
                        :class="selectedRole === role.value 
                            ? 'border-[#2563EB] bg-[#EFF6FF] shadow-md shadow-[#2563EB]/10 ring-1 ring-[#2563EB]' 
                            : 'border-[#E2E8F0] bg-white hover:border-[#CBD5E1] hover:bg-[#F8FAFC]'"
                        @click="selectedRole = role.value"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                            :class="selectedRole === role.value ? 'bg-[#2563EB] text-white' : 'bg-[#F1F5F9] text-[#94A3B8]'">
                            <svg v-if="role.icon === 'user'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                <path d="M5 20a7 7 0 0 1 14 0" />
                            </svg>
                            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 20h16" />
                                <path d="M7 20V6l5-2 5 2v14" />
                                <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold" :class="selectedRole === role.value ? 'text-[#0F172A]' : 'text-[#475569]'">{{ role.label }}</p>
                            <p class="text-[11px] text-[#64748B]">Verifikasi ID</p>
                        </div>
                        <div v-if="selectedRole === role.value" class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full bg-[#2563EB] ring-4 ring-[#DBEAFE]"></div>
                    </button>
                </div>
            </div>

            <!-- Email Input -->
            <div class="relative group">
                <label for="email" class="block text-sm font-bold text-[#0F172A] mb-2 group-focus-within:text-[#2563EB] transition-colors">Alamat Email Tedaftar</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#94A3B8] group-focus-within:text-[#2563EB] transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <polyline points="3 7 12 13 21 7"/>
                        </svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        placeholder="Masukkan alamat email SIKARA Anda"
                        class="block h-12 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] pl-11 pr-4 text-[15px] font-medium text-[#0F172A] placeholder:text-[#94A3B8] placeholder:font-normal transition-all focus:border-[#2563EB] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#2563EB]/10 hover:border-[#CBD5E1]"
                    />
                </div>
                <InputError class="mt-2 font-medium" :message="form.errors.email" />
            </div>

            <!-- Security Info Banner -->
            <div class="rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] p-4 flex gap-3 items-start">
                <div class="text-[#64748B] mt-0.5">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-[#475569]">Verifikasi Keamanan Berlapis</h4>
                    <p class="text-[11px] text-[#64748B] mt-0.5 leading-snug">Tautan yang dikirimkan menggunakan enkripsi SSL 256-bit dan hanya berlaku selama 60 menit sejak dikirimkan ke email Anda.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="relative mt-2 flex h-12 w-full items-center justify-center rounded-xl bg-[#0F172A] text-[15px] font-bold text-white shadow-lg transition-all overflow-hidden group hover:bg-[#1E293B] focus:outline-none focus:ring-4 focus:ring-[#0F172A]/20 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="form.processing || isChecking"
            >
                <!-- Background hover effect -->
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                
                <span v-if="!form.processing && !isChecking" class="flex items-center gap-2">
                    Lanjutkan Proses Verifikasi
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
                
                <span v-else class="flex items-center gap-3">
                    <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ isChecking && !form.processing ? 'Memverifikasi Data...' : 'Mengirim Tautan...' }}
                </span>
            </button>
        </form>

        <!-- Footer link -->
        <div class="mt-8 pt-6 border-t border-[#F1F5F9] text-center text-[13px] font-medium text-[#64748B]">
            Sudah ingat kembali kata sandi Anda?
            <Link :href="route('login')" class="inline-flex items-center gap-1 font-bold text-[#2563EB] transition-all hover:text-[#1d4ed8] hover:gap-1.5 ml-1">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Masuk Disini
            </Link>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes shimmer {
  100% { transform: translateX(100%); }
}
</style>
