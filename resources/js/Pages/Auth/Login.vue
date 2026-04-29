<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
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

const roleMeta = {
    mahasiswa: { label: 'Mahasiswa', description: 'Cari Lowongan' },
    perusahaan: { label: 'Perusahaan', description: 'Buka Rekrutmen' },
    admin: { label: 'Admin', description: 'Kelola Sistem' },
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk — SIKARA" />

        <!-- Header Form -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A] sm:text-3xl">
                Selamat Datang
            </h1>
            <p class="mt-2 text-sm text-[#64748B] sm:text-base">Masuk ke akun SIKARA Anda untuk melanjutkan.</p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-[#10B981]">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Role Selector -->
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Masuk Sebagai</p>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="roleOption in roles"
                        :key="roleOption.value"
                        type="button"
                        class="flex flex-col items-center justify-center gap-1.5 rounded-xl border px-2 py-3 transition-all duration-200"
                        :class="form.role === roleOption.value 
                            ? 'border-[#2563EB] bg-[#EFF6FF] shadow-sm ring-1 ring-[#2563EB]/20' 
                            : 'border-[#E2E8F0] bg-white hover:border-[#2563EB]/40 hover:bg-[#F8FAFC]'"
                        @click="form.role = roleOption.value"
                    >
                        <svg v-if="roleIcons[roleOption.value] === 'user'" class="h-5 w-5" 
                            :class="form.role === roleOption.value ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                            <path d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                        <svg v-else-if="roleIcons[roleOption.value] === 'building'" class="h-5 w-5" 
                            :class="form.role === roleOption.value ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 20h16" />
                            <path d="M7 20V6l5-2 5 2v14" />
                            <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                        </svg>
                        <svg v-else class="h-5 w-5" 
                            :class="form.role === roleOption.value ? 'text-[#2563EB]' : 'text-[#94A3B8]'"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                        </svg>
                        <span class="text-[11px] font-semibold" 
                            :class="form.role === roleOption.value ? 'text-[#0F172A]' : 'text-[#64748B]'">
                            {{ roleOption.label }}
                        </span>
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.role" />
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-[#0F172A]">Email</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16v12H4z" />
                            <path d="m4 7 8 6 8-6" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        placeholder="nama@email.com"
                        class="block h-11 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] transition-shadow focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-[#0F172A]">Kata Sandi</label>
                    <Link :href="route('password.request')" class="text-xs font-semibold text-[#2563EB] hover:text-[#1d4ed8] hover:underline">
                        Lupa sandi?
                    </Link>
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                        class="block h-11 w-full rounded-lg border border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] transition-shadow focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <label class="flex cursor-pointer items-center gap-2.5">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-[#2563EB]/20" />
                <span class="text-sm font-medium text-[#64748B]">Ingat saya</span>
            </label>

            <!-- Submit Button -->
            <button
                type="submit"
                class="mt-6 flex h-11 w-full items-center justify-center rounded-lg bg-[#2563EB] text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/50 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="form.processing"
            >
                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Masuk ke SIKARA
            </button>
        </form>

        <!-- Footer link -->
        <div class="mt-8 text-center text-sm text-[#64748B]">
            Belum punya akun?
            <Link :href="route('register')" class="font-semibold text-[#2563EB] transition-colors hover:text-[#1d4ed8] hover:underline">
                Daftar sekarang
            </Link>
        </div>
    </GuestLayout>
</template>
