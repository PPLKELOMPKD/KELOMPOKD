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
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-black text-2xl font-bold text-white">
                    SK
                </div>
                <h1 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-[#101828]">
                    Selamat Datang
                </h1>
                <p class="mt-2 text-base text-[#4a5565]">Masuk ke akun SIKARA Anda</p>
            </div>

            <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-6">
                    <div>
                        <p class="text-sm font-medium text-[#364153]">Masuk Sebagai</p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                        <button
                            v-for="roleOption in roles"
                            :key="roleOption.value"
                            type="button"
                            class="flex h-[84px] flex-col items-center justify-center gap-2 rounded-xl border-2 text-center transition"
                            :class="form.role === roleOption.value ? 'border-black bg-[#f9fafb] text-black' : 'border-[#e5e7eb] bg-white text-[#4a5565] hover:border-[#cbd5e1]'"
                            @click="form.role = roleOption.value"
                        >
                            <svg v-if="roleIcons[roleOption.value] === 'user'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                <path d="M5 20a7 7 0 0 1 14 0" />
                            </svg>
                            <svg v-else-if="roleIcons[roleOption.value] === 'building'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 20h16" />
                                <path d="M7 20V6l5-2 5 2v14" />
                                <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                            </svg>
                            <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                            </svg>
                            <span class="text-xs font-medium">{{ roleOption.label }}</span>
                        </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                    <div>
                        <label for="email" class="text-sm font-medium text-[#364153]">Email</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94a3b8]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16v12H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="block h-[50px] w-full rounded-xl border-[#d1d5dc] pl-10 pr-4 text-base text-[#101828] placeholder:text-black/50 focus:border-black focus:ring-black"
                                v-model="form.email"
                                required
                                autofocus
                                placeholder="nama@email.com"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="text-sm font-medium text-[#364153]">Kata Sandi</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#94a3b8]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                    <path d="M8 10V8a4 4 0 1 1 8 0v2" />
                                </svg>
                            </div>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[#94a3b8]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="block h-[50px] w-full rounded-xl border-[#d1d5dc] pl-10 pr-11 text-base text-[#101828] placeholder:text-black/50 focus:border-black focus:ring-black"
                                v-model="form.password"
                                required
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between text-sm text-[#364153]">
                        <label class="flex items-center gap-2">
                            <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-[#d1d5dc] text-black focus:ring-black" />
                            <span>Ingat saya</span>
                        </label>
                        <Link :href="route('password.request')" class="font-medium hover:text-black">Lupa sandi?</Link>
                    </div>
                </div>

                <div class="mt-8">
                    <PrimaryButton class="h-12 w-full justify-center rounded-xl bg-black text-base normal-case tracking-normal hover:bg-black focus:bg-black active:bg-black" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Masuk
                    </PrimaryButton>
                </div>
            </form>

            <div class="mt-5 text-center text-sm text-[#4a5565]">
                Belum punya akun?
                <Link :href="route('register')" class="font-semibold text-black">Daftar sekarang</Link>
            </div>
        </div>
    </GuestLayout>
</template>
