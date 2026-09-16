<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    products: {
        type: Array,
        default: () => [],
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

    verificationTypes: {
        type: Array,
        default: () => [],
    },

    submitLabel: {
        type: String,
        default: 'Save Source',
    },
})

const emit = defineEmits(['submit'])

const qualityGrades = [
    'economy',
    'standard',
    'premium',
    'luxury',
]

const defaultProductRow = () => ({
    product_id: '',
    price: 0,
    minimum_order_quantity: 1,
    quality_grade: 'standard',
    quality_score: 70,
    is_available: true,
    available_quantity: 0,
    unit: 'piece',
    available_from: '',
    available_until: '',
})

const performanceScore = computed(() => {
    const total = Number(
        props.form.performance?.total_orders || 0
    )

    const completed = Math.min(
        Number(
            props.form.performance?.completed_orders || 0
        ),
        total
    )

    const cancelled = Math.min(
        Number(
            props.form.performance?.cancelled_orders || 0
        ),
        total
    )

    const late = Math.min(
        Number(
            props.form.performance?.late_orders || 0
        ),
        total
    )

    const rating = Math.min(
        Math.max(
            Number(
                props.form.performance?.rating || 0
            ),
            0
        ),
        100
    )

    if (!total) {
        return rating
    }

    const completionRate =
        (completed / total) * 100

    const lateRate =
        (late / total) * 100

    const cancelRate =
        (cancelled / total) * 100

    return Math.round(
        Math.max(
            0,
            Math.min(
                100,
                completionRate * 0.5 +
                rating * 0.3 +
                (100 - lateRate) * 0.1 +
                (100 - cancelRate) * 0.1
            )
        )
    )
})

function addProduct() {
    props.form.products.push(defaultProductRow())
}

function removeProduct(index) {
    if (props.form.products.length <= 1) {
        return
    }

    props.form.products.splice(index, 1)
}

function productChanged(row) {
    const product = props.products.find(
        item => Number(item.id) === Number(row.product_id)
    )

    if (product && (!row.unit || row.unit === 'piece')) {
        row.unit = product.unit || 'piece'
    }
}

function error(field) {
    return props.form.errors?.[field]
}

function productError(index, field) {
    return error(`products.${index}.${field}`)
}

function submit() {
    emit('submit')
}
</script>

