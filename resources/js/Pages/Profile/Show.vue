<script setup>
defineProps({
    profile: {
        type: Object,
        default: null,
    },
    skills: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <main class="mx-auto max-w-3xl p-6">
        <h1 class="text-2xl font-semibold text-slate-900">
            Profile placeholder
        </h1>
        <p class="mt-2 text-sm text-slate-600">
            Temporary Task 1 Inertia page. Real profile UI will land in a later task.
        </p>
    </main>
</template>
