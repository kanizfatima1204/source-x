<script setup>
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3'
import { reactive, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sources: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    stats: {
        type: Object,
        default: () => ({}),
    },

    sourceTypes: {
        type: Array,
        default: () => [],
    },

    statuses: {
        type: Array,
        default: () => [],
    },

    verificationStatuses: {
        type: Array,
        default: () => [],
    },
})

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    type: props.filters.type ?? '',
    verification: props.filters.verification ?? '',
})

let searchTimer = null

function applyFilters() {
    router.get(
        route('admin.sources.index'),
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
            type: filters.type || undefined,
            verification:
                filters.verification || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

function debouncedSearch() {
    clearTimeout(searchTimer)

    searchTimer = setTimeout(() => {
        applyFilters()
    }, 400)
}

function clearFilters() {
    filters.search = ''
    filters.status = ''
    filters.type = ''
    filters.verification = ''

    applyFilters()
}

function deleteSource(source) {
    if (
        !window.confirm(
            `Delete source ${source.reference_code}? This action cannot be undone.`
        )
    ) {
        return
    }

    router.delete(
        route(
            'admin.sources.destroy',
            source.id
        ),
        {
            preserveScroll: true,
        }
    )
}

function badgeClass(status) {
    const classes = {
        active:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',

        inactive:
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',

        suspended:
            'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',

        verified:
            'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',

        pending:
            'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',

        rejected:
            'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',

        expired:
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    }

    return classes[status] ?? classes.inactive
}

function formatLabel(value) {
    return String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter =>
            letter.toUpperCase()
        )
}

function scoreClass(score) {
    if (score >= 80) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (score >= 60) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-red-600 dark:text-red-400'
}

watch(
    () => [
        filters.status,
        filters.type,
        filters.verification,
    ],
    () => {
        applyFilters()
    }
)
</script>

<template>
    <AppLayout>
        <Head title="Source Management" />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <!-- Header -->
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Admin / Source Management
                    </p>

                    <h1
                        class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Sources
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-slate-500 dark:text-slate-400"
                    >
                        Manage verified source profiles used by the intelligent matching engine.
                    </p>
                </div>

                <Link
                    :href="route('admin.sources.create')"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                >
                    + Add Source
                </Link>
            </div>

            <!-- Stats -->
            <div
                class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Total Sources
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ stats.total ?? 0 }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Active
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-emerald-600"
                    >
                        {{ stats.active ?? 0 }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Verified
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-blue-600"
                    >
                        {{ stats.verified ?? 0 }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Pending
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-amber-600"
                    >
                        {{ stats.pending ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5"
                >
                    <div class="lg:col-span-2">
                        <input
                            v-model="filters.search"
                            type="search"
                            placeholder="Search reference code or source name..."
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            @input="debouncedSearch"
                        />
                    </div>

                    <select
                        v-model="filters.status"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">
                            All Statuses
                        </option>

                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ formatLabel(status) }}
                        </option>
                    </select>

                    <select
                        v-model="filters.type"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">
                            All Types
                        </option>

                        <option
                            v-for="type in sourceTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ formatLabel(type) }}
                        </option>
                    </select>

                    <select
                        v-model="filters.verification"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">
                            All Verification
                        </option>

                        <option
                            v-for="status in verificationStatuses"
                            :key="status"
                            :value="status"
                        >
                            {{ formatLabel(status) }}
                        </option>
                    </select>
                </div>

                <button
                    type="button"
                    class="mt-3 text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                    @click="clearFilters"
                >
                    Clear filters
                </button>
            </div>

            <!-- Desktop table -->
            <div
                class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:block"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/60"
                        >
                            <tr>
                                <th class="table-heading">
                                    Source
                                </th>

                                <th class="table-heading">
                                    Type
                                </th>

                                <th class="table-heading">
                                    Status
                                </th>

                                <th class="table-heading">
                                    Verification
                                </th>

                                <th class="table-heading">
                                    Products
                                </th>

                                <th class="table-heading">
                                    Quality
                                </th>

                                <th class="table-heading">
                                    Performance
                                </th>

                                <th class="table-heading text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-700"
                        >
                            <tr
                                v-for="source in sources.data"
                                :key="source.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <td class="table-cell">
                                    <div>
                                        <div
                                            class="font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ source.reference_code }}
                                        </div>

                                        <div
                                            class="mt-1 text-sm text-slate-500"
                                        >
                                            {{
                                                source.name ||
                                                'Unnamed source'
                                            }}
                                        </div>
                                    </div>
                                </td>

                                <td class="table-cell">
                                    {{ formatLabel(source.type) }}
                                </td>

                                <td class="table-cell">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                                            badgeClass(source.status),
                                        ]"
                                    >
                                        {{
                                            formatLabel(
                                                source.status
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="table-cell">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                                            badgeClass(
                                                source.latest_verification?.status
                                            ),
                                        ]"
                                    >
                                        {{
                                            formatLabel(
                                                source.latest_verification?.status ||
                                                'pending'
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="table-cell font-semibold"
                                >
                                    {{ source.products_count }}
                                </td>

                                <td
                                    :class="[
                                        'table-cell font-bold',
                                        scoreClass(
                                            source.quality_score
                                        ),
                                    ]"
                                >
                                    {{ source.quality_score }}/100
                                </td>

                                <td
                                    :class="[
                                        'table-cell font-bold',
                                        scoreClass(
                                            source.performance_score
                                        ),
                                    ]"
                                >
                                    {{
                                        source.performance_score
                                    }}/100
                                </td>

                                <td class="table-cell">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="route('admin.sources.show', source.id)"
                                            class="action-button"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            :href="route('admin.sources.edit', source.id)"
                                            class="action-button"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            type="button"
                                            class="action-button text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                                            @click="deleteSource(source)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="!sources.data?.length"
                            >
                                <td
                                    colspan="8"
                                    class="px-6 py-16 text-center text-sm text-slate-500"
                                >
                                    No sources found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile cards -->
            <div class="space-y-4 lg:hidden">
                <article
                    v-for="source in sources.data"
                    :key="source.id"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="flex items-start justify-between gap-3"
                    >
                        <div>
                            <p
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                {{ source.reference_code }}
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{
                                    source.name ||
                                    'Unnamed source'
                                }}
                            </p>
                        </div>

                        <span
                            :class="[
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                badgeClass(source.status),
                            ]"
                        >
                            {{
                                formatLabel(
                                    source.status
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="mt-5 grid grid-cols-2 gap-3 text-sm"
                    >
                        <div>
                            <p class="text-slate-500">
                                Type
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    formatLabel(
                                        source.type
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Verification
                            </p>

                            <span
                                :class="[
                                    'mt-1 inline-block rounded-full px-2.5 py-1 text-xs font-semibold',
                                    badgeClass(
                                        source.latest_verification?.status
                                    ),
                                ]"
                            >
                                {{
                                    formatLabel(
                                        source.latest_verification?.status ||
                                        'pending'
                                    )
                                }}
                            </span>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Products
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ source.products_count }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Quality
                            </p>

                            <p
                                :class="[
                                    'mt-1 font-bold',
                                    scoreClass(
                                        source.quality_score
                                    ),
                                ]"
                            >
                                {{ source.quality_score }}/100
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Performance
                            </p>

                            <p
                                :class="[
                                    'mt-1 font-bold',
                                    scoreClass(
                                        source.performance_score
                                    ),
                                ]"
                            >
                                {{
                                    source.performance_score
                                }}/100
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex gap-2 border-t border-slate-200 pt-4 dark:border-slate-700"
                    >
                        <Link
                            :href="route('admin.sources.show', source.id)"
                            class="action-button flex-1 text-center"
                        >
                            View
                        </Link>

                        <Link
                            :href="route('admin.sources.edit', source.id)"
                            class="action-button flex-1 text-center"
                        >
                            Edit
                        </Link>

                        <button
                            type="button"
                            class="action-button flex-1 text-red-600"
                            @click="deleteSource(source)"
                        >
                            Delete
                        </button>
                    </div>
                </article>

                <div
                    v-if="!sources.data?.length"
                    class="rounded-2xl border border-slate-200 bg-white p-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900"
                >
                    No sources found.
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="sources.links?.length > 3"
                class="mt-6 flex flex-wrap justify-center gap-2"
            >
                <template
                    v-for="(link, index) in sources.links"
                    :key="index"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-lg border px-3 py-2 text-sm transition"
                        :class="
                            link.active
                                ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900'
                                : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                        "
                        v-html="link.label"
                    />

                    <span
                        v-else
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400 dark:border-slate-700"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.table-heading {
    @apply px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400;
}

.table-cell {
    @apply px-5 py-4 text-sm text-slate-700 dark:text-slate-300;
}

.action-button {
    @apply rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800;
}
</style>
