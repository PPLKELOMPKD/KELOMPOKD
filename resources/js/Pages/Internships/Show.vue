<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    internship: Array,
    hasApplied: Boolean,
});

const form = useForm({
    internship_id: props.internship.id,
});

const apply = () => {
    form.post(route("internships.apply"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Detail - ${internship.title}`" />

    <SikaraLayout
        title="Detail Lowongan"
        subtitle="Pelajari kualifikasi posisi ini sebelum mengirimkan lamaran Anda."
    >
        <div class="max-w-4xl bg-white rounded-[16px] border border-[#eaecf0] shadow-sm p-8">
            <!-- Tombol Kembali -->
            <Link 
                :href="route('internships.index')" 
                class="inline-flex items-center text-sm font-medium text-[#667085] hover:text-[#101828] mb-6 transition-colors"
            >
                &larr; Kembali ke Daftar Lowongan
            </Link>

            <!-- Header Detail -->
            <div class="border-b border-[#eaecf0] pb-6 mb-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-[#98a2b3]">
                    {{ internship.company_name }}
                </p>
                <h1 class="mt-2 text-3xl font-bold text-[#101828]">
                    {{ internship.title }}
                </h1>
                <div class="mt-4 flex flex-wrap gap-3 text-sm text-[#667085]">
                    <span class="rounded-full bg-[#f2f4f7] px-3 py-1 font-medium">
                        {{ internship.location }}
                    </span>
                    <span class="rounded-full bg-red-50 text-red-600 px-3 py-1 font-medium">
                        Ditutup: {{ new Date(internship.deadline_at).toLocaleDateString("id-ID", { day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </span>
                </div>
            </div>

            <!-- Flash Message (Jika ada) -->
            <div v-if="$page.props.flash.success" class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200">
                {{ $page.props.flash.error }}
            </div>

            <!-- Deskripsi Lengkap -->
            <div class="prose max-w-none text-[#475467] leading-relaxed mb-10 whitespace-pre-wrap">
                <h3 class="text-lg font-bold text-[#101828] mb-3">Deskripsi & Kualifikasi</h3>
                {{ internship.requirements }}
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 border-t border-[#eaecf0] pt-6">
                <button
                    :disabled="props.hasApplied || form.processing"
                    :class="[
                        'flex h-12 items-center justify-center rounded-xl px-8 text-base font-semibold text-white transition-colors',
                        props.hasApplied
                            ? 'bg-gray-400 cursor-not-allowed'
                            : 'bg-[#101828] hover:bg-gray-800',
                    ]"
                    @click="apply"
                >
                    <span v-if="form.processing">Memproses...</span>
                    <span v-else>{{ props.hasApplied ? "Sudah Dilamar" : "Lamar Posisi Ini" }}</span>
                </button>
            </div>
        </div>
    </SikaraLayout>
</template>