<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentRequests: {
        type: Array,
        default: () => [],
    },
    recentMatches: {
        type: Array,
        default: () => [],
    },
})

const pipeline = computed(() => [
    {
        label: 'Draft',
        value: props.stats.draft_requests ?? 0,
        icon: '◌',
    },
    {
        label: 'Submitted',
        value: props.stats.submitted_requests ?? 0,
        icon: '↑',
    },
    {
        label: 'Matched',
        value: props.stats.matched_requests ?? 0,
        icon: '✓',
    },
    {
        label: 'Cancelled',
        value: props.stats.cancelled_requests ?? 0,
        icon: '×',
    },
])

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        submitted: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        matched: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
        recommended: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        reviewed: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    }

    return classes[status] ?? classes.draft
}

const formatStatus = (status) => {
    return String(status ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase())
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">

                <!-- Welcome -->
                <section class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-violet-500/20 blur-3xl" />
                    <div class="absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl" />

                    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-medium text-slate-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                Source X sourcing platform
                            </div>

                            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                                Find the right source for what you need.
                            </h1>

                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                                Submit your sourcing requirements and let Source X identify suitable verified sources using product, price, quality, availability and performance signals.
                            </p>
                        </div>

                        <Link
                            :href="route('buyer.requests.create')"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 shadow-lg transition hover:bg-slate-100"
                        >
                            <span class="text-lg">+</span>
                            New Sourcing Request
                        </Link>
                    </div>
                </section>

                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="item in pipeline"
                        :key="item.label"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                    {{ item.label }} Requests
                                </p>

                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ item.value }}
                                </p>
                            </div>

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ item.icon }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matching summary -->
                <div class="grid gap-6 lg:grid-cols-3">

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Total Matches
                        </p>

                        <div class="mt-3 flex items-end gap-3">
                            <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ stats.total_matches }}
                            </p>

                            <span class="mb-1 text-xs text-slate-400">
                                generated
                            </span>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-violet-500"
                                :style="{
                                    width: stats.total_matches
                                        ? `${Math.min((stats.approved_matches / stats.total_matches) * 100, 100)}%`
                                        : '0%'
                                }"
                            />
                        </div>

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            {{ stats.approved_matches }} approved match{{ stats.approved_matches === 1 ? '' : 'es' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Recommended Matches
                        </p>

                        <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                            {{ stats.recommended_matches }}
                        </p>

                        <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Sources currently available for review based on your requests.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            Verified Source Network
                        </p>

                        <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                            {{ stats.verified_sources }}
                        </p>

                        <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Active verified sources available across the Source X network.
                        </p>
                    </div>
                </div>

                <!-- Requests -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-white">
                                My Recent Requests
                            </h2>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Track your sourcing requests and their progress.
                            </p>
                        </div>

                        <Link
                            :href="route('buyer.requests.index')"
                            class="text-sm font-semibold text-violet-600 hover:text-violet-700 dark:text-violet-400"
                        >
                            View all requests →
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="request in recentRequests"
                            :key="request.id"
                            class="flex flex-col gap-4 px-6 py-5 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between dark:hover:bg-slate-800/40"
                        >
                            <div>
                                <Link
                                    :href="route('buyer.requests.show', request.id)"
                                    class="font-semibold text-slate-900 hover:text-violet-600 dark:text-white dark:hover:text-violet-400"
                                >
                                    {{ request.reference_code }}
                                </Link>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ request.location }}</span>
                                    <span>{{ request.items_count }} item{{ request.items_count === 1 ? '' : 's' }}</span>

                                    <span v-if="request.required_by">
                                        Required by {{ request.required_by }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="getStatusClass(request.status)"
                                >
                                    {{ formatStatus(request.status) }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    {{ request.created_at }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!recentRequests.length"
                            class="px-6 py-12 text-center"
                        >
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl dark:bg-slate-800">
                                ▤
                            </div>

                            <p class="mt-4 font-medium text-slate-900 dark:text-white">
                                No sourcing requests yet
                            </p>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Create your first request to start finding verified sources.
                            </p>

                            <Link
                                :href="route('buyer.requests.create')"
                                class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
                            >
                                Create Request
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Matches -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            Recent Match Results
                        </h2>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Your latest source matching activity.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="match in recentMatches"
                            :key="match.id"
                            class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                    ◎
                                </div>

                                <div>
                                    <Link
                                        :href="route('buyer.requests.show', recentRequests.find(r => r.reference_code === match.reference_code)?.id ?? '#')"
                                        class="font-semibold text-slate-900 hover:text-violet-600 dark:text-white"
                                    >
                                        {{ match.reference_code }}
                                    </Link>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Match generated {{ match.created_at }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="font-bold text-slate-900 dark:text-white">
                                        {{ match.score.toFixed(1) }}
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        {{ formatStatus(match.confidence) }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="getStatusClass(match.status)"
                                >
                                    {{ formatStatus(match.status) }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!recentMatches.length"
                            class="px-6 py-12 text-center text-sm text-slate-500"
                        >
                            Matching results will appear here after a request is submitted.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
