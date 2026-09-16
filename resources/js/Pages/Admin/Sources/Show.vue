<script setup>
import {
    Head,
    Link,
} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    source: {
        type: Object,
        required: true,
    },
})

function formatLabel(value) {
    return String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter =>
            letter.toUpperCase()
        )
}

function statusClass(status) {
    const classes = {
        active:
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',

        verified:
            'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',

        pending:
            'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',

        rejected:
            'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',

        expired:
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',

        inactive:
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',

        suspended:
            'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
    }

    return classes[status] ?? classes.inactive
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
</script>

<template>
    <AppLayout>
        <Head
            :title="source.reference_code"
        />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <!-- Header -->
            <div
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        Admin / Sources /
                        {{ source.reference_code }}
                    </div>

                    <h1
                        class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{
                            source.name ||
                            'Unnamed Source'
                        }}
                    </h1>

                    <div
                        class="mt-3 flex flex-wrap gap-2"
                    >
                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-semibold',
                                statusClass(
                                    source.status
                                ),
                            ]"
                        >
                            {{
                                formatLabel(
                                    source.status
                                )
                            }}
                        </span>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{
                                formatLabel(
                                    source.type
                                )
                            }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Link
                        :href="route('admin.sources.index')"
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Back
                    </Link>

                    <Link
                        :href="route('admin.sources.edit', source.id)"
                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    >
                        Edit Source
                    </Link>
                </div>
            </div>

            <!-- Score Cards -->
            <div
                class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p class="text-sm text-slate-500">
                        Reference Code
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ source.reference_code }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p class="text-sm text-slate-500">
                        Quality Score
                    </p>

                    <p
                        :class="[
                            'mt-2 text-3xl font-bold',
                            scoreClass(
                                source.quality_score
                            ),
                        ]"
                    >
                        {{ source.quality_score }}
                        <span
                            class="text-sm text-slate-400"
                        >
                            /100
                        </span>
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <p class="text-sm text-slate-500">
                        Performance Score
                    </p>

                    <p
                        :class="[
                            'mt-2 text-3xl font-bold',
                            scoreClass(
                                source.performance_score
                            ),
                        ]"
                    >
                        {{
                            source.performance_score
                        }}
                        <span
                            class="text-sm text-slate-400"
                        >
                            /100
                        </span>
                    </p>
                </div>
            </div>

            <div
                class="grid grid-cols-1 gap-6 lg:grid-cols-3"
            >
                <!-- Verification -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Verification
                    </h2>

                    <div
                        v-if="source.verification"
                        class="mt-5 space-y-4"
                    >
                        <div>
                            <p class="detail-label">
                                Status
                            </p>

                            <span
                                :class="[
                                    'inline-block rounded-full px-3 py-1 text-xs font-semibold',
                                    statusClass(
                                        source.verification.status
                                    ),
                                ]"
                            >
                                {{
                                    formatLabel(
                                        source.verification.status
                                    )
                                }}
                            </span>
                        </div>

                        <div>
                            <p class="detail-label">
                                Method
                            </p>

                            <p class="detail-value">
                                {{
                                    formatLabel(
                                        source.verification.verification_type
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Verified By
                            </p>

                            <p class="detail-value">
                                {{
                                    source.verification.verified_by ||
                                    'Not verified'
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Notes
                            </p>

                            <p
                                class="text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    source.verification.notes ||
                                    'No verification notes.'
                                }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-else
                        class="mt-5 text-sm text-slate-500"
                    >
                        No verification record.
                    </p>
                </section>

                <!-- Location -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Location
                    </h2>

                    <div
                        v-if="source.locations?.length"
                        class="mt-5 space-y-3"
                    >
                        <div
                            v-for="(location, index) in source.locations"
                            :key="index"
                            class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    [
                                        location.area,
                                        location.city,
                                        location.district,
                                        location.division,
                                        location.country,
                                    ]
                                        .filter(Boolean)
                                        .join(', ')
                                }}
                            </p>

                            <p
                                v-if="location.latitude && location.longitude"
                                class="mt-2 text-xs text-slate-500"
                            >
                                Coordinates:
                                {{
                                    location.latitude
                                }},
                                {{
                                    location.longitude
                                }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-else
                        class="mt-5 text-sm text-slate-500"
                    >
                        No location information.
                    </p>
                </section>

                <!-- Performance -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Performance
                    </h2>

                    <div
                        v-if="source.performance"
                        class="mt-5 grid grid-cols-2 gap-4"
                    >
                        <div>
                            <p class="detail-label">
                                Total Orders
                            </p>

                            <p class="detail-value">
                                {{
                                    source.performance.total_orders
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Completed
                            </p>

                            <p class="detail-value">
                                {{
                                    source.performance.completed_orders
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Cancelled
                            </p>

                            <p class="detail-value">
                                {{
                                    source.performance.cancelled_orders
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Late
                            </p>

                            <p class="detail-value">
                                {{
                                    source.performance.late_orders
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Rating
                            </p>

                            <p class="detail-value">
                                {{
                                    source.performance.rating
                                }}/100
                            </p>
                        </div>

                        <div>
                            <p class="detail-label">
                                Score
                            </p>

                            <p
                                :class="[
                                    'font-bold',
                                    scoreClass(
                                        source.performance.performance_score
                                    ),
                                ]"
                            >
                                {{
                                    source.performance.performance_score
                                }}/100
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Products -->
            <section
                class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 p-6 dark:border-slate-700"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Assigned Products
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Products currently associated with this source.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead
                            class="bg-slate-50 dark:bg-slate-800/60"
                        >
                            <tr>
                                <th class="table-heading">
                                    Product
                                </th>

                                <th class="table-heading">
                                    Price
                                </th>

                                <th class="table-heading">
                                    MOQ
                                </th>

                                <th class="table-heading">
                                    Quality
                                </th>

                                <th class="table-heading">
                                    Availability
                                </th>

                                <th class="table-heading">
                                    Quantity
                                </th>

                                <th class="table-heading">
                                    Dates
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-700"
                        >
                            <tr
                                v-for="product in source.products"
                                :key="product.product_id"
                            >
                                <td class="table-cell">
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            product.product_name
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{
                                            product.category_name
                                        }}
                                    </p>
                                </td>

                                <td class="table-cell font-semibold">
                                    {{ product.price }}
                                </td>

                                <td class="table-cell">
                                    {{
                                        product.minimum_order_quantity
                                    }}
                                    {{ product.unit }}
                                </td>

                                <td class="table-cell">
                                    <div
                                        class="font-semibold"
                                    >
                                        {{
                                            formatLabel(
                                                product.quality_grade
                                            )
                                        }}
                                    </div>

                                    <div
                                        :class="[
                                            'text-xs font-bold',
                                            scoreClass(
                                                product.quality_score
                                            ),
                                        ]"
                                    >
                                        {{
                                            product.quality_score
                                        }}/100
                                    </div>
                                </td>

                                <td class="table-cell">
                                    <span
                                        :class="[
                                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                                            product.is_available
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                                : 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300',
                                        ]"
                                    >
                                        {{
                                            product.is_available
                                                ? 'Available'
                                                : 'Unavailable'
                                        }}
                                    </span>
                                </td>

                                <td class="table-cell">
                                    {{
                                        product.available_quantity
                                    }}
                                    {{ product.unit }}
                                </td>

                                <td class="table-cell text-xs">
                                    <div>
                                        {{
                                            product.available_from ||
                                            '—'
                                        }}
                                    </div>

                                    <div
                                        class="mt-1 text-slate-400"
                                    >
                                        {{
                                            product.available_until ||
                                            '—'
                                        }}
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="!source.products?.length"
                            >
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    No products assigned.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Internal Notes -->
            <section
                class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
            >
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Internal Notes
                </h2>

                <p
                    class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                >
                    {{
                        source.internal_notes ||
                        'No internal notes available.'
                    }}
                </p>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.detail-label {
    @apply mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400;
}

.detail-value {
    @apply text-sm font-semibold text-slate-900 dark:text-white;
}

.table-heading {
    @apply px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400;
}

.table-cell {
    @apply px-5 py-4 text-sm text-slate-700 dark:text-slate-300;
}
</style>
