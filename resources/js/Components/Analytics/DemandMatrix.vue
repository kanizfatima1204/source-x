<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },

    categories: {
        type: Array,
        default: () => [],
    },
})

const activeTab = ref('products')

const rows = computed(() => {
    return activeTab.value === 'products'
        ? props.products
        : props.categories
})

const maxRequests = computed(() => {
    return Math.max(
        1,
        ...rows.value.map(
            (row) => Number(row.request_count || 0)
        )
    )
})

const barWidth = (count) => {
    return `${Math.max(
        4,
        (Number(count || 0) / maxRequests.value) * 100
    )}%`
}
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
        >
            <div>
                <h3
                    class="text-base font-semibold text-slate-900 dark:text-white"
                >
                    Demand Intelligence
                </h3>

                <p
                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                >
                    Products and categories receiving the most buyer demand.
                </p>
            </div>

            <div
                class="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-slate-800"
            >
                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-xs font-semibold transition"
                    :class="
                        activeTab === 'products'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                    @click="activeTab = 'products'"
                >
                    Products
                </button>

                <button
                    type="button"
                    class="rounded-md px-3 py-1.5 text-xs font-semibold transition"
                    :class="
                        activeTab === 'categories'
                            ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                    @click="activeTab = 'categories'"
                >
                    Categories
                </button>
            </div>
        </div>

        <div class="p-5">
            <div
                v-if="!rows.length"
                class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400"
            >
                No demand data available for this period.
            </div>

            <div v-else class="space-y-5">
                <div
                    v-for="(row, index) in rows"
                    :key="`${row.product || row.category}-${index}`"
                >
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-medium text-slate-800 dark:text-slate-200"
                            >
                                {{ row.product || row.category }}
                            </p>

                            <p
                                v-if="row.category && activeTab === 'products'"
                                class="mt-0.5 text-xs text-slate-400"
                            >
                                {{ row.category }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ row.request_count }}
                            </p>

                            <p
                                class="text-[10px] text-slate-400"
                            >
                                requests
                            </p>
                        </div>
                    </div>

                    <div
                        class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <div
                            class="h-full rounded-full bg-slate-900 transition-all dark:bg-white"
                            :style="{
                                width: barWidth(row.request_count),
                            }"
                        />
                    </div>

                    <p
                        class="mt-1 text-[10px] text-slate-400"
                    >
                        Requested quantity:
                        {{ row.quantity }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>