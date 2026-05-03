<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { computed, ref } from 'vue';
import locationsData from '@/Data/locations.json';

const props = defineProps({
    internship: {
        type: Object,
        default: () => ({})
    }
});

const isEditing = computed(() => !!props.internship.id);

const form = useForm({
    title: props.internship.title || '',
    company_name: props.internship.company_name || '',
    company_logo: props.internship.company_logo || '',
    location: props.internship.location || '',
    work_type: props.internship.work_type || 'Magang',
    duration: props.internship.duration || '',
    salary: props.internship.salary || '',
    description: props.internship.description || '',
    requirements: props.internship.requirements || '',
    benefits: props.internship.benefits || '',
    deadline_at: props.internship.deadline_at ? props.internship.deadline_at.split('T')[0] : '',
    quota: props.internship.quota || 1,
    is_published: props.internship.is_published !== undefined ? props.internship.is_published : true,
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('perusahaan.internships.update', props.internship.id));
    } else {
        form.post(route('perusahaan.internships.store'));
    }
};

const showLocationOptions = ref(false);
const filteredLocations = ref([]);

const filterLocations = () => {
    if (form.location.length >= 3) {
        const query = form.location.toLowerCase();
        filteredLocations.value = locationsData.filter(loc => 
            loc.toLowerCase().includes(query)
        );
        showLocationOptions.value = true;
    } else {
        showLocationOptions.value = false;
        filteredLocations.value = [];
    }
};

