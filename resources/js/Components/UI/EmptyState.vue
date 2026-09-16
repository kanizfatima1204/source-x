<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    title: {
        type: String,
        required: true,
    },

    description: {
        type: String,
        default: '',
    },

    icon: {
        type: String,
        default: '○',
    },

    actionLabel: {
        type: String,
        default: '',
    },

    actionHref: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-500 dark:bg-slate-800 dark:text-slate-400">
            {{ icon }}
        </div>

        <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
            {{ title }}
        </h3>

        <p
            v-if="description"
            class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
        >
            {{ description }}
        </p>

        <Link
            v-if="actionLabel && actionHref"
            :href="actionHref"
            class="mt-5 inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
        >
            {{ actionLabel }}
        </Link>

        <div v-if="$slots.default" class="mt-5">
            <slot />
        </div>
    </div>
</template>
