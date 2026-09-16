<script setup>
import { computed, reactive, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    matches: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
            links: [],
        }),
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            confidence: '',
            min_score: '',
            max_score: '',
            date_from: '',
            date_to: '',
            sort: 'score_desc',
            per_page: 20,
        }),
    },

    statistics: {
        type: Object,
        default: () => ({
            total: 0,
            recommended: 0,
            reviewed: 0,
            approved: 0,
            rejected: 0,
            high_confidence: 0,
            average_score: 0,
            approval_rate: 0,
        }),
    },
})

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    confidence: props.filters.confidence ?? '',
    min_score: props.filters.min_score ?? '',
    max_score: props.filters.max_score ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    sort: props.filters.sort ?? 'score_desc',
    per_page: Number(props.filters.per_page ?? 20),
})

const selectedIds = ref([])
const processing = ref(false)
const showFilters = ref(false)
const showBulkModal = ref(false)
const bulkAction = ref('approve')
const bulkReason = ref('')

const allCurrentPageSelected = computed(() => {
    if (!props.matches.data.length) {
        return false
    }

    return props.matches.data.every(
        match => selectedIds.value.includes(match.id)
    )
})

const selectedCount = computed(
    () => selectedIds.value.length
)

const scoreClass = (score) => {
    const value = Number(score)

    if (value >= 85) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (value >= 70) {
        return 'text-blue-600 dark:text-blue-400'
    }

    if (value >= 50) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-rose-600 dark:text-rose-400'
}

const confidenceClass = (confidence) => {
    if (confidence === 'high') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
    }

    if (confidence === 'medium') {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400'
    }

    return 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'
}

const statusClass = (status) => {
    const classes = {
        recommended:
            'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',

        reviewed:
            'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400',

        approved:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',

        rejected:
            'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
    }

    return classes[status] ?? classes.recommended
}

const formatStatus = (status) => {
    if (!status) {
        return ''
    }

    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
}

const applyFilters = () => {
    router.get(
        route('admin.matches.index'),
        {
            ...filters,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const resetFilters = () => {
    Object.assign(filters, {
        search: '',
        status: '',
        confidence: '',
        min_score: '',
        max_score: '',
        date_from: '',
        date_to: '',
        sort: 'score_desc',
        per_page: 20,
    })

    applyFilters()
}

const goToPage = (url) => {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    })
}

const toggleSelection = (id) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(
            selectedId => selectedId !== id
        )

        return
    }

    selectedIds.value.push(id)
}

const toggleCurrentPage = () => {
    if (allCurrentPageSelected.value) {
        const currentIds = props.matches.data.map(
            match => match.id
        )

        selectedIds.value = selectedIds.value.filter(
            id => !currentIds.includes(id)
        )

        return
    }

    const currentIds = props.matches.data.map(
        match => match.id
    )

    selectedIds.value = [
        ...new Set([
            ...selectedIds.value,
            ...currentIds,
        ]),
    ]
}

const openBulkAction = (action) => {
    if (!selectedCount.value) {
        return
    }

    bulkAction.value = action
    bulkReason.value = ''
    showBulkModal.value = true
}

const submitBulkAction = () => {
    if (
        !selectedCount.value
        || bulkReason.value.trim().length < 5
    ) {
        return
    }

    processing.value = true

    router.post(
        route('admin.matches.bulk'),
        {
            action: bulkAction.value,
            match_ids: selectedIds.value,
            reason: bulkReason.value.trim(),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = []
                showBulkModal.value = false
                bulkReason.value = ''
            },
            onFinish: () => {
                processing.value = false
            },
        }
    )
}

const exportMatches = () => {
    const params = new URLSearchParams()

    Object.entries(filters).forEach(
        ([key, value]) => {
            if (
                value !== ''
                && value !== null
                && value !== undefined
            ) {
                params.append(key, value)
            }
        }
    )

    window.location.href =
        `${route('admin.matches.export')}?${params.toString()}`
}
</script>

