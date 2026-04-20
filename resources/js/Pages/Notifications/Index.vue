<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    items: Array,
});
</script>

<template>
    <Head title="Notifikasi" />

    <SikaraLayout title="Notifikasi" subtitle="Pantau update terbaru terkait lamaran dan aktivitas akun Anda.">
        <div class="space-y-4">
            <article
                v-for="item in items"
                :key="item.id"
                class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]"
            >
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-[#98a2b3]">{{ item.type }}</p>
                        <h3 class="mt-3 text-2xl font-semibold tracking-[-0.02em] text-[#101828]">{{ item.title }}</h3>
                        <p class="mt-3 text-sm leading-7 text-[#475467]">{{ item.message }}</p>
                        <p class="mt-4 text-xs font-medium uppercase tracking-[0.14em] text-[#98a2b3]">
                            {{ item.read_at ? 'Sudah dibaca' : 'Belum dibaca' }}
                        </p>
                    </div>

                    <Link
                        v-if="!item.read_at"
                        :href="route('notifications.read', item.id)"
                        method="post"
                        as="button"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-black px-4 text-sm font-medium text-white"
                    >
                        Tandai Dibaca
                    </Link>
                    <span v-else class="inline-flex h-11 items-center rounded-xl bg-[#f2f4f7] px-4 text-sm font-medium text-[#344054]">
                        Sudah dibaca
                    </span>
                </div>
            </article>
        </div>
    </SikaraLayout>
</template>
