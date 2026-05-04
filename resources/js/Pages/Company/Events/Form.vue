<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    event: {
        type: Object,
        default: () => ({})
    }
});

const isEditing = computed(() => !!props.event.id);

const form = useForm({
    title: props.event.title || '',
    description: props.event.description || '',
    location: props.event.location || '',
    type: props.event.type || 'offline',
    date: props.event.date ? props.event.date.split('T')[0] : '',
    start_time: props.event.start_time ? props.event.start_time.substring(0, 5) : '',
    end_time: props.event.end_time ? props.event.end_time.substring(0, 5) : '',
    max_participants: props.event.max_participants || 50,
    status: props.event.status || 'draft',
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('perusahaan.events.update', props.event.id));
    } else {
        form.post(route('perusahaan.events.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Event' : 'Tambah Event'" />

    <SikaraLayout 
        :title="isEditing ? 'Edit Event' : 'Tambah Event Baru'" 
        :subtitle="isEditing ? 'Perbarui informasi event' : 'Buat event baru untuk perusahaan Anda'"
    >
        <div class="mb-6">
            <Link :href="route('perusahaan.events.index')" class="inline-flex items-center text-sm font-medium text-[#475467] hover:text-[#101828]">
                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Event
            </Link>
        </div>

        <div class="rounded-[16px] border border-[#eaecf0] bg-white p-8 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Judul Event -->
                    <div class="sm:col-span-2">
                        <label for="title" class="block text-sm font-medium text-[#101828]">Judul Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" id="title" v-model="form.title" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: Workshop Frontend Development">
                        </div>
                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <!-- Tipe Event -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-[#101828]">Tipe Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="type" v-model="form.type" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <!-- Lokasi / Link -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-[#101828]">Lokasi / Link Online <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" id="location" v-model="form.location" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Alamat gedung atau link zoom">
                        </div>
                        <p v-if="form.errors.location" class="mt-2 text-sm text-red-600">{{ form.errors.location }}</p>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="description" class="block text-sm font-medium text-[#101828]">Deskripsi Event <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Jelaskan detail event..."></textarea>
                    </div>
                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Tanggal -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-[#101828]">Tanggal Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="date" id="date" v-model="form.date" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.date" class="mt-2 text-sm text-red-600">{{ form.errors.date }}</p>
                    </div>

                    <!-- Waktu Mulai -->
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-[#101828]">Waktu Mulai <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="time" id="start_time" v-model="form.start_time" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.start_time" class="mt-2 text-sm text-red-600">{{ form.errors.start_time }}</p>
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-[#101828]">Waktu Selesai <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="time" id="end_time" v-model="form.end_time" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.end_time" class="mt-2 text-sm text-red-600">{{ form.errors.end_time }}</p>
                    </div>

                    <!-- Kuota -->
                    <div>
                        <label for="max_participants" class="block text-sm font-medium text-[#101828]">Maksimal Peserta <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="number" id="max_participants" v-model="form.max_participants" min="1" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.max_participants" class="mt-2 text-sm text-red-600">{{ form.errors.max_participants }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-[#101828]">Status Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="status" v-model="form.status" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <p v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</p>
                    </div>
                </div>

                <div class="pt-5 border-t border-[#f2f4f7] flex justify-end gap-3">
                    <Link :href="route('perusahaan.events.index')" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[#344054] shadow-sm ring-1 ring-inset ring-[#d0d5dd] hover:bg-gray-50 transition-colors">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-xl bg-[#10B981] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#059669] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981] transition-colors disabled:opacity-50">
                        {{ isEditing ? 'Simpan Perubahan' : 'Simpan Event' }}
                    </button>
                </div>
            </form>
        </div>
    </SikaraLayout>
</template>
