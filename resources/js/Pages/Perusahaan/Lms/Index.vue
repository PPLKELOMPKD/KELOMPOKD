<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Link, router } from "@inertiajs/vue3";

defineProps({
    courses: Array,
});

const publish = (course) => {
    router.post(route('perusahaan.lms.publish', course.slug), {}, { preserveScroll: true });
};

const unpublish = (course) => {
    router.post(route('perusahaan.lms.unpublish', course.slug), {}, { preserveScroll: true });
};
</script>

<template>
    <SikaraLayout title="Manajemen LMS" subtitle="Kelola modul pembelajaran untuk peserta.">
        <template #headerAction>
            <Link :href="route('perusahaan.lms.create')" class="rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                Buat Modul
            </Link>
        </template>

        <div class="rounded-[20px] bg-white shadow-sm ring-1 ring-slate-200">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-900">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Judul</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Bab</th>
                        <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr v-for="course in courses" :key="course.id" class="hover:bg-slate-50">
                        <td class="px-6 py-4">{{ course.title }}</td>
                        <td class="px-6 py-4">
                            <span :class="course.status === 'published' ? 'text-green-600 bg-green-50' : 'text-slate-600 bg-slate-100'" class="px-2 py-1 rounded-full text-xs font-semibold">
                                {{ course.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ course.chapters_count }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <Link :href="route('perusahaan.lms.edit', course.slug)" class="text-[#2563EB] hover:underline">Edit</Link>
                            <Link :href="route('perusahaan.lms.builder', course.slug)" class="text-[#2563EB] hover:underline">Builder</Link>
                            <button v-if="course.status === 'draft'" @click="publish(course)" class="text-green-600 hover:underline">Publish</button>
                            <button v-else @click="unpublish(course)" class="text-amber-600 hover:underline">Unpublish</button>
                        </td>
                    </tr>
                    <tr v-if="courses.length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada modul LMS.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SikaraLayout>
</template>
