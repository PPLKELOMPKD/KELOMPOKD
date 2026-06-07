<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    event: {
        type: Object,
        default: () => ({})
    }
});

const isEditing = computed(() => !!props.event.id);

const form = useForm({
    title: props.event.title || '',
    category: props.event.category || '',
    description: props.event.description || '',
    location: props.event.location || '',
    type: props.event.type || 'offline',
    date: props.event.date ? props.event.date.split('T')[0] : '',
    start_time: props.event.start_time ? props.event.start_time.substring(0, 5) : '',
    end_time: props.event.end_time ? props.event.end_time.substring(0, 5) : '',
    max_participants: props.event.max_participants || 50,
});

// ─── Local client-side errors ───────────────────────────────────────────────
const localErrors = ref({
    date: '',
    start_time: '',
    end_time: '',
});

/** Returns today's date string in YYYY-MM-DD (local timezone) */
const todayStr = () => {
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm   = String(now.getMonth() + 1).padStart(2, '0');
    const dd   = String(now.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};

/** Returns current time as "HH:MM" */
const nowTimeStr = () => {
    const now = new Date();
    return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
};

/** True when the selected date is today */
const isToday = computed(() => form.date === todayStr());

// ─── Validators ─────────────────────────────────────────────────────────────
const validateDate = () => {
    if (!form.date) {
        localErrors.value.date = '';
        return true;
    }
    if (form.date < todayStr()) {
        localErrors.value.date = 'Tanggal event tidak boleh di masa lalu. Pilih tanggal hari ini atau yang akan datang.';
        return false;
    }
    localErrors.value.date = '';
    return true;
};

const validateStartTime = () => {
    if (!form.start_time) {
        localErrors.value.start_time = '';
        return true;
    }
    // If date is today, start_time must be in the future
    if (isToday.value && form.start_time <= nowTimeStr()) {
        localErrors.value.start_time = 'Waktu mulai tidak boleh di waktu yang sudah lewat. Pilih waktu yang akan datang.';
        return false;
    }
    localErrors.value.start_time = '';
    return true;
};

const validateEndTime = () => {
    if (!form.end_time) {
        localErrors.value.end_time = '';
        return true;
    }
    // end_time must be after start_time
    if (form.start_time && form.end_time <= form.start_time) {
        localErrors.value.end_time = 'Waktu selesai harus setelah waktu mulai.';
        return false;
    }
    // If date is today, end_time must also be in the future
    if (isToday.value && form.end_time <= nowTimeStr()) {
        localErrors.value.end_time = 'Waktu selesai tidak boleh di waktu yang sudah lewat.';
        return false;
    }
    localErrors.value.end_time = '';
    return true;
};

// ─── Watchers: re-validate on change ────────────────────────────────────────
watch(() => form.date, () => {
    validateDate();
    // Re-validate times when date changes (today vs future)
    if (form.start_time) validateStartTime();
    if (form.end_time)   validateEndTime();
});
watch(() => form.start_time, () => {
    validateStartTime();
    if (form.end_time) validateEndTime();
});
watch(() => form.end_time, () => validateEndTime());

// ─── Submit ──────────────────────────────────────────────────────────────────
const submit = () => {
    const dateOk  = validateDate();
    const startOk = validateStartTime();
    const endOk   = validateEndTime();

    if (!dateOk || !startOk || !endOk) {
        // Scroll to first error
        const firstError = document.querySelector('.field-error');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (isEditing.value) {
        form.put(route('perusahaan.events.update', props.event.id));
    } else {
        form.post(route('perusahaan.events.store'));
    }
};

/** Min attribute for the date input (today) */
const minDate = todayStr();
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
                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.title }}</p>
                    </div>

                    <!-- Kategori Event -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-[#101828]">Kategori Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="category" v-model="form.category" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                                <option value="" disabled>Pilih Kategori</option>
                                <option value="webinar">Webinar</option>
                                <option value="workshop">Workshop</option>
                                <option value="seminar">Seminar</option>
                            </select>
                        </div>
                        <p v-if="form.errors.category" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.category }}</p>
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
                        <p v-if="form.errors.type" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.type }}</p>
                    </div>

                    <!-- Lokasi / Link -->
                    <div class="sm:col-span-2">
                        <label for="location" class="block text-sm font-medium text-[#101828]">Lokasi / Link Online <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" id="location" v-model="form.location" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Alamat gedung atau link zoom">
                        </div>
                        <p v-if="form.errors.location" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.location }}</p>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="description" class="block text-sm font-medium text-[#101828]">Deskripsi Event <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Jelaskan detail event..."></textarea>
                    </div>
                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Tanggal -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-[#101828]">Tanggal Event <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input
                                type="date"
                                id="date"
                                v-model="form.date"
                                :min="minDate"
                                @change="validateDate"
                                :class="[
                                    'block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                                    (localErrors.date || form.errors.date) ? 'ring-red-400 focus:ring-red-500' : 'ring-[#d0d5dd] focus:ring-[#10B981]'
                                ]"
                            >
                        </div>
                        <p v-if="localErrors.date" class="mt-2 text-sm text-red-600 field-error flex items-start gap-1">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                            {{ localErrors.date }}
                        </p>
                        <p v-else-if="form.errors.date" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.date }}</p>
                    </div>

                    <!-- Waktu Mulai -->
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-[#101828]">Waktu Mulai <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input
                                type="time"
                                id="start_time"
                                v-model="form.start_time"
                                @change="validateStartTime"
                                :class="[
                                    'block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                                    (localErrors.start_time || form.errors.start_time) ? 'ring-red-400 focus:ring-red-500' : 'ring-[#d0d5dd] focus:ring-[#10B981]'
                                ]"
                            >
                        </div>
                        <p v-if="localErrors.start_time" class="mt-2 text-sm text-red-600 field-error flex items-start gap-1">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                            {{ localErrors.start_time }}
                        </p>
                        <p v-else-if="form.errors.start_time" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.start_time }}</p>
                        <!-- Info hint when date is today -->
                        <p v-if="isToday && !localErrors.start_time && !form.errors.start_time" class="mt-1 text-xs text-amber-600 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                            Event hari ini: pilih waktu yang belum lewat
                        </p>
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-[#101828]">Waktu Selesai <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input
                                type="time"
                                id="end_time"
                                v-model="form.end_time"
                                @change="validateEndTime"
                                :class="[
                                    'block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
                                    (localErrors.end_time || form.errors.end_time) ? 'ring-red-400 focus:ring-red-500' : 'ring-[#d0d5dd] focus:ring-[#10B981]'
                                ]"
                            >
                        </div>
                        <p v-if="localErrors.end_time" class="mt-2 text-sm text-red-600 field-error flex items-start gap-1">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                            {{ localErrors.end_time }}
                        </p>
                        <p v-else-if="form.errors.end_time" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.end_time }}</p>
                    </div>

                    <!-- Kuota -->
                    <div>
                        <label for="max_participants" class="block text-sm font-medium text-[#101828]">Maksimal Peserta <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="number" id="max_participants" v-model="form.max_participants" min="1" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.max_participants" class="mt-2 text-sm text-red-600 field-error">{{ form.errors.max_participants }}</p>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <p class="text-sm text-blue-700">
                        <strong>Catatan:</strong> Setelah Anda menyimpan event, data akan masuk ke tahap moderasi oleh tim kami sebelum dapat ditampilkan secara publik.
                    </p>
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
