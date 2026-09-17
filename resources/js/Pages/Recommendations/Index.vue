<script setup>
import axios from 'axios'
import { onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    recommendations: {
        type: Array,
        default: () => [],
    },
})

const recommendations = computed(
    () => props.recommendations ?? []
)

const factorLabels = {
    category_match: 'Category',
    location_match: 'Location',
    budget_match: 'Budget',
    availability: 'Availability',
    verification: 'Verification',
    behaviour_match: 'Behaviour',
    previous_requests: 'Previous Requests',
    popularity: 'Popularity',
    product_similarity: 'Product Similarity',
    buyer_similarity: 'Similar Buyers',
    recency: 'Recent Activity',
}

const trackImpression = async (item) => {
    if (!item.log_id) {
        return
    }

    try {
        await axios.post(
            '/recommendation-feedback/impression',
            {
                log_id: item.log_id,
            }
        )
    } catch (error) {
        console.error(
            'Impression tracking failed.',
            error
        )
    }
}

const trackClick = async (item) => {
    if (!item.log_id) {
        return
    }

    try {
        await axios.post(
            '/recommendation-feedback/click',
            {
                log_id: item.log_id,
            }
        )
    } catch (error) {
        console.error(
            'Click tracking failed.',
            error
        )
    }
}

const openProduct = async (item) => {
    await trackClick(item)

    router.visit(
        `/products/${item.product.id}`
    )
}

const factorEntries = (item) => {
    return Object.entries(
        item.breakdown ?? {}
    )
}

onMounted(() => {
    recommendations.value.forEach(
        (item) => {
            trackImpression(item)
        }
    )
})
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10">

            <div class="mb-10">
                <p
                    class="text-sm font-semibold uppercase tracking-[0.25em] text-indigo-400"
                >
                    Source X Hybrid Intelligence
                </p>

                <div
                    class="mt-3 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <h1 class="text-4xl font-bold">
                            Recommended For You
                        </h1>

                        <p
                            class="mt-3 max-w-3xl text-slate-400"
                        >
                            Personalized recommendations based on your
                            searches, requests, product views, similarity,
                            recency and buyer behaviour.
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 px-4 py-3"
                    >
                        <p class="text-xs text-slate-500">
                            Algorithm
                        </p>

                        <p class="mt-1 font-semibold text-indigo-400">
                            Hybrid v1
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="recommendations.length === 0"
                class="rounded-2xl border border-slate-800 bg-slate-900 p-12 text-center"
            >
                <h2 class="text-xl font-semibold">
                    Building your recommendations
                </h2>

                <p class="mx-auto mt-2 max-w-lg text-slate-400">
                    Search products, view product pages or create a buyer
                    request to give Source X more signals.
                </p>
            </div>

            <div
                v-else
                class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="item in recommendations"
                    :key="item.log_id ?? item.product.id"
                    class="rounded-2xl border border-slate-800 bg-slate-900 transition hover:-translate-y-1 hover:border-indigo-500/50"
                >
                    <div class="p-6">

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-indigo-400"
                                >
                                    {{ item.product.category }}
                                </p>

                                <h2 class="mt-1 text-xl font-bold">
                                    {{ item.product.name }}
                                </h2>
                            </div>

                            <div
                                class="rounded-xl bg-indigo-500/10 px-3 py-2 text-center"
                            >
                                <p
                                    class="text-2xl font-bold text-indigo-400"
                                >
                                    {{ item.score }}
                                </p>

                                <p
                                    class="text-[10px] uppercase text-slate-500"
                                >
                                    Match
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-xl border border-indigo-500/10 bg-indigo-500/5 p-4"
                        >
                            <p
                                class="text-xs uppercase tracking-wider text-indigo-400"
                            >
                                Why recommended
                            </p>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-300"
                            >
                                {{ item.reason }}
                            </p>
                        </div>

                        <p
                            class="mt-5 line-clamp-3 text-sm leading-6 text-slate-400"
                        >
                            {{ item.product.description }}
                        </p>

                        <div
                            class="mt-5 grid grid-cols-2 gap-3"
                        >
                            <div
                                class="rounded-xl bg-slate-800/60 p-3"
                            >
                                <span
                                    class="block text-xs text-slate-500"
                                >
                                    Location
                                </span>

                                <span
                                    class="mt-1 block text-sm font-medium"
                                >
                                    {{ item.product.location }}
                                </span>
                            </div>

                            <div
                                class="rounded-xl bg-slate-800/60 p-3"
                            >
                                <span
                                    class="block text-xs text-slate-500"
                                >
                                    Budget
                                </span>

                                <span
                                    class="mt-1 block text-sm font-medium capitalize"
                                >
                                    {{ item.product.budget_level }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex flex-wrap gap-2"
                        >
                            <span
                                v-if="item.product.is_verified"
                                class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs text-emerald-400"
                            >
                                ✓ Verified
                            </span>

                            <span
                                v-if="item.product.is_available"
                                class="rounded-full bg-blue-500/10 px-3 py-1 text-xs text-blue-400"
                            >
                                Available
                            </span>
                        </div>

                        <div
                            class="mt-6 border-t border-slate-800 pt-5"
                        >
                            <p
                                class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                Match Signals
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-for="[key, value] in factorEntries(item)"
                                    :key="key"
                                >
                                    <div
                                        class="mb-1 flex justify-between text-xs"
                                    >
                                        <span class="text-slate-500">
                                            {{ factorLabels[key] ?? key }}
                                        </span>

                                        <span class="text-slate-300">
                                            {{ Math.round(value * 100) }}%
                                        </span>
                                    </div>

                                    <div
                                        class="h-1.5 overflow-hidden rounded-full bg-slate-800"
                                    >
                                        <div
                                            class="h-full rounded-full bg-indigo-500"
                                            :style="{
                                                width: `${Math.min(value * 100, 100)}%`
                                            }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-6 w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold transition hover:bg-indigo-500"
                            @click="openProduct(item)"
                        >
                            View Product
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>
