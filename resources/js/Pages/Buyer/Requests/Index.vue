<script setup>
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3'
import {
    reactive,
    watch,
} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    requests: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    statuses: {
        type: Array,
        default: () => [],
    },
})

const filters = reactive({
    search:
        props.filters.search ?? '',

    status:
        props.filters.status ?? '',
})

let searchTimer = null

function applyFilters() {
    router.get(
        route('buyer.requests.index'),
        {
            search:
                filters.search || undefined,

            status:
                filters.status || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

function search() {
    clearTimeout(searchTimer)

    searchTimer = setTimeout(
        applyFilters,
        400
    )
}

function clearFilters() {
    filters.search = ''
    filters.status = ''

    applyFilters()
}

function deleteRequest(request) {
    if (
        !window.confirm(
            `Delete ${request.reference_code}?`
        )
    ) {
        return
    }

    router.delete(
        route(
            'buyer.requests.destroy',
            request.id
        ),
        {
            preserveScroll: true,
        }
    )
}

function badgeClass(status) {
    const classes = {
        draft:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',

        submitted:
            'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',

        processing:
            'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',

        matched:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',

        completed:
            'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300',

        cancelled:
            'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
    }

    return classes[status] ?? classes.draft
}

function label(value) {
    return String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char =>
            char.toUpperCase()
        )
}

watch(
    () => filters.status,
    () => {
        applyFilters()
    }
)
</script>

<template>
    <AppLayout>
        <Head title="My Sourcing Requests" />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Buyer / Requests
                    </p>

                    <h1
                        class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        My Requests
                    </h1>

                    <p
                        class="mt-2 text-slate-500 dark:text-slate-400"
                    >
                        Track your sourcing requests and matching progress.
                    </p>
                </div>

                <Link
                    :href="route('buyer.requests.create')"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                >
                    + New Request
                </Link>
            </div>

            <!-- Filters -->
            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="grid grid-cols-1 gap-3 md:grid-cols-3"
                >
                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Search request..."
                        class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        @input="search"
                    />

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
                            {{ label(status) }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Desktop -->
            <div
                class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:block"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead
                            class="bg-slate-50 dark:bg-slate-800/60"
                        >
                            <tr>
                                <th class="table-heading">
                                    Request
                                </th>

                                <th class="table-heading">
                                    Products
                                </th>

                                <th class="table-heading">
                                    Location
                                </th>

                                <th class="table-heading">
                                    Budget
                                </th>

                                <th class="table-heading">
                                    Status
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
                                v-for="request in requests.data"
                                :key="request.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <td class="table-cell">
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            request.reference_code
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{
                                            request.required_by ||
                                            'No required date'
                                        }}
                                    </p>
                                </td>

                                <td class="table-cell">
                                    {{
                                        request.items?.length ||
                                        0
                                    }}
                                </td>

                                <td class="table-cell">
                                    {{
                                        request.location
                                    }}
                                </td>

                                <td class="table-cell">
                                    <span
                                        v-if="
                                            request.min_budget !== null ||
                                            request.max_budget !== null
                                        "
                                    >
                                        {{
                                            request.min_budget ??
                                            '—'
                                        }}
                                        -
                                        {{
                                            request.max_budget ??
                                            '—'
                                        }}
                                    </span>

                                    <span
                                        v-else
                                        class="text-slate-400"
                                    >
                                        Flexible
                                    </span>
                                </td>

                                <td class="table-cell">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                                            badgeClass(
                                                request.status
                                            ),
                                        ]"
                                    >
                                        {{
                                            label(
                                                request.status
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="table-cell">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="route('buyer.requests.show', request.id)"
                                            class="action-button"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            v-if="
                                                ['draft', 'submitted'].includes(
                                                    request.status
                                                )
                                            "
                                            :href="route('buyer.requests.edit', request.id)"
                                            class="action-button"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            v-if="
                                                ['draft', 'cancelled'].includes(
                                                    request.status
                                                )
                                            "
                                            type="button"
                                            class="action-button text-red-600"
                                            @click="deleteRequest(request)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="!requests.data?.length"
                            >
                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center text-sm text-slate-500"
                                >
                                    You have no sourcing requests yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile -->
            <div class="space-y-4 lg:hidden">
                <article
                    v-for="request in requests.data"
                    :key="request.id"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="flex items-start justify-between gap-3"
                    >
                        <div>
                            <h2
                                class="font-bold text-slate-900 dark:text-white"
                            >
                                {{
                                    request.reference_code
                                }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{
                                    request.location
                                }}
                            </p>
                        </div>

                        <span
                            :class="[
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                badgeClass(
                                    request.status
                                ),
                            ]"
                        >
                            {{
                                label(
                                    request.status
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="mt-5 grid grid-cols-2 gap-4 text-sm"
                    >
                        <div>
                            <p class="text-slate-500">
                                Products
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    request.items?.length ||
                                    0
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-500">
                                Required By
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    request.required_by ||
                                    'Flexible'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex gap-2 border-t border-slate-200 pt-4 dark:border-slate-700"
                    >
                        <Link
                            :href="route('buyer.requests.show', request.id)"
                            class="action-button flex-1 text-center"
                        >
                            View
                        </Link>

                        <Link
                            v-if="
                                ['draft', 'submitted'].includes(
                                    request.status
                                )
                            "
                            :href="route('buyer.requests.edit', request.id)"
                            class="action-button flex-1 text-center"
                        >
                            Edit
                        </Link>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div
                v-if="requests.links?.length > 3"
                class="mt-6 flex flex-wrap justify-center gap-2"
            >
                <template
                    v-for="(link, index) in requests.links"
                    :key="index"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-lg border px-3 py-2 text-sm"
                        :class="
                            link.active
                                ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900'
                                : 'border-slate-300 bg-white text-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300'
                        "
                        v-html="link.label"
                    />

                    <span
                        v-else
                        class="rounded-lg border px-3 py-2 text-sm text-slate-400"
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
