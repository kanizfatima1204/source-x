<script setup>
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Health Score',
    },

    score: {
        type: Number,
        default: 0,
    },

    subtitle: {
        type: String,
        default: '',
    },
})

const normalizedScore = computed(() =>
    Math.max(0, Math.min(100, Number(props.score || 0)))
)

const label = computed(() => {
    if (normalizedScore.value >= 80) {
        return 'Healthy'
    }

    if (normalizedScore.value >= 60) {
        return 'Attention'
    }

    return 'Critical'
})

const circumference = 2 * Math.PI * 42

const dashOffset = computed(() =>
    circumference -
    (normalizedScore.value / 100) * circumference
)

const scoreClass = computed(() => {
    if (normalizedScore.value >= 80) {
        return 'text-emerald-500'
    }

    if (normalizedScore.value >= 60) {
        return 'text-amber-500'
    }

    return 'text-rose-500'
})
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div class="flex items-center justify-between gap-4">
            <div>
                <p
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    {{ title }}
                </p>

                <p
                    v-if="subtitle"
                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                >
                    {{ subtitle }}
                </p>
            </div>

            <div class="relative h-24 w-24 shrink-0">
                <svg
                    class="h-24 w-24 -rotate-90"
                    viewBox="0 0 100 100"
                >
                    <circle
                        cx="50"
                        cy="50"
                        r="42"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="9"
                        class="text-slate-100 dark:text-slate-800"
                    />

                    <circle
                        cx="50"
                        cy="50"
                        r="42"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="9"
                        stroke-linecap="round"
                        :class="scoreClass"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="dashOffset"
                    />
                </svg>

                <div
                    class="absolute inset-0 flex flex-col items-center justify-center"
                >
                    <span
                        class="text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ normalizedScore }}
                    </span>

                    <span
                        class="text-[10px] text-slate-400"
                    >
                        / 100
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <span
                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                :class="{
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400':
                        normalizedScore >= 80,

                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400':
                        normalizedScore >= 60 && normalizedScore < 80,

                    'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400':
                        normalizedScore < 60,
                }"
            >
                {{ label }}
            </span>
        </div>
    </div>
</template>
