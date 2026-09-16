<script setup>
import { computed } from 'vue'

const props = defineProps({
    value: {
        type: Number,
        default: 0,
    },

    label: {
        type: String,
        default: '',
    },

    showValue: {
        type: Boolean,
        default: true,
    },

    variant: {
        type: String,
        default: 'violet',
    },

    size: {
        type: String,
        default: 'md',
    },
})

const percentage = computed(() => {
    return Math.max(0, Math.min(100, Number(props.value) || 0))
})

const barClass = computed(() => {
    const classes = {
        violet: 'bg-violet-500',
        blue: 'bg-blue-500',
        emerald: 'bg-emerald-500',
        amber: 'bg-amber-500',
        rose: 'bg-rose-500',
        slate: 'bg-slate-700 dark:bg-slate-300',
    }

    return classes[props.variant] ?? classes.violet
})

const heightClass = computed(() => {
    return props.size === 'sm'
        ? 'h-1.5'
        : props.size === 'lg'
            ? 'h-3'
            : 'h-2'
})
</script>

<template>
    <div>
        <div
            v-if="label || showValue"
            class="mb-2 flex items-center justify-between gap-3"
        >
            <span
                v-if="label"
                class="text-xs font-medium text-slate-600 dark:text-slate-300"
            >
                {{ label }}
            </span>

            <span
                v-if="showValue"
                class="text-xs font-semibold text-slate-700 dark:text-slate-200"
            >
                {{ percentage }}%
            </span>
        </div>

        <div
            class="overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
            :class="heightClass"
        >
            <div
                class="h-full rounded-full transition-all duration-500"
                :class="barClass"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>
