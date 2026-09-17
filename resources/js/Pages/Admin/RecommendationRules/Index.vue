<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },

    topProducts: {
        type: Array,
        default: () => [],
    },

    factorPerformance: {
        type: Array,
        default: () => [],
    },

    sourcePerformance: {
        type: Array,
        default: () => [],
    },
})

const cards = computed(() => [
    {
        label: 'Total Recommendations',
        value: props.stats.total_recommendations ?? 0,
    },
    {
        label: 'Average Match Score',
        value: `${props.stats.average_score ?? 0}%`,
    },
    {
        label: 'Recommendation Clicks',
        value: props.stats.total_clicks ?? 0,
    },
    {
        label: 'Click Rate',
        value: `${props.stats.click_rate ?? 0}%`,
    },
])
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10">

            <div class="mb-10">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-indigo-400">
                    Source X Admin
                </p>

                <h1 class="mt-2 text-4xl font-bold">
                    Recommendation Analytics
                </h1>

                <p class="mt-3 text-slate-400">
                    Monitor recommendation quality, engagement and scoring
                    factor contribution.
                </p>
            </div>

            <div class="mb-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="card in cards"
                    :key="card.label"
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6"
                >
                    <p class="text-sm text-slate-500">
                        {{ card.label }}
                    </p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ card.value }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <h2 class="text-xl font-bold">
                        Top Recommended Products
                    </h2>

                    <div class="mt-6 space-y-4">
                        <div
                            v-for="item in topProducts"
                            :key="item.product_id"
                            class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold">
                                        {{ item.product?.name }}
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ item.product?.category }}
                                        ·
                                        {{ item.product?.location }}
                                    </p>
                                </div>

                                <span class="text-sm font-bold text-indigo-400">
                                    {{ item.average_score
                                    ? Number(item.average_score).toFixed(2)
                                    : '0.00' }}
                                </span>
                            </div>

                            <div class="mt-4 flex gap-5 text-xs text-slate-500">
                                <span>
                                    Recommendations:
                                    <strong class="text-slate-300">
                                        {{ item.recommendation_count }}
                                    </strong>
                                </span>

                                <span>
                                    Clicks:
                                    <strong class="text-slate-300">
                                        {{ item.click_count }}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="topProducts.length === 0"
                            class="py-10 text-center text-slate-500"
                        >
                            No analytics data yet.
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <h2 class="text-xl font-bold">
                        Factor Contribution
                    </h2>

                    <div class="mt-6 space-y-5">
                        <div
                            v-for="item in factorPerformance"
                            :key="item.factor"
                        >
                            <div class="mb-2 flex justify-between">
                                <span class="text-sm text-slate-300">
                                    {{ item.factor }}
                                </span>

                                <span class="text-sm font-semibold text-indigo-400">
                                    {{ item.average_contribution }}%
                                </span>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-indigo-500"
                                    :style="{
                                        width: `${Math.min(item.average_contribution, 100)}%`
                                    }"
                                />
                            </div>
                        </div>

                        <div
                            v-if="factorPerformance.length === 0"
                            class="py-10 text-center text-slate-500"
                        >
                            No factor data yet.
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 lg:col-span-2">
                    <h2 class="text-xl font-bold">
                        Recommendation Source Performance
                    </h2>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-slate-800 text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">
                                        Source
                                    </th>

                                    <th class="px-4 py-3">
                                        Total
                                    </th>

                                    <th class="px-4 py-3">
                                        Average Score
                                    </th>

                                    <th class="px-4 py-3">
                                        Clicks
                                    </th>

                                    <th class="px-4 py-3">
                                        Click Rate
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="item in sourcePerformance"
                                    :key="item.source"
                                    class="border-b border-slate-800/70"
                                >
                                    <td class="px-4 py-4 font-medium">
                                        {{ item.source }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-400">
                                        {{ item.total }}
                                    </td>

                                    <td class="px-4 py-4 text-slate-400">
                                        {{ item.average_score }}%
                                    </td>

                                    <td class="px-4 py-4 text-slate-400">
                                        {{ item.clicks }}
                                    </td>

                                    <td class="px-4 py-4 text-indigo-400">
                                        {{ item.click_rate }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
