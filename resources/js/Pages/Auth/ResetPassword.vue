<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Kata Sandi — SIKARA" />

        <!-- Header Form -->
        <div class="mb-8 relative z-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#0F172A]">
                Buat Kata Sandi Baru
            </h1>
            <p class="mt-2 text-[15px] leading-relaxed text-[#64748B]">
                Masukkan alamat email Anda dan buat kata sandi baru yang kuat untuk akun SIKARA Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email Input -->
            <div class="relative group">
                <label for="email" class="block text-sm font-bold text-[#0F172A] mb-2 group-focus-within:text-[#2563EB] transition-colors">Alamat Email SIKARA</label>
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
                        readonly
                        class="block h-12 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] pl-11 pr-4 text-[15px] font-medium text-[#64748B] opacity-80 cursor-not-allowed transition-all"
                    />
                </div>
                <InputError class="mt-2 font-medium" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="relative group">
                <label for="password" class="block text-sm font-bold text-[#0F172A] mb-2 group-focus-within:text-[#2563EB] transition-colors">Kata Sandi Baru</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#94A3B8] group-focus-within:text-[#2563EB] transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="10" width="14" height="10" rx="2" />
                            <path d="M8 10V8a4 4 0 1 1 8 0v2" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        placeholder="••••••••"
                        class="block h-12 w-full rounded-xl border border-[#E2E8F0] bg-white pl-11 pr-4 text-[15px] font-medium text-[#0F172A] placeholder:text-[#94A3B8] transition-all focus:border-[#2563EB] focus:outline-none focus:ring-4 focus:ring-[#2563EB]/10 hover:border-[#CBD5E1]"
                    />
                </div>
                <InputError class="mt-2 font-medium" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div class="relative group">
                <label for="password_confirmation" class="block text-sm font-bold text-[#0F172A] mb-2 group-focus-within:text-[#2563EB] transition-colors">Konfirmasi Kata Sandi Baru</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#94A3B8] group-focus-within:text-[#2563EB] transition-colors">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        placeholder="••••••••"
                        class="block h-12 w-full rounded-xl border border-[#E2E8F0] bg-white pl-11 pr-4 text-[15px] font-medium text-[#0F172A] placeholder:text-[#94A3B8] transition-all focus:border-[#2563EB] focus:outline-none focus:ring-4 focus:ring-[#2563EB]/10 hover:border-[#CBD5E1]"
                    />
                </div>
                <InputError class="mt-2 font-medium" :message="form.errors.password_confirmation" />
            </div>

            <!-- Security Requirements Info -->
            <div class="mt-2 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] p-4">
                <h4 class="text-xs font-bold text-[#475569] mb-2">Persyaratan Kata Sandi:</h4>
                <ul class="text-[11px] text-[#64748B] space-y-1.5 list-disc pl-4">
                    <li>Minimal 8 karakter</li>
                    <li>Mengandung huruf dan angka</li>
                    <li>Tidak boleh sama dengan email Anda</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="relative mt-6 flex h-12 w-full items-center justify-center rounded-xl bg-[#0F172A] text-[15px] font-bold text-white shadow-lg transition-all overflow-hidden group hover:bg-[#1E293B] focus:outline-none focus:ring-4 focus:ring-[#0F172A]/20 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="form.processing"
            >
                <!-- Background hover effect -->
                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                
                <span v-if="!form.processing" class="flex items-center gap-2">
                    Simpan Kata Sandi Baru
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
                
                <span v-else class="flex items-center gap-3">
                    <svg class="h-5 w-5 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Menyimpan Perubahan...
                </span>
            </button>
        </form>
    </GuestLayout>
</template>

<style scoped>
@keyframes shimmer {
  100% { transform: translateX(100%); }
}
</style>
