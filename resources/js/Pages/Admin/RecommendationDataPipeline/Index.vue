<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },

    algorithmBreakdown: {
        type: Array,
        default: () => [],
    },

    recentSamples: {
        type: Array,
        default: () => [],
    },
})

const processing = ref(false)

const positiveRate = computed(() => {
    if (!props.stats.training_samples) {
        return 0
    }

    return (
        (props.stats.positive_samples /
            props.stats.training_samples) *
        100
    ).toFixed(2)
})

const conversionRate = computed(() => {
    if (!props.stats.training_samples) {
        return 0
    }

    return (
        (props.stats.converted_samples /
            props.stats.training_samples) *
        100
    ).toFixed(2)
})

const buildFeatures = () => {
    processing.value = true

    router.post(
        '/admin/recommendation-data-pipeline/build-features',
        {
            days: 30,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const buildTrainingDataset = () => {
    processing.value = true

    router.post(
        '/admin/recommendation-data-pipeline/build-training-dataset',
        {
            days: 30,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const labelClass = (label) => {
    const value = Number(label ?? 0)

    if (value >= 1) {
        return 'text-emerald-300'
    }

    if (value >= 0.75) {
        return 'text-cyan-300'
    }

    if (value >= 0.5) {
        return 'text-amber-300'
    }

    return 'text-slate-500'
}
</script>

<template>
    <Head title="Recommendation Data Pipeline" />

    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">

            <!-- Header -->
            <div class="mb-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span
                        class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300"
                    >
                        Source X AI
                    </span>

                    <h1 class="mt-4 text-3xl font-bold lg:text-4xl">
                        Recommendation Data Pipeline
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">
                        Transform buyer behaviour and recommendation events
                        into structured features and ML-ready training samples.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="/admin/recommendation-experiments"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300"
                    >
                        Experiments
                    </a>

                    <a
                        href="/admin/recommendation-performance"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300"
                    >
                        Performance
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Buyer Snapshots
                    </p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ stats.buyer_snapshots ?? 0 }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Product Snapshots
                    </p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ stats.product_snapshots ?? 0 }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Training Samples
                    </p>

                    <p class="mt-3 text-3xl font-bold">
                        {{ stats.training_samples ?? 0 }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Positive Rate
                    </p>

                    <p class="mt-3 text-3xl font-bold text-cyan-300">
                        {{ positiveRate }}%
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <p class="text-xs uppercase tracking-wider text-slate-500">
                        Conversion Samples
                    </p>

                    <p class="mt-3 text-3xl font-bold text-emerald-300">
                        {{ stats.converted_samples ?? 0 }}
                    </p>
                </div>

            </div>

            <!-- Pipeline Actions -->
            <section class="mt-8 grid gap-5 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <h2 class="text-xl font-bold">
                                Build Feature Snapshots
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Generate buyer and product features from
                                the latest 30 days of behaviour.
                            </p>
                        </div>

                        <div class="rounded-xl bg-cyan-500/10 px-3 py-2 text-cyan-300">
                            30D
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="processing"
                        @click="buildFeatures"
                        class="mt-6 rounded-xl bg-cyan-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400 disabled:opacity-50"
                    >
                        {{
                            processing
                                ? 'Processing...'
                                : 'Build Features'
                        }}
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <h2 class="text-xl font-bold">
                                Build Training Dataset
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Convert recommendation logs and feedback
                                events into learning samples.
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-500/10 px-3 py-2 text-emerald-300">
                            ML
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="processing"
                        @click="buildTrainingDataset"
                        class="mt-6 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-emerald-400 disabled:opacity-50"
                    >
                        {{
                            processing
                                ? 'Processing...'
                                : 'Build Dataset'
                        }}
                    </button>
                </div>

            </section>

            <!-- Pipeline -->
            <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/70 p-6">

                <h2 class="text-xl font-bold">
                    Feature Pipeline
                </h2>

                <div class="mt-8 grid gap-4 md:grid-cols-5">

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-5">
                        <span class="text-xs text-cyan-400">
                            01
                        </span>

                        <h3 class="mt-3 font-semibold">
                            Behaviour
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Search, view, request and feedback events.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-5">
                        <span class="text-xs text-cyan-400">
                            02
                        </span>

                        <h3 class="mt-3 font-semibold">
                            Buyer Features
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Category, location, budget and engagement.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-5">
                        <span class="text-xs text-cyan-400">
                            03
                        </span>

                        <h3 class="mt-3 font-semibold">
                            Product Features
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Popularity, availability, verification and quality.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-5">
                        <span class="text-xs text-cyan-400">
                            04
                        </span>

                        <h3 class="mt-3 font-semibold">
                            Training Sample
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Pair buyer + product + recommendation signals.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-5">
                        <span class="text-xs text-cyan-400">
                            05
                        </span>

                        <h3 class="mt-3 font-semibold">
                            Future ML
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Ready for ranking models and embeddings.
                        </p>
                    </div>

                </div>
            </section>

            <!-- Algorithm Breakdown -->
            <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/70 p-6">

                <h2 class="text-xl font-bold">
                    Training Samples by Algorithm
                </h2>

                <div class="mt-6 space-y-4">
                    <div
                        v-for="item in algorithmBreakdown"
                        :key="item.algorithm"
                        class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-950 px-5 py-4"
                    >
                        <span class="font-medium">
                            {{ item.algorithm || 'Unknown' }}
                        </span>

                        <span class="rounded-full bg-slate-800 px-3 py-1 text-sm text-slate-300">
                            {{ item.samples }}
                        </span>
                    </div>

                    <div
                        v-if="!algorithmBreakdown.length"
                        class="py-8 text-center text-sm text-slate-500"
                    >
                        No training samples available yet.
                    </div>
                </div>
            </section>

            <!-- Recent Samples -->
            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">

                <div class="border-b border-slate-800 p-6">
                    <h2 class="text-xl font-bold">
                        Recent Training Samples
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="bg-slate-950/70">
                            <tr>
                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Buyer
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Product
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Algorithm
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Score
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Clicks
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Conversion
                                </th>

                                <th class="px-6 py-4 text-xs uppercase tracking-wider text-slate-500">
                                    Label
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-800">
                            <tr
                                v-for="sample in recentSamples"
                                :key="sample.id"
                            >
                                <td class="px-6 py-4">
                                    {{ sample.user?.name ?? 'Unknown' }}
                                </td>

                                <td class="px-6 py-4 text-slate-300">
                                    {{ sample.product?.name ?? 'Unknown' }}
                                </td>

                                <td class="px-6 py-4 text-slate-400">
                                    {{ sample.algorithm ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ sample.recommendation_score ?? 0 }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ sample.clicks }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ sample.conversions }}
                                </td>

                                <td
                                    class="px-6 py-4 font-semibold"
                                    :class="labelClass(sample.engagement_label)"
                                >
                                    {{ sample.engagement_label }}
                                </td>
                            </tr>

                            <tr v-if="!recentSamples.length">
                                <td
                                    colspan="7"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    No training samples found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </section>

        </div>
    </div>
</template>
