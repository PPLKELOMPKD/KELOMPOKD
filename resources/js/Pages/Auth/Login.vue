<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    roles: Array,
    status: String,
});

// Detect if role is forced via URL (e.g. /login?role=perusahaan)
const urlParams = new URLSearchParams(window.location.search);
const forcedRole = urlParams.get('role');
const isRoleLocked = ref(false);

const form = useForm({
    role: 'mahasiswa', // Default
    email: '',
    password: '',
    remember: false,
});

onMounted(() => {
    // If a valid forced role is provided, lock the form to it
    if (forcedRole && props.roles.some(r => r.value === forcedRole)) {
        form.role = forcedRole;
        isRoleLocked.value = true;
    }
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

const getRoleLabel = (roleValue) => {
    const role = props.roles.find(r => r.value === roleValue);
    return role ? role.label : '';
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk — SIKARA" />

        <!-- Header -->
        <div class="mb-7">
            <h1 class="text-xl font-bold text-[#0F172A]">
                Selamat Datang{{ isRoleLocked ? `, ${getRoleLabel(form.role)}` : '' }}
            </h1>
            <p class="mt-1 text-sm text-[#64748B]">Masuk ke akun SIKARA Anda</p>
        </div>

        <!-- Status -->
        <div v-if="status" class="mb-5 rounded-lg border border-[#10B981]/25 bg-[#10B981]/8 px-4 py-3 text-sm text-[#10B981]">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Role selector (Hidden if role is locked via URL) -->
            <div v-if="!isRoleLocked">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#64748B]">Masuk sebagai</p>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        v-for="roleOption in roles"
                        :key="roleOption.value"
                        type="button"
                        class="flex flex-col items-center justify-center gap-1.5 rounded-xl border py-3 text-center transition-all duration-150"
                        :class="form.role === roleOption.value
                            ? 'border-[#2563EB] bg-[#EFF6FF] text-[#2563EB]'
                            : 'border-[#E2E8F0] bg-white text-[#64748B] hover:border-[#2563EB]/40 hover:bg-[#F8FAFC]'"
                        @click="form.role = roleOption.value"
                    >
                        <svg v-if="roleIcons[roleOption.value] === 'user'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /><path d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                        <svg v-else-if="roleIcons[roleOption.value] === 'building'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 20h16" /><path d="M7 20V6l5-2 5 2v14" />
                            <path d="M9 9h.01M9 12h.01M12 9h.01M12 12h.01M15 9h.01M15 12h.01" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                        </svg>
                        <span class="text-[11px] font-medium leading-tight">{{ roleOption.label }}</span>
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.role" />
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
                    <TextInput id="email" type="email" v-model="form.email" required autofocus placeholder="nama@email.com" class="block h-10 w-full rounded-lg border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:ring-[#2563EB]/10" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-[#0F172A]">Kata Sandi</label>
                    <Link :href="route('password.request')" class="text-xs text-[#2563EB] hover:underline">Lupa sandi?</Link>
                </div>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94A3B8]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V8a4 4 0 1 1 8 0v2" />
                        </svg>
                    </div>
                    <TextInput id="password" type="password" v-model="form.password" required placeholder="••••••••" class="block h-10 w-full rounded-lg border-[#E2E8F0] bg-white pl-9 pr-3 text-sm text-[#0F172A] placeholder:text-[#CBD5E1] focus:border-[#2563EB] focus:ring-[#2563EB]/10" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Remember -->
            <label class="flex cursor-pointer items-center gap-2.5">
                <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-[#E2E8F0] text-[#2563EB] focus:ring-[#2563EB]/20" />
                <span class="text-sm text-[#64748B]">Ingat saya</span>
            </label>

            <!-- Submit -->
            <button type="submit" class="flex h-10 w-full items-center justify-center rounded-lg bg-[#2563EB] text-sm font-semibold text-white transition-colors hover:bg-[#1d4ed8] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing">
                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Masuk
            </button>
        </form>

        <!-- Footer -->
        <p class="mt-6 text-center text-sm text-[#64748B]">
            Belum punya akun?
            <Link :href="route('register')" class="font-semibold text-[#2563EB] hover:underline">Daftar sekarang</Link>
        </p>

        <!-- Dynamic Back Link if locked -->
        <p v-if="isRoleLocked" class="mt-2 text-center text-xs text-[#94A3B8]">
            Ingin masuk dengan peran lain?
            <Link :href="route('login')" class="text-[#64748B] hover:text-[#0F172A] underline">Kembali</Link>
        </p>
    </GuestLayout>
</template>
