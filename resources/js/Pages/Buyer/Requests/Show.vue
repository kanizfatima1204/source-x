<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref } from 'vue'

const props = defineProps({
    buyerRequest: {
        type: Object,
        required: true,
    },

    matches: {
        type: Array,
        default: () => [],
    },
})

const matching = ref(false)

const matches = computed(() => {
    return props.matches ?? []
})

const statusClasses = {
    draft: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    submitted: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    matched: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
}

const confidenceClasses = {
    high: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    low: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
}

function runMatching() {
    if (matching.value) {
        return
    }

    matching.value = true

    router.post(
        route('buyer.requests.match', props.buyerRequest.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                matching.value = false
            },
        }
    )
}

function scoreClass(score) {
    if (score >= 85) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (score >= 70) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-red-600 dark:text-red-400'
}

function factorBarClass(score) {
    if (score >= 85) {
        return 'bg-emerald-500'
    }

    if (score >= 70) {
        return 'bg-amber-500'
    }

    return 'bg-red-500'
}

function factorLabel(factor) {
    return {
        product: 'Product Match',
        category: 'Category',
        location: 'Location',
        price: 'Price',
        quality: 'Quality',
        availability: 'Availability',
        verification: 'Verification',
        performance: 'Performance',
    }[factor] ?? factor
}
</script>