<template>
    <form
        class="space-y-6"
        @submit.prevent="submit"
    >
        <!-- Basic Information -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="mb-6">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Basic Information
                </h2>

                <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                    Define the internal source profile.
                </p>
            </div>

            <div
                class="grid grid-cols-1 gap-5 md:grid-cols-2"
            >
                <div class="md:col-span-2">
                    <label class="form-label">
                        Source Name
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        class="form-input"
                        placeholder="e.g. Premium Agricultural Source"
                    />

                    <p
                        v-if="error('name')"
                        class="form-error"
                    >
                        {{ error('name') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Source Type
                    </label>

                    <select
                        v-model="form.type"
                        class="form-input"
                    >
                        <option
                            v-for="type in sourceTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ type.replaceAll('_', ' ') }}
                        </option>
                    </select>

                    <p
                        v-if="error('type')"
                        class="form-error"
                    >
                        {{ error('type') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Status
                    </label>

                    <select
                        v-model="form.status"
                        class="form-input"
                    >
                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </option>
                    </select>

                    <p
                        v-if="error('status')"
                        class="form-error"
                    >
                        {{ error('status') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Quality Score
                    </label>

                    <input
                        v-model.number="form.quality_score"
                        type="number"
                        min="0"
                        max="100"
                        class="form-input"
                    />

                    <p
                        v-if="error('quality_score')"
                        class="form-error"
                    >
                        {{ error('quality_score') }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">
                        Internal Notes
                    </label>

                    <textarea
                        v-model="form.internal_notes"
                        rows="4"
                        class="form-input"
                        placeholder="Internal notes about this source..."
                    ></textarea>

                    <p
                        v-if="error('internal_notes')"
                        class="form-error"
                    >
                        {{ error('internal_notes') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Location -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="mb-6">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Source Location
                </h2>
            </div>

            <div
                class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <label class="form-label">
                        Country
                    </label>

                    <input
                        v-model="form.country"
                        type="text"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Division
                    </label>

                    <input
                        v-model="form.division"
                        type="text"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        District
                    </label>

                    <input
                        v-model="form.district"
                        type="text"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        City
                    </label>

                    <input
                        v-model="form.city"
                        type="text"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Area
                    </label>

                    <input
                        v-model="form.area"
                        type="text"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Latitude
                    </label>

                    <input
                        v-model="form.latitude"
                        type="number"
                        step="any"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Longitude
                    </label>

                    <input
                        v-model="form.longitude"
                        type="number"
                        step="any"
                        class="form-input"
                    />
                </div>
            </div>
        </section>

        <!-- Verification -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="mb-6">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Verification
                </h2>
            </div>

            <div
                class="grid grid-cols-1 gap-5 md:grid-cols-2"
            >
                <div>
                    <label class="form-label">
                        Verification Status
                    </label>

                    <select
                        v-model="form.verification_status"
                        class="form-input"
                    >
                        <option
                            v-for="status in verificationStatuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Verification Type
                    </label>

                    <select
                        v-model="form.verification_type"
                        class="form-input"
                    >
                        <option
                            v-for="type in verificationTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ type }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">
                        Verification Notes
                    </label>

                    <textarea
                        v-model="form.verification_notes"
                        rows="3"
                        class="form-input"
                    ></textarea>
                </div>
            </div>
        </section>

        <!-- Products -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Source Products
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Assign products and define sourcing conditions.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    @click="addProduct"
                >
                    + Add Product
                </button>
            </div>

            <div class="space-y-5">
                <article
                    v-for="(row, index) in form.products"
                    :key="index"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50"
                >
                    <div
                        class="mb-5 flex items-center justify-between"
                    >
                        <h3
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            Product {{ index + 1 }}
                        </h3>

                        <button
                            v-if="form.products.length > 1"
                            type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700"
                            @click="removeProduct(index)"
                        >
                            Remove
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div class="lg:col-span-2">
                            <label class="form-label">
                                Product
                            </label>

                            <select
                                v-model="row.product_id"
                                class="form-input"
                                @change="productChanged(row)"
                            >
                                <option value="">
                                    Select product
                                </option>

                                <option
                                    v-for="product in products"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{ product.category_name }}
                                    —
                                    {{ product.name }}
                                </option>
                            </select>

                            <p
                                v-if="productError(index, 'product_id')"
                                class="form-error"
                            >
                                {{
                                    productError(
                                        index,
                                        'product_id'
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label class="form-label">
                                Price
                            </label>

                            <input
                                v-model.number="row.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Minimum Order Quantity
                            </label>

                            <input
                                v-model.number="row.minimum_order_quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Quality Grade
                            </label>

                            <select
                                v-model="row.quality_grade"
                                class="form-input"
                            >
                                <option
                                    v-for="grade in qualityGrades"
                                    :key="grade"
                                    :value="grade"
                                >
                                    {{ grade }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">
                                Product Quality Score
                            </label>

                            <input
                                v-model.number="row.quality_score"
                                type="number"
                                min="0"
                                max="100"
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Available Quantity
                            </label>

                            <input
                                v-model.number="row.available_quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Unit
                            </label>

                            <input
                                v-model="row.unit"
                                type="text"
                                class="form-input"
                                placeholder="kg, piece..."
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Available From
                            </label>

                            <input
                                v-model="row.available_from"
                                type="date"
                                class="form-input"
                            />
                        </div>

                        <div>
                            <label class="form-label">
                                Available Until
                            </label>

                            <input
                                v-model="row.available_until"
                                type="date"
                                class="form-input"
                            />
                        </div>

                        <div
                            class="flex items-center gap-3 lg:col-span-3"
                        >
                            <input
                                :id="`available-${index}`"
                                v-model="row.is_available"
                                type="checkbox"
                                class="h-5 w-5 rounded border-slate-300"
                            />

                            <label
                                :for="`available-${index}`"
                                class="text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Product is currently available
                            </label>
                        </div>
                    </div>

                    <div
                        v-if="productError(index, 'price') || productError(index, 'product_id')"
                        class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/30 dark:text-red-300"
                    >
                        Please correct the highlighted product fields.
                    </div>
                </article>
            </div>

            <p
                v-if="error('products')"
                class="form-error mt-4"
            >
                {{ error('products') }}
            </p>
        </section>

        <!-- Performance -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Performance Metrics
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        These metrics contribute to source matching.
                    </p>
                </div>

                <div
                    class="rounded-xl bg-slate-100 px-4 py-3 text-center dark:bg-slate-800"
                >
                    <div
                        class="text-xs font-medium uppercase tracking-wide text-slate-500"
                    >
                        Calculated Score
                    </div>

                    <div
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ performanceScore }}
                        <span class="text-sm">/100</span>
                    </div>
                </div>
            </div>

            <div
                class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5"
            >
                <div>
                    <label class="form-label">
                        Total Orders
                    </label>

                    <input
                        v-model.number="form.performance.total_orders"
                        type="number"
                        min="0"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Completed
                    </label>

                    <input
                        v-model.number="form.performance.completed_orders"
                        type="number"
                        min="0"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Cancelled
                    </label>

                    <input
                        v-model.number="form.performance.cancelled_orders"
                        type="number"
                        min="0"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Late Orders
                    </label>

                    <input
                        v-model.number="form.performance.late_orders"
                        type="number"
                        min="0"
                        class="form-input"
                    />
                </div>

                <div>
                    <label class="form-label">
                        Rating
                    </label>

                    <input
                        v-model.number="form.performance.rating"
                        type="number"
                        min="0"
                        max="100"
                        class="form-input"
                    />
                </div>
            </div>
        </section>

        <!-- Actions -->
        <div
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <Link
                :href="route('admin.sources.index')"
                class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                Cancel
            </Link>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
            >
                {{
                    form.processing
                        ? 'Saving...'
                        : submitLabel
                }}
            </button>
        </div>
    </form>
</template>

<style scoped>
.form-label {
    @apply mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300;
}

.form-input {
    @apply w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:focus:border-slate-400 dark:focus:ring-slate-700;
}

.form-error {
    @apply mt-2 text-sm text-red-600 dark:text-red-400;
}
</style>
