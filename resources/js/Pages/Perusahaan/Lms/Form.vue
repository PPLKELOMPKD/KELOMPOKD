<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    course: Object,
});

const form = useForm({
    title: props.course?.title || '',
    provider: props.course?.provider || '',
    description: props.course?.description || '',
    level: props.course?.level || 'BEGINNER',
    image: null,
    location: props.course?.location || '',
    quota: props.course?.quota || '',
    started_at: props.course?.started_at || '',
    ends_at: props.course?.ends_at || '',
    start_time: props.course?.start_time || '',
    passing_grade: props.course?.passing_grade || 70,
});

const submit = () => {
    if (props.course) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('perusahaan.lms.update', props.course.slug));
    } else {
        form.post(route('perusahaan.lms.store'));
    }
};
</script>

<template>
    <SikaraLayout :title="course ? 'Edit Modul' : 'Buat Modul'" subtitle="Lengkapi informasi dasar modul pembelajaran.">
        <div class="rounded-[20px] bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Judul</label>
                    <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Penyelenggara</label>
                    <input v-model="form.provider" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Level</label>
                    <select v-model="form.level" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                        <option value="BEGINNER">BEGINNER</option>
                        <option value="INTERMEDIATE">INTERMEDIATE</option>
                        <option value="ADVANCED">ADVANCED</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Gambar Banner (Opsional)</label>
                    <input @input="form.image = $event.target.files[0]" type="file" accept="image/*" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-[#EFF6FF] file:text-[#2563EB] hover:file:bg-[#DBEAFE]" />
                    <div v-if="form.progress" class="mt-2 text-sm text-[#2563EB]">
                        Uploading: {{ form.progress.percentage }}%
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Lokasi</label>
                        <input v-model="form.location" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" placeholder="Contoh: Jakarta atau Online" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Kuota Peserta</label>
                        <input v-model="form.quota" type="number" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">KKM (Nilai Kelulusan)</label>
                        <input v-model="form.passing_grade" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" required />
                        <p class="text-[10px] text-slate-500 mt-1 italic">*Nilai rata-rata minimal untuk lulus</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input v-model="form.started_at" type="date" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                        <input v-model="form.ends_at" type="date" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Waktu Mulai</label>
                        <input v-model="form.start_time" type="time" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Alt Gambar</label>
                    <input v-model="form.image_alt" type="text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" />
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]" :disabled="form.processing">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </SikaraLayout>
</template>