const selectLocation = (loc) => {
    form.location = loc;
    showLocationOptions.value = false;
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Lowongan Magang' : 'Tambah Lowongan Magang'" />

    <SikaraLayout 
        :title="isEditing ? 'Edit Lowongan' : 'Tambah Lowongan Baru'" 
        :subtitle="isEditing ? 'Perbarui informasi lowongan magang' : 'Buat lowongan magang baru untuk perusahaan Anda'"
    >
        <div class="mb-6">
            <Link :href="route('perusahaan.internships.index')" class="inline-flex items-center text-sm font-medium text-[#475467] hover:text-[#101828]">
                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Lowongan
            </Link>
        </div>

        <div class="rounded-[16px] border border-[#eaecf0] bg-white p-8 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Posisi -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-[#101828]">Posisi Magang <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" id="title" v-model="form.title" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: Frontend Developer Intern">
                        </div>
                        <p v-if="form.errors.title" class="mt-2 text-sm text-red-600" id="title-error">{{ form.errors.title }}</p>
                    </div>

                    <!-- Perusahaan -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-[#101828]">Nama Perusahaan <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" id="company_name" v-model="form.company_name" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: PT Teknologi Nusantara">
                        </div>
                        <p v-if="form.errors.company_name" class="mt-2 text-sm text-red-600">{{ form.errors.company_name }}</p>
                    </div>
                </div>

                <!-- Logo Perusahaan -->
                <div>
                    <label for="company_logo" class="block text-sm font-medium text-[#101828]">URL Logo Perusahaan</label>
                    <div class="mt-2">
                        <input type="text" id="company_logo" v-model="form.company_logo" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: https://example.com/logo.png">
                    </div>
                    <p v-if="form.errors.company_logo" class="mt-2 text-sm text-red-600">{{ form.errors.company_logo }}</p>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="description" class="block text-sm font-medium text-[#101828]">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Tuliskan deskripsi pekerjaan secara detail..."></textarea>
                    </div>
                    <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Kualifikasi (Skill) -->
                <div>
                    <label for="requirements" class="block text-sm font-medium text-[#101828]">Kualifikasi (Skill) <span class="text-red-500">*</span></label>
                    <div class="mt-2">
                        <textarea id="requirements" v-model="form.requirements" rows="4" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Tuliskan kualifikasi atau skill yang dibutuhkan..."></textarea>
                    </div>
                    <p v-if="form.errors.requirements" class="mt-2 text-sm text-red-600">{{ form.errors.requirements }}</p>
                </div>

                <!-- Benefit -->
                <div>
                    <label for="benefits" class="block text-sm font-medium text-[#101828]">Benefit</label>
                    <div class="mt-2">
                        <textarea id="benefits" v-model="form.benefits" rows="3" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Tuliskan benefit yang didapatkan (opsional)..."></textarea>
                    </div>
                    <p v-if="form.errors.benefits" class="mt-2 text-sm text-red-600">{{ form.errors.benefits }}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Tipe Pekerjaan -->
                    <div>
                        <label for="work_type" class="block text-sm font-medium text-[#101828]">Tipe Pekerjaan <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="work_type" v-model="form.work_type" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                                <option value="Magang">Magang</option>
                                <option value="Magang WFO">Magang WFO</option>
                                <option value="Magang WFH">Magang WFH</option>
                                <option value="Magang Hybrid">Magang Hybrid</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                        </div>
                        <p v-if="form.errors.work_type" class="mt-2 text-sm text-red-600">{{ form.errors.work_type }}</p>
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label for="duration" class="block text-sm font-medium text-[#101828]">Durasi</label>
                        <div class="mt-2">
                            <input type="text" id="duration" v-model="form.duration" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: 3 Bulan">
                        </div>
                        <p v-if="form.errors.duration" class="mt-2 text-sm text-red-600">{{ form.errors.duration }}</p>
                    </div>

                    <!-- Gaji/Uang Saku -->
                    <div>
                        <label for="salary" class="block text-sm font-medium text-[#101828]">Gaji/Uang Saku</label>
                        <div class="mt-2">
                            <input type="text" id="salary" v-model="form.salary" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" placeholder="Misal: Rp 1.500.000 / Unpaid">
                        </div>
                        <p v-if="form.errors.salary" class="mt-2 text-sm text-red-600">{{ form.errors.salary }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <!-- Lokasi -->
                    <div class="relative">
                        <label for="location" class="block text-sm font-medium text-[#101828]">Lokasi <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input 
                                type="text" 
                                id="location" 
                                v-model="form.location" 
                                @input="filterLocations"
                                @focus="filterLocations"
                                @blur="setTimeout(() => showLocationOptions = false, 200)"
                                class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] placeholder:text-[#98a2b3] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6" 
                                placeholder="Misal: Jakarta Selatan / WFA"
                                autocomplete="off"
                            >
                        </div>

                        <!-- Dropdown Opsi Lokasi -->
                        <ul v-if="showLocationOptions && filteredLocations.length > 0" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                            <li 
                                v-for="(loc, index) in filteredLocations" 
                                :key="index"
                                @mousedown.prevent="selectLocation(loc)"
                                class="relative cursor-pointer select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-[#10B981] hover:text-white"
                            >
                                <span class="block truncate">{{ loc }}</span>
                            </li>
                        </ul>

                        <p v-if="form.errors.location" class="mt-2 text-sm text-red-600">{{ form.errors.location }}</p>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label for="deadline_at" class="block text-sm font-medium text-[#101828]">Batas Waktu Lamaran <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="date" id="deadline_at" v-model="form.deadline_at" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.deadline_at" class="mt-2 text-sm text-red-600">{{ form.errors.deadline_at }}</p>
                    </div>

                    <!-- Kuota -->
                    <div>
                        <label for="quota" class="block text-sm font-medium text-[#101828]">Kuota <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="number" id="quota" v-model="form.quota" min="1" class="block w-full rounded-lg border-0 py-2.5 px-3 text-[#101828] shadow-sm ring-1 ring-inset ring-[#d0d5dd] focus:ring-2 focus:ring-inset focus:ring-[#10B981] sm:text-sm sm:leading-6">
                        </div>
                        <p v-if="form.errors.quota" class="mt-2 text-sm text-red-600">{{ form.errors.quota }}</p>
                    </div>
                </div>

                <div v-if="isEditing" class="mt-4 flex items-center gap-2">
                    <input type="checkbox" id="is_published" v-model="form.is_published" class="h-4 w-4 rounded border-gray-300 text-[#10B981] focus:ring-[#10B981]">
                    <label for="is_published" class="text-sm font-medium text-[#101828]">Status Publikasi (Ceklis untuk Aktif)</label>
                </div>

                <div class="pt-5 border-t border-[#f2f4f7] flex justify-end gap-3">
                    <Link :href="route('perusahaan.internships.index')" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[#344054] shadow-sm ring-1 ring-inset ring-[#d0d5dd] hover:bg-gray-50 transition-colors">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-xl bg-[#10B981] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#059669] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981] transition-colors disabled:opacity-50">
                        {{ isEditing ? 'Simpan Perubahan' : 'Simpan Lowongan' }}
                    </button>
                </div>
            </form>
        </div>
    </SikaraLayout>
</template>
