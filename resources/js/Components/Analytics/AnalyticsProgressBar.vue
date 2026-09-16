<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        default: 0,
    },
    max: {
        type: [Number, String],
        default: 100,
    },
})

const numericValue = computed(() => Number(props.value || 0))
const numericMax = computed(() => Math.max(1, Number(props.max || 1)))

const percentage = computed(() => {
    return Math.min(100, Math.max(0, Math.round((numericValue.value / numericMax.value) * 100)))
})
</script>

<template>
    <div>
        <div class="flex items-center justify-between text-xs font-medium">
            <span class="text-slate-600 dark:text-slate-300">{{ label }}</span>
            <span class="text-slate-900 dark:text-white font-semibold">
                {{ value }} <span class="text-slate-400">({{ percentage }}%)</span>
            </span>
        </div>
        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div
                class="h-full rounded-full bg-indigo-600 transition-all duration-300 dark:bg-indigo-500"
                :style="{ width: `${percentage}%` }"
            />
        </div>
    </div>
</template>
