<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    settings: Object,
});

// Flatten settings to submit as a single array
const flattenedSettings = computed(() => {
    return Object.values(props.settings).flat();
});

const form = useForm({
    settings: flattenedSettings.value.map(s => ({ ...s })) // clone to avoid direct mutation issues
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};

// Group settings back for display purposes
const groupedSettings = computed(() => {
    const groups = {};
    form.settings.forEach(setting => {
        if (!groups[setting.group]) {
            groups[setting.group] = [];
        }
        groups[setting.group].push(setting);
    });
    return groups;
});

const groupTitles = {
    general: 'Pengaturan Umum',
    system: 'Konfigurasi Sistem',
};
const groupDescriptions = {
    general: 'Atur informasi dasar tentang platform seperti nama, deskripsi, dan kontak.',
    system: 'Konfigurasikan parameter teknis yang mempengaruhi operasional sistem.',
};
</script>

<template>
    <AdminLayout title="Pengaturan Sistem">
        <Head title="Pengaturan Sistem" />

        <div class="mx-auto max-w-5xl space-y-8">
            <div class="flex items-end justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Pengaturan Sistem Dinamis</h1>
                    <p class="mt-1 text-sm text-[#64748B]">
                        Konfigurasikan parameter sistem secara global tanpa perlu melakukan perubahan kode program.
                    </p>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition-all hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>

            <!-- Flash Message -->
            <div v-if="$page.props.flash.success" class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 shadow-sm">
                <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $page.props.flash.success }}
            </div>

            <!-- Form Categories -->
            <form @submit.prevent="submit" class="space-y-6">
                <div 
                    v-for="(settings, groupKey) in groupedSettings" 
                    :key="groupKey"
                    class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm transition-all hover:shadow-md"
                >
                    <div class="border-b border-[#F1F5F9] bg-[#F8FAFC]/50 px-6 py-4">
                        <h2 class="text-base font-semibold text-[#0F172A]">{{ groupTitles[groupKey] || groupKey }}</h2>
                        <p class="text-xs text-[#64748B]">{{ groupDescriptions[groupKey] || 'Pengaturan terkait kelompok ini.' }}</p>
                    </div>
                    
                    <div class="divide-y divide-[#F1F5F9] px-6">
                        <div 
                            v-for="setting in settings" 
                            :key="setting.id"
                            class="grid grid-cols-1 gap-4 py-5 md:grid-cols-3"
                        >
                            <div class="md:col-span-1">
                                <label :for="'setting_' + setting.id" class="block text-sm font-medium text-[#0F172A]">
                                    {{ setting.label }}
                                </label>
                                <span class="block text-[11px] text-[#94A3B8] font-mono mt-1">Key: {{ setting.key }}</span>
                            </div>
                            
                            <div class="md:col-span-2 flex items-center">
                                <!-- Boolean Toggle -->
                                <template v-if="setting.type === 'boolean'">
                                    <button 
                                        type="button" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
                                        :class="setting.value === 'true' ? 'bg-[#10B981]' : 'bg-[#CBD5E1]'"
                                        role="switch" 
                                        :aria-checked="setting.value === 'true'"
                                        @click="setting.value = setting.value === 'true' ? 'false' : 'true'"
                                    >
                                        <span class="sr-only">Use setting</span>
                                        <span 
                                            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            :class="setting.value === 'true' ? 'translate-x-5' : 'translate-x-0'"
                                        >
                                            <span 
                                                class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity duration-200 ease-in"
                                                :class="setting.value === 'true' ? 'opacity-0 ease-out duration-100' : 'opacity-100 ease-in duration-200'"
                                            >
                                                <svg class="h-3 w-3 text-[#94A3B8]" fill="none" viewBox="0 0 12 12">
                                                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                            <span 
                                                class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity duration-200 ease-out"
                                                :class="setting.value === 'true' ? 'opacity-100 ease-in duration-200' : 'opacity-0 ease-out duration-100'"
                                            >
                                                <svg class="h-3 w-3 text-[#10B981]" fill="currentColor" viewBox="0 0 12 12">
                                                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                                                </svg>
                                            </span>
                                        </span>
                                    </button>
                                    <span class="ml-3 text-sm font-medium" :class="setting.value === 'true' ? 'text-[#10B981]' : 'text-[#64748B]'">
                                        {{ setting.value === 'true' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </template>
                                
                                <!-- String Input -->
                                <template v-else-if="setting.type === 'string'">
                                    <input 
                                        :id="'setting_' + setting.id"
                                        type="text" 
                                        v-model="setting.value"
                                        class="block w-full rounded-xl border-[#E2E8F0] px-4 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] transition-colors"
                                        :placeholder="'Masukkan ' + setting.label"
                                    />
                                </template>

                                <!-- Integer Input -->
                                <template v-else-if="setting.type === 'integer'">
                                    <input 
                                        :id="'setting_' + setting.id"
                                        type="number" 
                                        v-model="setting.value"
                                        class="block w-full max-w-[200px] rounded-xl border-[#E2E8F0] px-4 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] transition-colors"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
