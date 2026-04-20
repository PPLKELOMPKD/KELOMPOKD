<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    internships: Array,
});

const apply = (id) => {
    useForm({ internship_id: id }).post(route('internships.apply'));
};
</script>

<template>
    <Head title="Lowongan Magang" />

    <SikaraLayout title="Lowongan Magang" subtitle="Jelajahi peluang magang aktif dan kirim lamaran langsung dari SIKARA.">
        <div class="grid gap-5">
            <article
                v-for="internship in internships"
                :key="internship.id"
                class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]"
            >
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-[#98a2b3]">{{ internship.company_name }}</p>
                        <h3 class="mt-3 text-[28px] font-semibold tracking-[-0.03em] text-[#101828]">{{ internship.title }}</h3>
                        <div class="mt-3 flex flex-wrap gap-2 text-sm text-[#667085]">
                            <span class="rounded-full bg-[#f2f4f7] px-3 py-1">{{ internship.location }}</span>
                            <span class="rounded-full bg-[#f2f4f7] px-3 py-1">Deadline {{ internship.deadline_at }}</span>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-[#475467]">{{ internship.requirements }}</p>
                    </div>

                    <div class="flex flex-col gap-3 lg:w-44">
                        <button
                            class="flex h-11 items-center justify-center rounded-xl bg-black px-4 text-sm font-medium text-white"
                            @click="apply(internship.id)"
                        >
                            Apply Now
                        </button>
                        <button
                            type="button"
                            class="flex h-11 items-center justify-center rounded-xl border border-[#d0d5dd] bg-white px-4 text-sm font-medium text-[#344054]"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </article>
        </div>
    </SikaraLayout>
</template>
