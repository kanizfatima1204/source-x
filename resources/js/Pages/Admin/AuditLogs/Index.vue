<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        required: true,
    },

    actions: {
        type: Array,
        default: () => [],
    },
})

const search = ref(props.filters.search || '')
const action = ref(props.filters.action || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const loading = ref(false)

const applyFilters = () => {
    loading.value = true

    router.get(
        route('admin.audit-logs.index'),
        {
            search: search.value,
            action: action.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

const resetFilters = () => {
    search.value = ''
    action.value = ''
    dateFrom.value = ''
    dateTo.value = ''

    loading.value = true

    router.get(
        route('admin.audit-logs.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

const actionClass = action => {
    const classes = {
        select:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',

        approve:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',

        reject:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',

        change_rank:
            'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
    }

    return classes[action] || classes.select
}

const actionLabel = action => {
    return String(action || '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

const statusClass = status => {
    const classes = {
        recommended:
            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',

        reviewed:
            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',

        approved:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',

        rejected:
            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    }

    return classes[status] || classes.reviewed
}

const visitPage = url => {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<template>
    <AppLayout title="Audit Logs">
        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <div
                class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
            >
                <!-- Header -->
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-400"
                        >
                            Administration
                        </p>

                        <h1
                            class="mt-1 text-3xl font-black tracking-tight text-slate-950 dark:text-white"
                        >
                            Audit Logs
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400"
                        >
                            Review every manual matching decision made by
                            administrators.
                        </p>
                    </div>

                    <a
                        :href="route('admin.dashboard')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        ← Analytics Dashboard
                    </a>
                </div>

                <!-- Filters -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="grid gap-3 lg:grid-cols-[1.5fr_1fr_1fr_1fr_auto_auto]"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500"
                            >
                                Search
                            </label>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Request, source, admin, reason..."
                                class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500"
                            >
                                Action
                            </label>

                            <select
                                v-model="action"
                                class="w-full rounded-xl border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            >
                                <option
                                    v-for="item in actions"
                                    :key="item.value"
                                    :value="item.value"
                                >
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>

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
                            class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50 dark:bg-white dark:text-slate-950"
                            @click="applyFilters"
                        >
                            {{ loading ? 'Loading...' : 'Filter' }}
                        </button>

                        <button
                            type="button"
                            :disabled="loading"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 dark:border-slate-700 dark:text-slate-300"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </section>

                <!-- Summary -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Total Decisions
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-slate-950 dark:text-white"
                        >
                            {{ logs.total }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Current Page
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-violet-600 dark:text-violet-400"
                        >
                            {{ logs.current_page }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Per Page
                        </p>

                        <p
                            class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-400"
                        >
                            {{ logs.per_page }}
                        </p>
                    </div>
                </div>

                <!-- Desktop Table -->
                <section
                    class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block"
                >
                    <div
                        class="overflow-x-auto"
                    >
                        <table class="min-w-full">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 dark:border-slate-800"
                                >
                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Decision
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Request
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Source
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Match
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Admin
                                    </th>

                                    <th
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Time
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="log in logs.data"
                                    :key="log.id"
                                    class="transition hover:bg-slate-50 dark:hover:bg-slate-950"
                                >
                                    <td class="px-5 py-4 align-top">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"
                                            :class="actionClass(log.action)"
                                        >
                                            {{ actionLabel(log.action) }}
                                        </span>

                                        <p
                                            class="mt-2 max-w-xs text-xs leading-5 text-slate-500"
                                        >
                                            {{ log.reason }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        <span
                                            class="font-mono text-sm font-black text-slate-950 dark:text-white"
                                        >
                                            {{
                                                log.request.reference_code ||
                                                '—'
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        <span
                                            class="font-mono text-sm text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                log.match.source_reference ||
                                                '—'
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="font-black text-slate-950 dark:text-white"
                                            >
                                                {{
                                                    log.match.score ?? '—'
                                                }}
                                            </span>

                                            <span
                                                v-if="log.match.status"
                                                class="rounded-full px-2 py-1 text-[10px] font-bold"
                                                :class="
                                                    statusClass(
                                                        log.match.status
                                                    )
                                                "
                                            >
                                                {{
                                                    log.match.status
                                                }}
                                            </span>
                                        </div>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Rank:
                                            {{
                                                log.match.rank ?? '—'
                                            }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        <p
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ log.admin.name || 'Admin' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ log.admin.email || '' }}
                                        </p>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 align-top text-xs text-slate-500"
                                    >
                                        {{ log.created_at }}
                                    </td>
                                </tr>

                                <tr v-if="logs.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-5 py-14 text-center"
                                    >
                                        <div
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl dark:bg-slate-800"
                                        >
                                            ◎
                                        </div>

                                        <p
                                            class="mt-4 font-bold text-slate-900 dark:text-white"
                                        >
                                            No audit records found
                                        </p>

                                        <p
                                            class="mt-1 text-sm text-slate-500"
                                        >
                                            Try changing your search or filters.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Mobile Cards -->
                <section class="space-y-3 lg:hidden">
                    <article
                        v-for="log in logs.data"
                        :key="log.id"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between gap-3"
                        >
                            <div>
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"
                                    :class="actionClass(log.action)"
                                >
                                    {{ actionLabel(log.action) }}
                                </span>

                                <p
                                    class="mt-2 font-mono text-sm font-black text-slate-950 dark:text-white"
                                >
                                    {{
                                        log.request.reference_code ||
                                        'Unknown Request'
                                    }}
                                </p>
                            </div>

                            <span
                                class="font-black text-slate-950 dark:text-white"
                            >
                                {{ log.match.score ?? '—' }}
                            </span>
                        </div>

                        <div
                            class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-950"
                        >
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Source
                                </p>

                                <p
                                    class="mt-1 font-mono text-xs font-bold text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        log.match.source_reference ||
                                        '—'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Rank
                                </p>

                                <p
                                    class="mt-1 text-xs font-bold text-slate-700 dark:text-slate-300"
                                >
                                    {{ log.match.rank ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <p
                            class="mt-3 text-xs leading-5 text-slate-500"
                        >
                            {{ log.reason }}
                        </p>

                        <div
                            class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                >
                                    {{ log.admin.name || 'Admin' }}
                                </p>

                                <p class="text-[10px] text-slate-400">
                                    {{ log.created_at }}
                                </p>
                            </div>

                            <span
                                v-if="log.match.status"
                                class="rounded-full px-2 py-1 text-[10px] font-bold"
                                :class="statusClass(log.match.status)"
                            >
                                {{ log.match.status }}
                            </span>
                        </div>
                    </article>

                    <div
                        v-if="logs.data.length === 0"
                        class="rounded-2xl border border-slate-200 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900"
                    >
                        <p
                            class="font-bold text-slate-900 dark:text-white"
                        >
                            No audit records found
                        </p>
                    </div>
                </section>

                <!-- Pagination -->
                <div
                    v-if="logs.links?.length > 3"
                    class="flex flex-wrap justify-center gap-2"
                >
                    <button
                        v-for="link in logs.links"
                        :key="link.label"
                        type="button"
                        :disabled="!link.url || link.active"
                        class="rounded-xl border px-3 py-2 text-xs font-bold transition"
                        :class="
                            link.active
                                ? 'border-slate-950 bg-slate-950 text-white dark:border-white dark:bg-white dark:text-slate-950'
                                : link.url
                                    ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300'
                                    : 'cursor-not-allowed border-slate-100 text-slate-300 dark:border-slate-800 dark:text-slate-700'
                        "
                        @click="visitPage(link.url)"
                        v-html="link.label"
                    ></button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>