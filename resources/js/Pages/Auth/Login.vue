<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const showMaintenanceModal = ref(false);

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
    if (page.props.global_settings?.maintenance_mode === 'true' && form.role !== 'admin') {
        showMaintenanceModal.value = true;
        return;
    }

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

        <div class="rounded-[2rem] border border-white/60 bg-white/80 backdrop-blur-xl p-8 sm:p-10 shadow-2xl shadow-slate-200/50">
            <!-- Header Form -->
            <div class="mb-8">
                <h1 class="text-[28px] font-extrabold tracking-tight text-slate-900">
                    Selamat Datang
                </h1>
                <p class="mt-2 text-[15px] text-slate-500">Masuk ke akun SIKARA Anda untuk melanjutkan.</p>
            </div>

            <div v-if="status" class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-600 border border-emerald-100">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Role Selector -->
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Masuk Sebagai</p>
                    <div class="grid grid-cols-3 gap-3">
                        <button
                            v-for="roleOption in roles"
                            :key="roleOption.value"
                            type="button"
                            :dusk="'role-' + roleOption.value"
                            class="group relative flex flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border p-3 transition-all duration-300 ease-out hover:-translate-y-1 focus:outline-none"
                            :class="form.role === roleOption.value 
                                ? 'border-indigo-600 bg-indigo-50/50 shadow-md shadow-indigo-100 ring-1 ring-indigo-600' 
                                : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-md hover:shadow-slate-100'"
                            @click="form.role = roleOption.value"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300"
                                :class="form.role === roleOption.value ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600'">
                                <svg v-if="roleIcons[roleOption.value] === 'user'" class="h-5 w-5" 
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                    <path d="M5 20a7 7 0 0 1 14 0" />
                                </svg>
                                <svg v-else-if="roleIcons[roleOption.value] === 'building'" class="h-5 w-5" 
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 20h16" />
                                    <path d="M7 20V6l5-2 5 2v14" />
                                    <path d="M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01M15 9h.01M15 12h.01M15 15h.01" />
                                </svg>
                                <svg v-else class="h-5 w-5" 
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 3 5 6v6c0 4.97 2.98 7.77 7 9 4.02-1.23 7-4.03 7-9V6l-7-3Z" />
                                </svg>
                            </div>
                            <span class="text-[12px] font-bold" 
                                :class="form.role === roleOption.value ? 'text-indigo-900' : 'text-slate-700'">
                                {{ roleOption.label }}
                            </span>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                            <Link :href="route('password.request')" class="text-xs font-bold text-indigo-600 transition-colors hover:text-indigo-500 hover:underline">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
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
                                class="block h-12 w-full rounded-xl border-slate-200 bg-slate-50/50 pl-11 pr-4 text-sm text-slate-900 transition-all placeholder:text-slate-400 focus:border-indigo-600 focus:bg-white focus:ring-4 focus:ring-indigo-600/10"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="pt-1">
                    <label class="flex cursor-pointer items-center gap-3 group">
                        <div class="relative flex items-center">
                            <input v-model="form.remember" type="checkbox" class="peer h-5 w-5 cursor-pointer appearance-none rounded border-2 border-slate-300 bg-white transition-all checked:border-indigo-600 checked:bg-indigo-600 hover:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-600/20" />
                            <svg class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white opacity-0 transition-opacity peer-checked:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-600 group-hover:text-slate-800 transition-colors">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="group relative flex h-14 w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-[15px] font-bold text-white shadow-lg shadow-indigo-600/30 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-600/40 focus:outline-none focus:ring-4 focus:ring-indigo-600/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100"
                        :disabled="form.processing"
                    >
                        <!-- Shine effect -->
                        <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 group-hover:translate-x-full"></div>
                        
                        <svg v-if="form.processing" class="mr-2 h-5 w-5 animate-spin text-white relative z-10" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="relative z-10 tracking-wide">Masuk ke SIKARA</span>
                    </button>
                </div>
            </form>

            <!-- Footer link -->
            <div class="mt-8 text-center text-sm font-medium text-slate-500">
                Belum punya akun?
                <Link :href="route('register')" class="font-bold text-indigo-600 transition-all hover:text-indigo-500 hover:underline">
                    Daftar sekarang
                </Link>
            </div>
        </div>

        <!-- Maintenance Mode Modal -->
        <Modal :show="showMaintenanceModal" @close="showMaintenanceModal = false" maxWidth="md">
            <div class="relative overflow-hidden bg-white p-8 text-center rounded-[2rem]">
                <!-- Decorative Elements -->
                <div class="absolute -right-24 -top-24 h-48 w-48 rounded-full bg-blue-500 opacity-5 blur-2xl"></div>
                <div class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-emerald-500 opacity-5 blur-2xl"></div>
                
                <div class="relative z-10">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 border-8 border-blue-50/50">
                        <svg class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    
                    <h2 class="mb-3 text-2xl font-extrabold text-slate-900">Sistem Dalam Pemeliharaan</h2>
                    <p class="mb-8 text-[15px] leading-relaxed text-slate-500">
                        Portal SIKARA saat ini sedang dalam pemeliharaan rutin untuk meningkatkan kualitas layanan. Login untuk Peserta dan Perusahaan sementara dinonaktifkan.
                    </p>
                    
                    <button @click="showMaintenanceModal = false" type="button" class="flex h-12 w-full items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-900 transition-colors hover:bg-slate-200">
                        Mengerti, Kembali
                    </button>
                </div>
            </div>
        </Modal>
    </GuestLayout>
</template>
