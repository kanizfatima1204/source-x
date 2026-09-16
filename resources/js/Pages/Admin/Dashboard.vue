<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },

    metrics: {
        type: Object,
        required: true,
    },

    requestStatus: {
        type: Array,
        default: () => [],
    },

    funnel: {
        type: Array,
        default: () => [],
    },

    requestsOverTime: {
        type: Array,
        default: () => [],
    },

    scoreDistribution: {
        type: Array,
        default: () => [],
    },

    confidenceDistribution: {
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

    lowPerformingSources: {
        type: Array,
        default: () => [],
    },

    selectionAnalytics: {
        type: Array,
        default: () => [],
    },

    adminActivity: {
        type: Array,
        default: () => [],
    },

    recentRequests: {
        type: Array,
        default: () => [],
    },

    recentDecisions: {
        type: Array,
        default: () => [],
    },
})

const dateFrom = ref(props.filters.date_from)
const dateTo = ref(props.filters.date_to)
const loading = ref(false)

const applyFilters = () => {
    loading.value = true

    router.get(
        route('admin.dashboard'),
        {
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

const resetFilters = () => {
    dateFrom.value = ''
    dateTo.value = ''

    loading.value = true

    router.get(
        route('admin.dashboard'),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

const maxRequests = computed(() => {
    return Math.max(
        1,
        ...props.requestsOverTime.map(
            item => Number(item.count)
        )
    )
})

const maxFunnel = computed(() => {
    return Math.max(
        1,
        ...props.funnel.map(
            item => Number(item.value)
        )
    )
})

const maxFactorScore = computed(() => {
    return Math.max(
        100,
        ...props.factorPerformance.map(
            item => Number(item.average_score)
        )
    )
})

const maxPerformance = computed(() => {
    return Math.max(
        100,
        ...props.sourcePerformance.map(
            item => Number(item.performance_score)
        )
    )
})

const statusClass = status => {
    const classes = {
        draft:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',

        submitted:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',

        matched:
            'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',

        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',

        recommended:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',

        reviewed:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',

        approved:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',

        rejected:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    }

    return classes[status] ?? classes.draft
}

const formatStatus = value => {
    if (!value) {
        return 'Unknown'
    }

    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

const scoreClass = score => {
    if (score >= 85) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (score >= 70) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-red-600 dark:text-red-400'
}

const factorClass = score => {
    if (score >= 85) {
        return 'bg-emerald-500'
    }

    if (score >= 70) {
        return 'bg-amber-500'
    }

    return 'bg-red-500'
}
</script>

<template>
    <AppLayout title="Admin Analytics">
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <div
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
            >
                <!-- Header -->
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 font-bold text-white shadow-lg dark:bg-white dark:text-slate-950"
                            >
                                SX
                            </div>

                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-400"
                                >
                                    Source X
                                </p>

                                <h1
                                    class="text-2xl font-black tracking-tight text-slate-950 dark:text-white sm:text-3xl"
                                >
                                    Analytics Intelligence
                                </h1>
                            </div>
                        </div>

                        <p
                            class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Monitor sourcing demand, matching quality,
                            source performance and administrative decisions
                            from your real database.
                        </p>
                    </div>

                    <!-- Filters -->
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-end"
                        >
                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                >
                                    From
                                </label>

                                <input
                                    v-model="dateFrom"
                                    type="date"
                                    class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                >
                                    To
                                </label>

                                <input
                                    v-model="dateTo"
                                    type="date"
                                    class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                />
                            </div>

                            <button
                                type="button"
                                :disabled="loading"
                                class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 disabled:opacity-50 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200"
                                @click="applyFilters"
                            >
                                {{ loading ? 'Loading...' : 'Apply' }}
                            </button>

                            <button
                                type="button"
                                :disabled="loading"
                                class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-slate-700 dark:text-slate-300"
                                @click="resetFilters"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- KPI -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Buyer Requests
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-slate-950 dark:text-white"
                        >
                            {{ metrics.total_requests }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ metrics.matched_requests }} matched
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Verified Sources
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400"
                        >
                            {{ metrics.verified_sources }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ metrics.active_sources }} active sources
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Average Match Score
                        </p>

                        <p
                            :class="scoreClass(metrics.average_match_score)"
                            class="mt-2 text-3xl font-black"
                        >
                            {{ metrics.average_match_score }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ metrics.total_matches }} matches
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Match Conversion
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-violet-600 dark:text-violet-400"
                        >
                            {{ metrics.match_conversion_rate }}%
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Requests converted to matched
                        </p>
                    </div>
                </div>

                <!-- Secondary KPI -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            High Confidence
                        </p>

                        <p
                            class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"
                        >
                            {{ metrics.high_confidence_percentage }}%
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Approval Rate
                        </p>

                        <p
                            class="mt-1 text-2xl font-black text-blue-600 dark:text-blue-400"
                        >
                            {{ metrics.approval_rate }}%
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Rejection Rate
                        </p>

                        <p
                            class="mt-1 text-2xl font-black text-red-600 dark:text-red-400"
                        >
                            {{ metrics.rejection_rate }}%
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Total Matches
                        </p>

                        <p
                            class="mt-1 text-2xl font-black text-slate-950 dark:text-white"
                        >
                            {{ metrics.total_matches }}
                        </p>
                    </div>
                </div>

                <!-- Requests + Funnel -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <section
                        class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="mb-6">
                            <h2
                                class="font-black text-slate-950 dark:text-white"
                            >
                                Requests Over Time
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Daily buyer request volume.
                            </p>
                        </div>

                        <div
                            class="flex h-64 items-end gap-1 overflow-x-auto border-b border-slate-200 pb-2 dark:border-slate-800"
                        >
                            <div
                                v-for="item in requestsOverTime"
                                :key="item.date"
                                class="group flex min-w-[28px] flex-1 flex-col items-center justify-end"
                            >
                                <div
                                    class="relative w-full max-w-[34px] rounded-t-lg bg-violet-500 transition hover:bg-violet-400"
                                    :style="{
                                        height: `${Math.max(
                                            item.count > 0 ? 8 : 2,
                                            (item.count / maxRequests) * 210
                                        )}px`
                                    }"
                                    :title="`${item.label}: ${item.count}`"
                                >
                                    <span
                                        v-if="item.count"
                                        class="absolute -top-6 left-1/2 hidden -translate-x-1/2 text-[10px] font-bold text-slate-600 group-hover:block dark:text-slate-300"
                                    >
                                        {{ item.count }}
                                    </span>
                                </div>

                                <span
                                    v-if="
                                        requestsOverTime.length <= 15 ||
                                        item.date.endsWith('-01') ||
                                        item.date.endsWith('-15')
                                    "
                                    class="mt-2 whitespace-nowrap text-[9px] text-slate-400"
                                >
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Matching Funnel
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Request progression.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div
                                v-for="(item, index) in funnel"
                                :key="item.label"
                            >
                                <div
                                    class="mb-2 flex justify-between text-sm"
                                >
                                    <span
                                        class="font-medium text-slate-600 dark:text-slate-300"
                                    >
                                        {{ item.label }}
                                    </span>

                                    <span
                                        class="font-black text-slate-950 dark:text-white"
                                    >
                                        {{ item.value }}
                                    </span>
                                </div>

                                <div
                                    class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="[
                                            index === 0
                                                ? 'bg-slate-800 dark:bg-slate-200'
                                                : index === 1
                                                    ? 'bg-blue-500'
                                                    : index === 2
                                                        ? 'bg-violet-500'
                                                        : 'bg-emerald-500'
                                        ]"
                                        :style="{
                                            width: `${Math.max(
                                                item.value > 0 ? 5 : 0,
                                                (item.value / maxFunnel) * 100
                                            )}%`
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Score + Confidence -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Match Score Distribution
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Quality distribution of generated matches.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div
                                v-for="bucket in scoreDistribution"
                                :key="bucket.label"
                            >
                                <div
                                    class="mb-2 flex justify-between text-sm"
                                >
                                    <span
                                        class="font-medium text-slate-600 dark:text-slate-300"
                                    >
                                        {{ bucket.label }}
                                    </span>

                                    <span
                                        class="font-black text-slate-950 dark:text-white"
                                    >
                                        {{ bucket.count }}
                                    </span>
                                </div>

                                <div
                                    class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-violet-500"
                                        :style="{
                                            width: `${bucket.percentage}%`
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Confidence Distribution
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Confidence levels produced by the matching engine.
                        </p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div
                                v-for="item in confidenceDistribution"
                                :key="item.status"
                                class="rounded-2xl border border-slate-200 p-5 text-center dark:border-slate-800"
                            >
                                <div
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full"
                                    :class="
                                        item.status === 'high'
                                            ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : item.status === 'medium'
                                                ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'
                                                : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                                    "
                                >
                                    <span class="text-xl font-black">
                                        {{ item.count }}
                                    </span>
                                </div>

                                <p
                                    class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-300"
                                >
                                    {{ item.label }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Factor Analytics -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-6">
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Matching Factor Performance
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Average score generated by each matching factor.
                        </p>
                    </div>

                    <div
                        v-if="factorPerformance.length"
                        class="grid gap-5 md:grid-cols-2"
                    >
                        <div
                            v-for="factor in factorPerformance"
                            :key="factor.factor"
                            class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800"
                        >
                            <div
                                class="mb-3 flex items-center justify-between"
                            >
                                <div>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ factor.label }}
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        {{ factor.total }} evaluations
                                    </p>
                                </div>

                                <span
                                    :class="scoreClass(factor.average_score)"
                                    class="font-black"
                                >
                                    {{ factor.average_score }}
                                </span>
                            </div>

                            <div
                                class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    :class="factorClass(factor.average_score)"
                                    class="h-full rounded-full"
                                    :style="{
                                        width: `${Math.min(
                                            100,
                                            (factor.average_score /
                                                maxFactorScore) *
                                                100
                                        )}%`
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl bg-slate-50 p-8 text-center text-sm text-slate-500 dark:bg-slate-950"
                    >
                        No factor analytics available yet.
                    </div>
                </section>

                <!-- Source Performance -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                        >
                            <h2
                                class="font-black text-slate-950 dark:text-white"
                            >
                                Source Performance
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Active sources ordered by performance score.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div
                                v-for="source in sourcePerformance"
                                :key="source.reference_code"
                                class="px-5 py-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <div>
                                        <span
                                            class="font-mono text-sm font-black text-slate-950 dark:text-white"
                                        >
                                            {{ source.reference_code }}
                                        </span>

                                        <span
                                            class="ml-2 text-xs text-slate-400"
                                        >
                                            {{ source.total_orders }} orders
                                        </span>
                                    </div>

                                    <span
                                        :class="
                                            scoreClass(
                                                source.performance_score
                                            )
                                        "
                                        class="font-black"
                                    >
                                        {{ source.performance_score }}
                                    </span>
                                </div>

                                <div
                                    class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-emerald-500"
                                        :style="{
                                            width: `${
                                                (source.performance_score /
                                                    maxPerformance) *
                                                100
                                            }%`
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <div
                                v-if="sourcePerformance.length === 0"
                                class="p-8 text-center text-sm text-slate-500"
                            >
                                No source performance data available.
                            </div>
                        </div>
                    </section>

                    <!-- Selection Analytics -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Selection Analytics
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Algorithm and administrative selection activity.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div
                                v-for="item in selectionAnalytics"
                                :key="item.label"
                            >
                                <div
                                    class="mb-2 flex justify-between text-sm"
                                >
                                    <span
                                        class="font-medium text-slate-600 dark:text-slate-300"
                                    >
                                        {{ item.label }}
                                    </span>

                                    <span
                                        class="font-black text-slate-950 dark:text-white"
                                    >
                                        {{ item.count }}
                                        <span class="font-normal text-slate-400">
                                            ({{ item.percentage }}%)
                                        </span>
                                    </span>
                                </div>

                                <div
                                    class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-blue-500"
                                        :style="{
                                            width: `${item.percentage}%`
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Recent Requests + Decisions -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                        >
                            <h2
                                class="font-black text-slate-950 dark:text-white"
                            >
                                Recent Requests
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Latest buyer activity.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div
                                v-for="request in recentRequests"
                                :key="request.id"
                                class="px-5 py-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="font-mono text-sm font-black text-slate-950 dark:text-white"
                                        >
                                            {{ request.reference_code }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{
                                                request.user?.name ||
                                                'Unknown buyer'
                                            }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ request.created_at }}
                                        </p>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold"
                                        :class="statusClass(request.status)"
                                    >
                                        {{ formatStatus(request.status) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="recentRequests.length === 0"
                                class="p-8 text-center text-sm text-slate-500"
                            >
                                No requests available.
                            </div>
                        </div>
                    </section>

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                        >
                            <h2
                                class="font-black text-slate-950 dark:text-white"
                            >
                                Recent Admin Decisions
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Latest manual matching decisions.
                            </p>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div
                                v-for="decision in recentDecisions"
                                :key="decision.id"
                                class="px-5 py-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="font-mono text-sm font-black text-slate-950 dark:text-white"
                                            >
                                                {{
                                                    decision.request_reference
                                                }}
                                            </span>

                                            <span class="text-slate-400">
                                                →
                                            </span>

                                            <span
                                                class="font-mono text-sm text-slate-600 dark:text-slate-300"
                                            >
                                                {{
                                                    decision.source_reference
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{ decision.admin }}
                                            ·
                                            {{ formatStatus(decision.action) }}
                                        </p>

                                        <p
                                            v-if="decision.reason"
                                            class="mt-2 line-clamp-2 text-xs text-slate-400"
                                        >
                                            {{ decision.reason }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ decision.created_at }}
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p
                                            :class="scoreClass(decision.score)"
                                            class="font-black"
                                        >
                                            {{ decision.score }}
                                        </p>

                                        <span
                                            class="mt-1 inline-flex rounded-full px-2 py-1 text-[10px] font-bold"
                                            :class="statusClass(decision.status)"
                                        >
                                            {{
                                                formatStatus(
                                                    decision.status
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="recentDecisions.length === 0"
                                class="p-8 text-center text-sm text-slate-500"
                            >
                                No admin decisions available.
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Audit Timeline -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Admin Activity Timeline
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Audit trail for administrative matching actions.
                        </p>
                    </div>

                    <div
                        v-if="adminActivity.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div
                            v-for="activity in adminActivity"
                            :key="activity.id"
                            class="flex gap-4 px-5 py-5"
                        >
                            <div
                                class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-black text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"
                            >
                                A
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ activity.admin }}
                                        <span
                                            class="font-normal text-slate-500"
                                        >
                                            performed
                                        </span>
                                        {{ formatStatus(activity.action) }}
                                    </p>

                                    <span
                                        class="text-xs text-slate-400"
                                    >
                                        {{ activity.created_at }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-slate-500">
                                    Request:
                                    <span
                                        class="font-mono font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ activity.request_reference }}
                                    </span>

                                    <span class="mx-1">
                                        →
                                    </span>

                                    Source:
                                    <span
                                        class="font-mono font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ activity.source_reference }}
                                    </span>
                                </p>

                                <p
                                    v-if="activity.reason"
                                    class="mt-2 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500 dark:bg-slate-950 dark:text-slate-400"
                                >
                                    {{ activity.reason }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="p-10 text-center text-sm text-slate-500"
                    >
                        No administrative activity recorded during this period.
                    </div>
                </section>

                <!-- Low Performance -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2
                            class="font-black text-slate-950 dark:text-white"
                        >
                            Sources Requiring Attention
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Active sources with comparatively lower performance
                            scores.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 text-left dark:border-slate-800"
                                >
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Source
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Performance
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Quality
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Rating
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="source in lowPerformingSources"
                                    :key="source.reference_code"
                                >
                                    <td
                                        class="px-5 py-4 font-mono text-sm font-black text-slate-950 dark:text-white"
                                    >
                                        {{ source.reference_code }}
                                    </td>

                                    <td
                                        :class="
                                            scoreClass(
                                                source.performance_score
                                            )
                                        "
                                        class="px-5 py-4 font-black"
                                    >
                                        {{ source.performance_score }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ source.quality_score }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ source.rating }}
                                    </td>
                                </tr>

                                <tr
                                    v-if="
                                        lowPerformingSources.length === 0
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="px-5 py-8 text-center text-sm text-slate-500"
                                    >
                                        No source data available.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>