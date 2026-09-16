<script setup>
import { computed } from 'vue'

const props = defineProps({
    alert: {
        type: Object,
        required: true,
    },
})

const icon = computed(() => {
    return {
        verification: '✓',
        availability: '◈',
        performance: '↗',
    }[props.alert.type] ?? '!'
})

const severityClasses = computed(() => {
    if (props.alert.severity === 'high') {
        return {
            wrapper:
                'border-rose-200 bg-rose-50/70 dark:border-rose-500/20 dark:bg-rose-500/5',

            icon:
                'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        }
    }

    return {
        wrapper:
            'border-amber-200 bg-amber-50/70 dark:border-amber-500/20 dark:bg-amber-500/5',

        icon:
            'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    }
})
</script>

<template>
    <div
        class="rounded-xl border p-4"
        :class="severityClasses.wrapper"
    >
        <div class="flex gap-3">
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold"
                :class="severityClasses.icon"
            >
                {{ icon }}
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h4
                        class="text-sm font-semibold text-slate-900 dark:text-white"
                    >
                        {{ alert.title }}
                    </h4>

                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                        :class="
                            alert.severity === 'high'
                                ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'
                                : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'
                        "
                    >
                        {{ alert.severity }}
                    </span>
                </div>

                <p
                    class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400"
                >
                    {{ alert.message }}
                </p>

                <p
                    v-if="alert.reference_code"
                    class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-500"
                >
                    {{ alert.reference_code }}
                </p>
            </div>
        </div>
    </div>
</template>