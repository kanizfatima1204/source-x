<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true,
    },

    label: {
        type: String,
        default: '',
    },

    size: {
        type: String,
        default: 'md',
    },
})

const normalizedStatus = computed(() => {
    return String(props.status ?? '').toLowerCase()
})

const displayLabel = computed(() => {
    if (props.label) {
        return props.label
    }

    return String(props.status ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase())
})

const statusClass = computed(() => {
    const classes = {
        active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        verified: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        matched: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',

        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        needs_review: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        reviewed: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',

        submitted: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        recommended: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',

        inactive: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        draft: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',

        rejected: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
        cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
    }

    return classes[normalizedStatus]
        ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
})

const sizeClass = computed(() => {
    return props.size === 'sm'
        ? 'px-2 py-0.5 text-[10px]'
        : 'px-2.5 py-1 text-xs'
})
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full font-semibold"
        :class="[statusClass, sizeClass]"
    >
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70" />

        {{ displayLabel }}
    </span>
</template>
