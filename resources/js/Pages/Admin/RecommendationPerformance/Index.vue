<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },

    algorithmPerformance: {
        type: Array,
        default: () => [],
    },

    productPerformance: {
        type: Array,
        default: () => [],
    },

    recentEvents: {
        type: Array,
        default: () => [],
    },

    dailyPerformance: {
        type: Array,
        default: () => [],
    },
})

const statCards = computed(() => [
    {
        label: 'Impressions',
        value: props.stats.impressions ?? 0,
    },
    {
        label: 'Clicks',
        value: props.stats.clicks ?? 0,
    },
    {
        label: 'Inquiries',
        value: props.stats.inquiries ?? 0,
    },
    {
        label: 'Conversions',
        value: props.stats.conversions ?? 0,
    },
    {
        label: 'CTR',
        value: `${props.stats.ctr ?? 0}%`,
    },
    {
        label: 'Inquiry Rate',
        value: `${props.stats.inquiry_rate ?? 0}%`,
    },
    {
        label: 'Conversion Rate',
        value: `${props.stats.conversion_rate ?? 0}%`,
    },
    {
        label: 'Recommendation Conversion',
        value: `${props.stats.recommendation_conversion_rate ?? 0}%`,
    },
])

const eventLabel = (type) => {
    return {
        impression: 'Impression',
        click: 'Click',
        inquiry: 'Inquiry',
        conversion: 'Conversion',
    }[type] ?? type
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10">

            <div class="mb-10">
                <p
                    class="text-sm font-semibold uppercase tracking-[0.25em] text-indigo-400"
                >
                    Source X Intelligence
                </p>

                <h1 class="mt-2 text-4xl font-bold">
                    Recommendation Performance
                </h1>

                <p class="mt-3 text-slate-400">
                    Measure the complete recommendation funnel from
                    impression to conversion.
                </p>
            </div>

            <!-- Stats -->

            <div
                class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-5"
                >
                    <p class="text-sm text-slate-500">
                        {{ card.label }}
                    </p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ card.value }}
                    </p>
                </div>
            </div>

            <!-- Funnel -->

            <section
                class="mb-8 rounded-2xl border border-slate-800 bg-slate-900 p-6"
            >
                <div
                    class="mb-6 flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-xl font-bold">
                            Recommendation Funnel
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Buyer engagement after recommendations are shown.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-xl bg-slate-950 p-5">
                        <p class="text-xs uppercase text-slate-500">
                            Impression
                        </p>

                        <p class="mt-2 text-2xl font-bold">
                            {{ stats.impressions }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-950 p-5">
                        <p class="text-xs uppercase text-slate-500">
                            Click
                        </p>

                        <p class="mt-2 text-2xl font-bold">
                            {{ stats.clicks }}
                        </p>

                        <p class="mt-1 text-xs text-indigo-400">
                            {{ stats.ctr }}% CTR
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-950 p-5">
                        <p class="text-xs uppercase text-slate-500">
                            Inquiry
                        </p>

                        <p class="mt-2 text-2xl font-bold">
                            {{ stats.inquiries }}
                        </p>

                        <p class="mt-1 text-xs text-indigo-400">
                            {{ stats.inquiry_rate }}%
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-950 p-5">
                        <p class="text-xs uppercase text-slate-500">
                            Conversion
                        </p>

                        <p class="mt-2 text-2xl font-bold">
                            {{ stats.conversions }}
                        </p>

                        <p class="mt-1 text-xs text-indigo-400">
                            {{ stats.conversion_rate }}%
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-2">

                <!-- Algorithm -->

                <section
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6"
                >
                    <h2 class="text-xl font-bold">
                        Algorithm Performance
                    </h2>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="border-b border-slate-800 text-slate-500"
                            >
                                <tr>
                                    <th class="px-3 py-3">
                                        Algorithm
                                    </th>

                                    <th class="px-3 py-3">
                                        Impressions
                                    </th>

                                    <th class="px-3 py-3">
                                        Clicks
                                    </th>

                                    <th class="px-3 py-3">
                                        CTR
                                    </th>

                                    <th class="px-3 py-3">
                                        Conversion
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="row in algorithmPerformance"
                                    :key="row.algorithm"
                                    class="border-b border-slate-800/70"
                                >
                                    <td class="px-3 py-4 font-medium">
                                        {{ row.algorithm }}
                                    </td>

                                    <td class="px-3 py-4 text-slate-400">
                                        {{ row.impressions }}
                                    </td>

                                    <td class="px-3 py-4 text-slate-400">
                                        {{ row.clicks }}
                                    </td>

                                    <td class="px-3 py-4 text-indigo-400">
                                        {{ row.ctr }}%
                                    </td>

                                    <td class="px-3 py-4 text-emerald-400">
                                        {{ row.conversion_rate }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Products -->

                <section
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6"
                >
                    <h2 class="text-xl font-bold">
                        Product Performance
                    </h2>

                    <div class="mt-6 space-y-3">
                        <div
                            v-for="row in productPerformance"
                            :key="row.product_id"
                            class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <p class="font-semibold">
                                        {{ row.product?.name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ row.product?.category }}
                                        ·
                                        {{ row.product?.location }}
                                    </p>
                                </div>

                                <span
                                    class="text-sm font-bold text-emerald-400"
                                >
                                    {{ row.conversions }}
                                    conversions
                                </span>
                            </div>

                            <div
                                class="mt-4 grid grid-cols-3 gap-3 text-xs"
                            >
                                <div>
                                    <span class="text-slate-500">
                                        Impressions
                                    </span>

                                    <p class="mt-1 font-semibold">
                                        {{ row.impressions }}
                                    </p>
                                </div>

                                <div>
                                    <span class="text-slate-500">
                                        Clicks
                                    </span>

                                    <p class="mt-1 font-semibold">
                                        {{ row.clicks }}
                                    </p>
                                </div>

                                <div>
                                    <span class="text-slate-500">
                                        CTR
                                    </span>

                                    <p class="mt-1 font-semibold text-indigo-400">
                                        {{ row.ctr }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Daily -->

                <section
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 lg:col-span-2"
                >
                    <h2 class="text-xl font-bold">
                        Last 30 Days
                    </h2>

                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="border-b border-slate-800 text-slate-500"
                            >
                                <tr>
                                    <th class="px-4 py-3">
                                        Date
                                    </th>

                                    <th class="px-4 py-3">
                                        Impressions
                                    </th>

                                    <th class="px-4 py-3">
                                        Clicks
                                    </th>

                                    <th class="px-4 py-3">
                                        Conversions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="row in dailyPerformance"
                                    :key="row.date"
                                    class="border-b border-slate-800/70"
                                >
                                    <td class="px-4 py-3">
                                        {{ row.date }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-400">
                                        {{ row.impressions }}
                                    </td>

                                    <td class="px-4 py-3 text-slate-400">
                                        {{ row.clicks }}
                                    </td>

                                    <td class="px-4 py-3 text-emerald-400">
                                        {{ row.conversions }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Recent Events -->

                <section
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 lg:col-span-2"
                >
                    <h2 class="text-xl font-bold">
                        Recent Recommendation Events
                    </h2>

                    <div class="mt-6 space-y-3">
                        <div
                            v-for="event in recentEvents"
                            :key="event.id"
                            class="flex flex-col gap-3 rounded-xl border border-slate-800 bg-slate-950 p-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ event.product?.name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Buyer:
                                    {{ event.user?.name ?? 'Unknown' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <span
                                    class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-400"
                                >
                                    {{ eventLabel(event.event_type) }}
                                </span>

                                <span class="text-xs text-slate-500">
                                    {{ event.occurred_at }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="recentEvents.length === 0"
                            class="py-8 text-center text-slate-500"
                        >
                            No recommendation events yet.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
