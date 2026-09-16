<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: String,
        required: true,
    },

    value: {
        type: [String, Number],
        required: true,
    },

    description: {
        type: String,
        default: '',
    },

    icon: {
        type: String,
        default: '•',
    },

    variant: {
        type: String,
        default: 'default',
    },

    href: {
        type: String,
        default: null,
    },
})

const variants = {
    default: {
        icon: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        accent: 'bg-slate-900 dark:bg-white',
    },

    blue: {
        icon: 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
        accent: 'bg-blue-500',
    },

    violet: {
        icon: 'bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        accent: 'bg-violet-500',
    },

    emerald: {
        icon: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        accent: 'bg-emerald-500',
    },

    amber: {
        icon: 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        accent: 'bg-amber-500',
    },

    rose: {
        icon: 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        accent: 'bg-rose-500',
    },
}

const currentVariant = computed(() => {
    return variants[props.variant] ?? variants.default
})
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href"
        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
        :class="href ? 'cursor-pointer' : ''"
    >
        <div
            class="absolute inset-y-0 left-0 w-1"
            :class="currentVariant.accent"
        />

        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                    {{ label }}
                </p>

                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ value }}
                </p>

                <p
                    v-if="description"
                    class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                >
                    {{ description }}
                </p>
            </div>

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg transition group-hover:scale-105"
                :class="currentVariant.icon"
            >
                {{ icon }}
            </div>
        </div>

        <div
            v-if="$slots.footer"
            class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800"
        >
            <slot name="footer" />
        </div>
    </component>
</template>
