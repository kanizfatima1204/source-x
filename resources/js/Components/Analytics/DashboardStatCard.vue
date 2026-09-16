<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
    >
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ title }}
        </p>

        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ value }}
        </p>

        <p
            v-if="subtitle"
            class="mt-1.5 text-xs font-medium text-slate-500 dark:text-slate-400"
        >
            {{ subtitle }}
        </p>
    </div>
</template>