<template>
    <Head title="Match Review" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-sm font-medium text-indigo-600 dark:text-indigo-400">
                            <span class="h-2 w-2 rounded-full bg-current"></span>
                            Source X Operations
                        </div>

                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            Match Review
                        </h1>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Review, filter and manage source matching decisions.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click="showFilters = !showFilters"
                        >
                            {{ showFilters ? 'Hide Filters' : 'Advanced Filters' }}
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click="exportMatches"
                        >
                            Export CSV
                        </button>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Total Matches
                        </p>

                        <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">
                            {{ statistics.total }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ statistics.high_confidence }} high-confidence
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Average Score
                        </p>

                        <p
                            :class="scoreClass(statistics.average_score)"
                            class="mt-3 text-3xl font-bold"
                        >
                            {{ statistics.average_score }}%
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Across all matches
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Approved
                        </p>

                        <p class="mt-3 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ statistics.approved }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ statistics.approval_rate }}% approval rate
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Needs Review
                        </p>

                        <p class="mt-3 text-3xl font-bold text-violet-600 dark:text-violet-400">
                            {{ statistics.reviewed + statistics.recommended }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Recommended + reviewed
                        </p>
                    </div>
                </div>

                <!-- Filters -->
                <section
                    v-if="showFilters"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Search
                            </label>

                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Request reference, source reference or name..."
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </label>

                            <select
                                v-model="filters.status"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">
                                    All statuses
                                </option>

                                <option value="recommended">
                                    Recommended
                                </option>

                                <option value="reviewed">
                                    Reviewed
                                </option>

                                <option value="approved">
                                    Approved
                                </option>

                                <option value="rejected">
                                    Rejected
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Confidence
                            </label>

                            <select
                                v-model="filters.confidence"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="">
                                    All confidence
                                </option>

                                <option value="high">
                                    High
                                </option>

                                <option value="medium">
                                    Medium
                                </option>

                                <option value="low">
                                    Low
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Minimum Score
                            </label>

                            <input
                                v-model="filters.min_score"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="0"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Maximum Score
                            </label>

                            <input
                                v-model="filters.max_score"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="100"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                From
                            </label>

                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                To
                            </label>

                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Sort
                            </label>

                            <select
                                v-model="filters.sort"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="score_desc">
                                    Highest Score
                                </option>

                                <option value="score_asc">
                                    Lowest Score
                                </option>

                                <option value="rank_asc">
                                    Best Rank
                                </option>

                                <option value="rank_desc">
                                    Lowest Rank
                                </option>

                                <option value="newest">
                                    Newest
                                </option>

                                <option value="oldest">
                                    Oldest
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2 lg:col-span-3">
                            <button
                                type="button"
                                class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                                @click="applyFilters"
                            >
                                Apply Filters
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                @click="resetFilters"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Bulk Toolbar -->
                <div
                    v-if="selectedCount"
                    class="sticky top-4 z-20 flex flex-col gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between dark:border-indigo-500/30 dark:bg-indigo-500/10"
                >
                    <div>
                        <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                            {{ selectedCount }} match(es) selected
                        </p>

                        <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-300">
                            Choose an action to update the selected matches.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-emerald-700"
                            @click="openBulkAction('approve')"
                        >
                            Approve
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-violet-700"
                            @click="openBulkAction('review')"
                        >
                            Mark Reviewed
                        </button>

                        <button
                            type="button"
                            class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-rose-700"
                            @click="openBulkAction('reject')"
                        >
                            Reject
                        </button>

                        <button
                            type="button"
                            class="rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-indigo-500/30 dark:bg-slate-900 dark:text-slate-200"
                            @click="selectedIds = []"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                        <div>
                            <h2 class="font-semibold text-slate-900 dark:text-white">
                                Match Results
                            </h2>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Showing {{ matches.from ?? 0 }}–{{ matches.to ?? 0 }}
                                of {{ matches.total ?? 0 }}
                            </p>
                        </div>

                        <select
                            v-model="filters.per_page"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                            @change="applyFilters"
                        >
                            <option :value="10">
                                10 / page
                            </option>

                            <option :value="20">
                                20 / page
                            </option>

                            <option :value="30">
                                30 / page
                            </option>

                            <option :value="50">
                                50 / page
                            </option>

                            <option :value="100">
                                100 / page
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="matches.data?.length"
                        class="overflow-x-auto"
                    >
                        <table class="min-w-[1050px] w-full">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="w-12 px-5 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="allCurrentPageSelected"
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            @change="toggleCurrentPage"
                                        />
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Match
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Request
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Source
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Score
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Confidence
                                    </th>

                                    <th class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Status
                                    </th>

                                    <th class="px-5 py-4 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr
                                    v-for="match in matches.data"
                                    :key="match.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                                >
                                    <td class="px-5 py-4">
                                        <input
                                            type="checkbox"
                                            :checked="selectedIds.includes(match.id)"
                                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                            @change="toggleSelection(match.id)"
                                        />
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-xs font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                                #{{ match.rank }}
                                            </div>

                                            <div>
                                                <p class="font-semibold text-slate-900 dark:text-white">
                                                    Match #{{ match.id }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-400">
                                                    {{ match.created_at }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <Link
                                            :href="route('admin.matches.show', match.buyer_request_id)"
                                            class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                                        >
                                            {{ match.buyer_request?.reference_code }}
                                        </Link>

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ match.buyer_request?.user?.name }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ match.buyer_request?.location }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-900 dark:text-white">
                                            {{ match.source?.reference_code }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            {{ match.source?.name || 'Internal source' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span
                                                :class="scoreClass(match.total_score)"
                                                class="text-xl font-bold"
                                            >
                                                {{ Number(match.total_score).toFixed(1) }}
                                            </span>

                                            <span class="text-xs text-slate-400">
                                                / 100
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            :class="confidenceClass(match.confidence)"
                                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        >
                                            {{ formatStatus(match.confidence) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            <span
                                                :class="statusClass(match.status)"
                                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                            >
                                                {{ formatStatus(match.status) }}
                                            </span>

                                            <span
                                                v-if="match.is_admin_selected"
                                                class="rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-semibold text-violet-700 dark:bg-violet-500/10 dark:text-violet-400"
                                            >
                                                Admin Selected
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <Link
                                            :href="route('admin.matches.show', match.buyer_request_id)"
                                            class="inline-flex rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                        >
                                            Review
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty -->
                    <div
                        v-else
                        class="px-6 py-16 text-center"
                    >
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl dark:bg-slate-800">
                            ◎
                        </div>

                        <h3 class="mt-5 font-semibold text-slate-900 dark:text-white">
                            No match results found
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">
                            No matches match your current search and filter criteria.
                        </p>

                        <button
                            type="button"
                            class="mt-5 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                            @click="resetFilters"
                        >
                            Reset Filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="matches.last_page > 1"
                        class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Page {{ matches.current_page }}
                            of {{ matches.last_page }}
                        </p>

                        <div class="flex flex-wrap gap-1">
                            <template
                                v-for="(link, index) in matches.links"
                                :key="index"
                            >
                                <button
                                    v-if="link.url"
                                    type="button"
                                    :disabled="link.active"
                                    class="rounded-lg px-3 py-2 text-xs font-medium transition"
                                    :class="link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
                                    @click="goToPage(link.url)"
                                >
                                    <span v-html="link.label"></span>
                                </button>

                                <span
                                    v-else
                                    class="rounded-lg px-3 py-2 text-xs text-slate-300 dark:text-slate-600"
                                >
                                    <span v-html="link.label"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Bulk Action Modal -->
        <div
            v-if="showBulkModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="showBulkModal = false"
        >
            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            {{ formatStatus(bulkAction) }} Matches
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            You are updating {{ selectedCount }} selected match(es).
                        </p>
                    </div>

                    <button
                        type="button"
                        class="text-xl text-slate-400 hover:text-slate-600"
                        @click="showBulkModal = false"
                    >
                        ×
                    </button>
                </div>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Reason
                    </label>

                    <textarea
                        v-model="bulkReason"
                        rows="5"
                        placeholder="Explain why this bulk decision is being made..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    ></textarea>

                    <p class="mt-2 text-xs text-slate-400">
                        Minimum 5 characters. This reason will be stored in the audit log.
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="showBulkModal = false"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="processing || bulkReason.trim().length < 5"
                        class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submitBulkAction"
                    >
                        {{ processing ? 'Processing...' : 'Confirm Decision' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>