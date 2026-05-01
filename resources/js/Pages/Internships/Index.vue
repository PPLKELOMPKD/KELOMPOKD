<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    internships: Array,
    applieds_id: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    internship_id: null,
});

const apply = (id) => {
    form.internship_id = id;
    form.post(route("internships.apply"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Lowongan Magang" />

    <SikaraLayout
        title="Lowongan Magang"
        subtitle="Jelajahi peluang magang aktif dan kirim lamaran langsung dari SIKARA."
    >
        <div class="grid gap-5">
            <article
                v-for="internship in internships"
                :key="internship.id"
                class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]"
            >
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div class="max-w-3xl">
                        <p
                            class="text-xs font-medium uppercase tracking-[0.18em] text-[#98a2b3]"
                        >
                            {{ internship.company_name }}
                        </p>
                        <h3
                            class="mt-3 text-[28px] font-semibold tracking-[-0.03em] text-[#101828] hover:text-blue-600 transition-colors cursor-pointer"
                        >
                            {{ internship.title }}
                        </h3>
                        <div
                            class="mt-3 flex flex-wrap gap-2 text-sm text-[#667085]"
                        >
                            <span class="rounded-full bg-[#f2f4f7] px-3 py-1">{{
                                internship.location
                            }}</span>
                            <span class="rounded-full bg-[#f2f4f7] px-3 py-1"
                                >Deadline
                                {{
                                    new Date(
                                        internship.deadline_at,
                                    ).toLocaleDateString("id-ID", {
                                        day: "numeric",
                                        month: "long",
                                        year: "numeric",
                                    })
                                }}</span
                            >
                        </div>
                        <p
                            class="mt-4 text-sm leading-7 text-[#475467] line-clamp-3"
                        >
                            {{ internship.requirements }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 lg:w-44">
                        <button
                            :disabled="
                                props.applieds_id.some(
                                    (id) => id == internship.id,
                                ) || form.processing
                            "
                            :class="[
                                'flex h-11 items-center justify-center rounded-xl px-4 text-sm font-medium text-white transition-colors',
                                props.applieds_id.some(
                                    (id) => id == internship.id,
                                )
                                    ? 'bg-gray-400 cursor-not-allowed'
                                    : 'bg-black hover:bg-gray-800',
                            ]"
                            @click="apply(internship.id)"
                        >
                            <span
                                v-if="
                                    form.processing &&
                                    form.internship_id === internship.id
                                "
                                >Memproses...</span
                            >
                            <span v-else>{{
                                props.applieds_id.some(
                                    (id) => id == internship.id,
                                )
                                    ? "Sudah Dilamar"
                                    : "Lamar Sekarang"
                            }}</span>
                        </button>

                        <Link
                            :href="route('internships.show', internship.id)"
                            class="flex h-11 items-center justify-center rounded-xl border border-[#d0d5dd] bg-white px-4 text-sm font-medium text-[#344054] hover:bg-gray-50 transition-colors cursor-pointer"
                        >
                            Detail Lowongan
                        </Link>
                    </div>
                </div>
            </article>
        </div>
    </SikaraLayout>
</template>
