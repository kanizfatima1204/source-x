<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    experiment: {
        type: Object,
        required: true,
    },

    metrics: {
        type: Object,
        default: () => ({}),
    },
})

const control = computed(() => {
    return props.metrics.control ?? {
        users: 0,
        impressions: 0,
        clicks: 0,
        inquiries: 0,
        conversions: 0,
        ctr: 0,
        inquiry_rate: 0,
        conversion_rate: 0,
        recommendation_conversion_rate: 0,
    }
})

const variant = computed(() => {
    return props.metrics.variant ?? {
        users: 0,
        impressions: 0,
        clicks: 0,
        inquiries: 0,
        conversions: 0,
        ctr: 0,
        inquiry_rate: 0,
        conversion_rate: 0,
        recommendation_conversion_rate: 0,
    }
})

const comparison = computed(() => {
    return {
        ctr:
            variant.value.ctr -
            control.value.ctr,

        conversion:
            variant.value.conversion_rate -
            control.value.conversion_rate,

        recommendationConversion:
            variant.value.recommendation_conversion_rate -
            control.value.recommendation_conversion_rate,
    }
})

const formatDelta = (value) => {
    const number = Number(value ?? 0)

    return `${number >= 0 ? '+' : ''}${number.toFixed(2)}%`
}
</script>

<template>
    <Head :title="`Experiment Metrics - ${experiment.name}`" />

    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">

            <div class="mb-10">
                <a
                    href="/admin/recommendation-experiments"
                    class="text-sm text-cyan-400 hover:text-cyan-300"
                >
                    ← Back to Experiments
                </a>

                <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">
                            {{ experiment.name }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ experiment.description }}
                        </p>
                    </div>

                    <span
                        class="w-fit rounded-full border border-slate-700 bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-300"
                    >
                        {{ experiment.status }}
                    </span>
                </div>
            </div>

            <!-- Variant Header -->
            <div class="mb-6 grid gap-5 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Control
                    </p>

                    <h2 class="mt-2 text-2xl font-bold">
                        {{ experiment.control_algorithm }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ control.users }} assigned buyers
                    </p>
                </div>

                <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-6">
                    <p class="text-xs uppercase tracking-wider text-cyan-400">
                        Variant
                    </p>

                    <h2 class="mt-2 text-2xl font-bold">
                        {{ experiment.variant_algorithm }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ variant.users }} assigned buyers
                    </p>
                </div>

            </div>

            <!-- Metrics Table -->
            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left">
                        <thead class="border-b border-slate-800 bg-slate-950/70">
                            <tr>
                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Metric
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Control
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-cyan-400">
                                    Variant
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Difference
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Impressions
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.impressions }}
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.impressions }}
                                </td>

                                <td class="px-6 py-5 text-slate-500">
                                    —
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Clicks
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.clicks }}
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.clicks }}
                                </td>

                                <td class="px-6 py-5 text-slate-500">
                                    —
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    CTR
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.ctr }}%
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.ctr }}%
                                </td>

                                <td class="px-6 py-5 font-semibold text-cyan-300">
                                    {{ formatDelta(comparison.ctr) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Inquiries
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.inquiries }}
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.inquiries }}
                                </td>

                                <td class="px-6 py-5 text-slate-500">
                                    —
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Conversions
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.conversions }}
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.conversions }}
                                </td>

                                <td class="px-6 py-5 text-slate-500">
                                    —
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Conversion Rate
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.conversion_rate }}%
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.conversion_rate }}%
                                </td>

                                <td class="px-6 py-5 font-semibold text-cyan-300">
                                    {{ formatDelta(comparison.conversion) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="px-6 py-5 font-medium">
                                    Recommendation Conversion
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ control.recommendation_conversion_rate }}%
                                </td>

                                <td class="px-6 py-5 text-slate-200">
                                    {{ variant.recommendation_conversion_rate }}%
                                </td>

                                <td class="px-6 py-5 font-semibold text-cyan-300">
                                    {{ formatDelta(comparison.recommendationConversion) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Explanation -->
            <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/50 p-6">
                <h2 class="font-semibold">
                    Experiment Measurement
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Metrics are calculated from recommendation events generated
                    during the experiment period. A buyer keeps the same
                    experiment assignment until the assignment is reset.
                </p>
            </div>

        </div>
    </div>
</template>
