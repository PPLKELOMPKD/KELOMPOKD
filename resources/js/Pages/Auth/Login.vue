<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    roles: Array,
    status: String,
});

const form = useForm({
    role: 'mahasiswa',
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const roleIcons = {
    mahasiswa: 'user',
    perusahaan: 'building',
    admin: 'shield',
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk" />

        <div class="mx-auto w-full max-w-[384px]">
            <div class="mb-8">
                <h1 class="text-[28px] font-bold tracking-tight text-[#0F172A]">
                    Selamat Datang
                </h1>
                <p class="mt-1 text-[15px] text-[#64748B]">Masuk ke akun SIKARA Anda</p>
            </div>

            <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-5">
                    <div>
                        <p class="text-[13px] font-bold uppercase tracking-wider text-[#64748B]">Masuk Sebagai</p>
                        <div class="mt-3 grid grid-cols-3 gap-3">
                            <button
                                v-for="roleOption in roles"
                                :key="roleOption.value"
                                type="button"
                                class="flex h-[84px] flex-col items-center justify-center gap-2 rounded-xl border-2 text-center transition-all duration-200"
                                :class="form.role === roleOption.value ? 'border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] shadow-sm shadow-[#2563EB]/10' : 'border-[#E2E8F0] bg-white text-[#64748B] hover:border-[#CBD5E1] hover:bg-[#F8FAFC]'"
                                @click="form.role = roleOption.value"
                            >
                                <svg v-if="roleIcons[roleOption.value] === 'user'" class="h-[22px] w-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                    <path d="M5 20a7 7 0 0 1 14 0" />
                                </svg>
                                <svg v-else-if="roleIcons[roleOption.value] === 'building'" class="h-[22px] w-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 20h16" />
                                    <path d="M7 20V6l5-2 5 2v14" />
                                    <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                                </svg>
                                <svg v-else class="h-[22px] w-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                                </svg>
                                <span class="text-[11px] font-bold">{{ roleOption.label }}</span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                    <div>
                        <label for="email" class="block text-[13px] font-bold text-[#0F172A] mb-1.5">Email</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#94A3B8]">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 6h16v12H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="block h-[46px] w-full rounded-xl border-[#E2E8F0] pl-10 pr-4 text-[14px] text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:ring-[#2563EB]/20 transition-all shadow-sm"
                                v-model="form.email"
                                required
                                autofocus
                                placeholder="nama@email.com"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-[13px] font-bold text-[#0F172A]">Kata Sandi</label>
                            <Link :href="route('password.request')" class="text-[13px] font-semibold text-[#2563EB] hover:text-[#1d4ed8] transition-colors">Lupa sandi?</Link>
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#94A3B8]">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                    <path d="M8 10V8a4 4 0 1 1 8 0v2" />
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="block h-[46px] w-full rounded-xl border-[#E2E8F0] pl-10 pr-4 text-[14px] text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:ring-[#2563EB]/20 transition-all shadow-sm"
                                v-model="form.password"
                                required
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-[#2563EB]/20 transition-colors" />
                            <span class="text-[13px] font-medium text-[#64748B]">Ingat saya</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="submit" class="flex h-[46px] w-full items-center justify-center rounded-xl bg-[#2563EB] text-[15px] font-semibold text-white shadow-lg shadow-[#2563EB]/20 transition-all hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 active:bg-[#1e40af]" :class="{ 'opacity-70 cursor-not-allowed': form.processing }" :disabled="form.processing">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-[14px] text-[#64748B]">
                Belum punya akun?
                <Link :href="route('register')" class="font-bold text-[#2563EB] hover:text-[#1d4ed8] transition-colors">Daftar sekarang</Link>
            </div>
        </div>
    </GuestLayout>
</template>