<template>
    <Head :title="`Request ${buyerRequest.reference_code}`" />

    <AppLayout>
        <div class="space-y-8">

            <!-- Header -->
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <Link
                            :href="route('buyer.requests.index')"
                            class="transition hover:text-indigo-600"
                        >
                            My Requests
                        </Link>

                        <span>/</span>

                        <span>{{ buyerRequest.reference_code }}</span>
                    </div>

                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ buyerRequest.reference_code }}
                    </h1>

                    <p class="mt-2 text-slate-500 dark:text-slate-400">
                        Review your sourcing request and matched sources.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link
                        v-if="['draft', 'submitted'].includes(buyerRequest.status)"
                        :href="route('buyer.requests.edit', buyerRequest.id)"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Edit Request
                    </Link>

                    <button
                        v-if="['submitted', 'matched'].includes(buyerRequest.status)"
                        type="button"
                        :disabled="matching"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="runMatching"
                    >
                        <svg
                            v-if="matching"
                            class="mr-2 h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                                class="opacity-25"
                            />
                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>

                        <span>
                            {{ matching ? 'Finding Sources...' : 'Find Matching Sources' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Request overview -->
            <div class="grid gap-6 lg:grid-cols-3">

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 text-sm font-medium text-slate-500 dark:text-slate-400">
                        Status
                    </div>

                    <span
                        class="inline-flex rounded-full px-3 py-1 text-sm font-semibold capitalize"
                        :class="statusClasses[buyerRequest.status] ?? statusClasses.draft"
                    >
                        {{ buyerRequest.status }}
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                        Location
                    </div>

                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ buyerRequest.location }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                        Required By
                    </div>

                    <div class="font-semibold text-slate-900 dark:text-white">
                        {{ buyerRequest.required_by || 'Flexible' }}
                    </div>
                </div>
            </div>

            <!-- Request details -->
            <section class="grid gap-6 lg:grid-cols-2">

                <!-- Products -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            Requested Products
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Products included in this sourcing request.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="item in buyerRequest.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-4 px-6 py-5"
                        >
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">
                                    {{ item.product?.name || 'Unknown Product' }}
                                </div>

                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ item.category?.name || 'Uncategorized' }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-semibold text-slate-900 dark:text-white">
                                    {{ item.quantity }}
                                </div>

                                <div class="text-xs uppercase text-slate-500 dark:text-slate-400">
                                    {{ item.unit }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget / Quality -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            Request Preferences
                        </h2>
                    </div>

                    <div class="space-y-5 p-6">
                        <div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Budget Range
                            </div>

                            <div class="mt-1 font-semibold text-slate-900 dark:text-white">
                                <template v-if="buyerRequest.min_budget || buyerRequest.max_budget">
                                    {{ buyerRequest.min_budget || '0' }}
                                    —
                                    {{ buyerRequest.max_budget || 'No limit' }}
                                </template>

                                <template v-else>
                                    Flexible
                                </template>
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Quality Requirement
                            </div>

                            <div class="mt-1 text-slate-900 dark:text-white">
                                {{ buyerRequest.quality_requirement || 'No specific requirement' }}
                            </div>
                        </div>

                        <div v-if="buyerRequest.notes">
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Notes
                            </div>

                            <div class="mt-1 whitespace-pre-line text-slate-900 dark:text-white">
                                {{ buyerRequest.notes }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Matching section -->
            <section class="space-y-5">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Source Matches
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Source X ranked verified sources using multiple matching factors.
                        </p>
                    </div>

                    <div
                        v-if="matches.length"
                        class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                    >
                        {{ matches.length }} match{{ matches.length === 1 ? '' : 'es' }}
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!matches.length"
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl dark:bg-indigo-900/30">
                        ◎
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900 dark:text-white">
                        No matching sources yet
                    </h3>

                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Run the matching engine to find verified sources that
                        fit your products, budget, quality, availability and
                        location requirements.
                    </p>

                    <button
                        v-if="['submitted', 'matched'].includes(buyerRequest.status)"
                        type="button"
                        :disabled="matching"
                        class="mt-6 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                        @click="runMatching"
                    >
                        {{ matching ? 'Finding Sources...' : 'Start Matching' }}
                    </button>
                </div>

                <!-- Match cards -->
                <div
                    v-for="match in matches"
                    :key="match.id"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Match header -->
                    <div class="border-b border-slate-200 p-6 dark:border-slate-800">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    #{{ match.rank }}
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                            Verified Source {{ match.source?.reference_code }}
                                        </h3>

                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                            :class="confidenceClasses[match.confidence]"
                                        >
                                            {{ match.confidence }} confidence
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        {{ match.summary }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-left lg:text-right">
                                <div
                                    class="text-3xl font-black"
                                    :class="scoreClass(Number(match.total_score))"
                                >
                                    {{ Number(match.total_score).toFixed(2) }}
                                </div>

                                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                    Match Score / 100
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Factor breakdown -->
                    <div class="grid gap-6 p-6 lg:grid-cols-2">
                        <div
                            v-for="factor in match.explanation?.factors || []"
                            :key="factor.id"
                            class="space-y-2"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                        {{ factorLabel(factor.factor) }}
                                    </span>

                                    <span class="text-xs text-slate-400">
                                        {{ factor.weight }}%
                                    </span>
                                </div>

                                <span
                                    class="text-sm font-bold"
                                    :class="scoreClass(Number(factor.score))"
                                >
                                    {{ Number(factor.score).toFixed(0) }}
                                </span>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="factorBarClass(Number(factor.score))"
                                    :style="{ width: `${Math.min(100, Number(factor.score))}%` }"
                                />
                            </div>

                            <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">
                                {{ factor.message }}
                            </p>
                        </div>
                    </div>

                    <!-- Strengths / weaknesses -->
                    <div
                        v-if="
                            (match.explanation?.strengths?.length || 0) ||
                            (match.explanation?.weaknesses?.length || 0)
                        "
                        class="grid gap-4 border-t border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-950/40 lg:grid-cols-2"
                    >
                        <div v-if="match.explanation?.strengths?.length">
                            <div class="mb-3 text-sm font-bold text-emerald-700 dark:text-emerald-400">
                                Why this source matched
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="strength in match.explanation.strengths"
                                    :key="`${match.id}-strength-${strength.factor}`"
                                    class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <span class="mt-0.5 text-emerald-500">✓</span>

                                    <span>
                                        {{ strength.message }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="match.explanation?.weaknesses?.length">
                            <div class="mb-3 text-sm font-bold text-amber-700 dark:text-amber-400">
                                Factors to consider
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="weakness in match.explanation.weaknesses"
                                    :key="`${match.id}-weakness-${weakness.factor}`"
                                    class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <span class="mt-0.5 text-amber-500">!</span>

                                    <span>
                                        {{ weakness.message }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buyer-safe footer -->
                    <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                        <div class="flex flex-col gap-2 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                Source identity is protected by Source X.
                            </span>

                            <span>
                                Match generated by the Source X intelligent matching engine.
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
