<script setup>
import { computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import DashboardStatCard from '@/Components/Analytics/DashboardStatCard.vue'
import AnalyticsProgressBar from '@/Components/Analytics/AnalyticsProgressBar.vue'
import HealthScoreCard from '@/Components/Analytics/HealthScoreCard.vue'
import OperationalAlertCard from '@/Components/Analytics/OperationalAlertCard.vue'
import DemandMatrix from '@/Components/Analytics/DemandMatrix.vue'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    dashboard: {
        type: Object,
        required: true,
    },
})

const filters = reactive({
    date_from:
        props.dashboard.period?.date_from ?? '',

    date_to:
        props.dashboard.period?.date_to ?? '',
})

const overview = computed(
    () => props.dashboard.overview ?? {}
)

const sourceHealth = computed(
    () => props.dashboard.source_health ?? {}
)

const sourceUtilization = computed(
    () => props.dashboard.source_utilization ?? {}
)

const matchingHealth = computed(
    () => props.dashboard.matching_health ?? {}
)

const demandMatrix = computed(
    () => props.dashboard.demand_matrix ?? {}
)

const alerts = computed(
    () => props.dashboard.alerts ?? {}
)

const adminActivity = computed(
    () => props.dashboard.admin_activity ?? {}
)

const applyFilters = () => {
    router.get(
        route('admin.operational.index'),
        {
            date_from: filters.date_from,
            date_to: filters.date_to,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const resetFilters = () => {
    filters.date_from = ''
    filters.date_to = ''

    router.get(
        route('admin.operational.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const confidenceTotal = computed(() => {
    const confidence =
        matchingHealth.value.confidence ?? {}

    return (
        Number(confidence.high || 0)
        + Number(confidence.medium || 0)
        + Number(confidence.low || 0)
    )
})

const confidencePercentage = (value) => {
    if (!confidenceTotal.value) {
        return 0
    }

    return (
        Number(value || 0)
        / confidenceTotal.value
    ) * 100
}

const healthClass = (score) => {
    if (Number(score) >= 80) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (Number(score) >= 60) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-rose-600 dark:text-rose-400'
}

const healthBadgeClass = (score) => {
    if (Number(score) >= 80) {
        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
    }

    if (Number(score) >= 60) {
        return 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'
    }

    return 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'
}
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 dark:bg-slate-950"
    >
        <div class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div
                class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <div
                        class="mb-2 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        />

                        Operational Intelligence
                    </div>

                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                    >
                        Source X Operations
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Monitor source health, matching quality, demand,
                        alerts and administrative activity from live
                        application data.
                    </p>
                </div>

                <!-- Filters -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-end"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                            >
                                From
                            </label>

                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="block rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-0 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                            >
                                To
                            </label>

                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="block rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-0 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>

                        <button
                            type="button"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                            @click="applyFilters"
                        >
                            Apply
                        </button>

                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overview -->
            <section class="mb-6">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <DashboardStatCard
                        title="Buyer Requests"
                        :value="overview.total_requests || 0"
                        subtitle="Selected period"
                    />

                    <DashboardStatCard
                        title="Matched Requests"
                        :value="overview.matched_requests || 0"
                        :subtitle="`${overview.match_success_rate || 0}% match success`"
                    />

                    <DashboardStatCard
                        title="Average Match Score"
                        :value="overview.average_match_score || 0"
                        subtitle="Across generated matches"
                    />

                    <DashboardStatCard
                        title="Open Alerts"
                        :value="alerts.total || 0"
                        :subtitle="`${alerts.high || 0} high priority`"
                    />
                </div>
            </section>

            <!-- Health -->
            <section class="mb-6">
                <div
                    class="mb-4 flex items-end justify-between gap-4"
                >
                    <div>
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Source Health
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Combined operational condition of active and
                            registered sources.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <HealthScoreCard
                        title="Average Source Health"
                        :score="sourceHealth.average_score || 0"
                        subtitle="Quality + performance + verification + availability"
                    />

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Source Distribution
                        </p>

                        <div class="mt-5 space-y-4">
                            <AnalyticsProgressBar
                                label="Healthy"
                                :value="sourceHealth.healthy_sources || 0"
                                :max="sourceHealth.total_sources || 1"
                            />

                            <AnalyticsProgressBar
                                label="Needs Attention"
                                :value="sourceHealth.attention_sources || 0"
                                :max="sourceHealth.total_sources || 1"
                            />

                            <AnalyticsProgressBar
                                label="Critical"
                                :value="sourceHealth.critical_sources || 0"
                                :max="sourceHealth.total_sources || 1"
                            />
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            Matching Health
                        </p>

                        <div class="mt-5">
                            <p
                                class="text-4xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ matchingHealth.average_score || 0 }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-400"
                            >
                                Average score
                            </p>

                            <div class="mt-6">
                                <AnalyticsProgressBar
                                    label="Requests with matches"
                                    :value="matchingHealth.requests?.with_matches || 0"
                                    :max="matchingHealth.requests?.total || 1"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Source table -->
            <section class="mb-6">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2
                            class="font-bold text-slate-900 dark:text-white"
                        >
                            Source Health Ranking
                        </h2>

                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Health is calculated from operational source data.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead
                                class="bg-slate-50 dark:bg-slate-950/50"
                            >
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Source
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Health
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Quality
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Performance
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Verification
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Availability
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wide text-slate-400"
                                    >
                                        Products
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="source in sourceHealth.sources || []"
                                    :key="source.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                                >
                                    <td class="px-5 py-4">
                                        <p
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ source.reference_code }}
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-400"
                                        >
                                            {{ source.name }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-bold"
                                                :class="healthClass(source.health_score)"
                                            >
                                                {{ source.health_score }}
                                            </span>

                                            <span
                                                class="rounded-full px-2 py-1 text-[10px] font-semibold"
                                                :class="healthBadgeClass(source.health_score)"
                                            >
                                                {{ source.health_label }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ source.quality_score }}
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ source.performance_score }}
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ source.verification_score }}
                                    </td>

                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ source.availability_score }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="text-sm text-slate-600 dark:text-slate-300"
                                        >
                                            {{ source.available_product_count }}
                                            /
                                            {{ source.product_count }}
                                        </span>
                                    </td>
                                </tr>

                                <tr
                                    v-if="!(sourceHealth.sources || []).length"
                                >
                                    <td
                                        colspan="7"
                                        class="px-5 py-10 text-center text-sm text-slate-500"
                                    >
                                        No sources available.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Matching + confidence -->
            <section class="mb-6 grid gap-6 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="font-bold text-slate-900 dark:text-white"
                    >
                        Match Confidence
                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Distribution of generated match confidence.
                    </p>

                    <div class="mt-6 space-y-5">
                        <AnalyticsProgressBar
                            label="High confidence"
                            :value="matchingHealth.confidence?.high || 0"
                            :max="confidenceTotal || 1"
                        />

                        <AnalyticsProgressBar
                            label="Medium confidence"
                            :value="matchingHealth.confidence?.medium || 0"
                            :max="confidenceTotal || 1"
                        />

                        <AnalyticsProgressBar
                            label="Low confidence"
                            :value="matchingHealth.confidence?.low || 0"
                            :max="confidenceTotal || 1"
                        />
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="font-bold text-slate-900 dark:text-white"
                    >
                        Match Outcomes
                    </h2>

                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <div
                            class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-500/10"
                        >
                            <p
                                class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                Approved
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300"
                            >
                                {{ matchingHealth.statuses?.approved || 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-rose-50 p-4 dark:bg-rose-500/10"
                        >
                            <p
                                class="text-xs font-semibold text-rose-600 dark:text-rose-400"
                            >
                                Rejected
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-rose-700 dark:text-rose-300"
                            >
                                {{ matchingHealth.statuses?.rejected || 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-slate-100 p-4 dark:bg-slate-800"
                        >
                            <p
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                Other
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-slate-800 dark:text-white"
                            >
                                {{ matchingHealth.statuses?.other || 0 }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-xl border border-slate-200 p-4 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <span
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Requests without matches
                            </span>

                            <span
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                {{ matchingHealth.requests?.without_matches || 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Demand -->
            <section class="mb-6">
                <DemandMatrix
                    :products="demandMatrix.products || []"
                    :categories="demandMatrix.categories || []"
                />
            </section>

            <!-- Utilization + Admin activity -->
            <section class="mb-6 grid gap-6 lg:grid-cols-2">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <h2
                            class="font-bold text-slate-900 dark:text-white"
                        >
                            Source Utilization
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="source in sourceUtilization.sources || []"
                            :key="source.reference_code"
                            class="p-5"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ source.reference_code }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        {{ source.name }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ source.match_count }}
                                    </p>

                                    <p
                                        class="text-[10px] text-slate-400"
                                    >
                                        matches
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3">
                                <AnalyticsProgressBar
                                    label="Utilization"
                                    :value="source.utilization_score"
                                    :max="100"
                                />
                            </div>
                        </div>

                        <div
                            v-if="!(sourceUtilization.sources || []).length"
                            class="p-8 text-center text-sm text-slate-500"
                        >
                            No utilization data available.
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <h2
                        class="font-bold text-slate-900 dark:text-white"
                    >
                        Admin Activity
                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Match decisions recorded by administrators.
                    </p>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div
                            class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"
                        >
                            <p class="text-xs text-slate-400">
                                Total actions
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"
                            >
                                {{ adminActivity.total_actions || 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-500/10"
                        >
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                Approvals
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300"
                            >
                                {{ adminActivity.actions?.approve || 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-rose-50 p-4 dark:bg-rose-500/10"
                        >
                            <p class="text-xs text-rose-600 dark:text-rose-400">
                                Rejections
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-rose-700 dark:text-rose-300"
                            >
                                {{ adminActivity.actions?.reject || 0 }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-blue-50 p-4 dark:bg-blue-500/10"
                        >
                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                Rank changes
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300"
                            >
                                {{ adminActivity.actions?.change_rank || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Alert Center -->
            <section>
                <div
                    class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                Operational Alert Center
                            </h2>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Source conditions requiring administrative
                                attention.
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <span
                                class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400"
                            >
                                {{ alerts.high || 0 }} high
                            </span>

                            <span
                                class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400"
                            >
                                {{ alerts.medium || 0 }} medium
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-3">
                        <OperationalAlertCard
                            v-for="(alert, index) in alerts.items || []"
                            :key="`${alert.reference_code}-${alert.type}-${index}`"
                            :alert="alert"
                        />

                        <div
                            v-if="!(alerts.items || []).length"
                            class="col-span-full rounded-xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700"
                        >
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                            >
                                ✓
                            </div>

                            <p
                                class="mt-3 text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                No operational alerts
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Current source data does not contain any
                                detected operational issues.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>