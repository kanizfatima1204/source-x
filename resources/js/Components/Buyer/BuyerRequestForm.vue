<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    categories: {
        type: Array,
        default: () => [],
    },

    products: {
        type: Array,
        default: () => [],
    },

    submitLabel: {
        type: String,
        default: 'Submit Request',
    },
})

const emit = defineEmits(['submit'])

const filteredProducts = computed(() => {
    return props.products
})

function addItem() {
    props.form.items.push({
        category_id: '',
        product_id: '',
        quantity: 1,
        unit: 'piece',
    })
}

function removeItem(index) {
    if (props.form.items.length <= 1) {
        return
    }

    props.form.items.splice(index, 1)
}

function categoryChanged(item) {
    item.product_id = ''
    item.unit = 'piece'
}

function productChanged(item) {
    const product = props.products.find(
        product =>
            Number(product.id) ===
            Number(item.product_id)
    )

    if (!product) {
        return
    }

    item.category_id = product.category_id
    item.unit = product.unit || 'piece'
}

function error(field) {
    return props.form.errors?.[field]
}

function itemError(index, field) {
    return error(`items.${index}.${field}`)
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
        <!-- Request Details -->
        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="mb-6">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Request Details
                </h2>

                <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                    Tell Source X what you need.
                </p>
            </div>

            <div
                class="grid grid-cols-1 gap-5 md:grid-cols-2"
            >
                <div class="md:col-span-2">
                    <label class="form-label">
                        Delivery / Source Location
                    </label>

                    <input
                        v-model="form.location"
                        type="text"
                        class="form-input"
                        placeholder="e.g. Dhaka, Bangladesh"
                    />

                    <p
                        v-if="error('location')"
                        class="form-error"
                    >
                        {{ error('location') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Minimum Budget
                    </label>

                    <input
                        v-model.number="form.min_budget"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-input"
                        placeholder="Optional"
                    />

                    <p
                        v-if="error('min_budget')"
                        class="form-error"
                    >
                        {{ error('min_budget') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Maximum Budget
                    </label>

                    <input
                        v-model.number="form.max_budget"
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-input"
                        placeholder="Optional"
                    />

                    <p
                        v-if="error('max_budget')"
                        class="form-error"
                    >
                        {{ error('max_budget') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Required By
                    </label>

                    <input
                        v-model="form.required_by"
                        type="date"
                        class="form-input"
                    />

                    <p
                        v-if="error('required_by')"
                        class="form-error"
                    >
                        {{ error('required_by') }}
                    </p>
                </div>

                <div>
                    <label class="form-label">
                        Quality Requirement
                    </label>

                    <input
                        v-model="form.quality_requirement"
                        type="text"
                        class="form-input"
                        placeholder="e.g. Premium, organic, grade A"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">
                        Additional Notes
                    </label>

                    <textarea
                        v-model="form.notes"
                        rows="4"
                        class="form-input"
                        placeholder="Additional requirements..."
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
                        Products Needed
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Add one or more products to your request.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                    @click="addItem"
                >
                    + Add Product
                </button>
            </div>

            <div class="space-y-5">
                <article
                    v-for="(item, index) in form.items"
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
                            v-if="form.items.length > 1"
                            type="button"
                            class="text-sm font-semibold text-red-600"
                            @click="removeItem(index)"
                        >
                            Remove
                        </button>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4"
                    >
                        <div>
                            <label class="form-label">
                                Category
                            </label>

                            <select
                                v-model="item.category_id"
                                class="form-input"
                                @change="categoryChanged(item)"
                            >
                                <option value="">
                                    Select category
                                </option>

                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <p
                                v-if="itemError(index, 'category_id')"
                                class="form-error"
                            >
                                {{
                                    itemError(
                                        index,
                                        'category_id'
                                    )
                                }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">
                                Product
                            </label>

                            <select
                                v-model="item.product_id"
                                class="form-input"
                                @change="productChanged(item)"
                            >
                                <option value="">
                                    Select product
                                </option>

                                <option
                                    v-for="product in filteredProducts.filter(
                                        product =>
                                            !item.category_id ||
                                            Number(product.category_id) ===
                                            Number(item.category_id)
                                    )"
                                    :key="product.id"
                                    :value="product.id"
                                >
                                    {{
                                        product.category_name
                                    }}
                                    —
                                    {{ product.name }}
                                </option>
                            </select>

                            <p
                                v-if="itemError(index, 'product_id')"
                                class="form-error"
                            >
                                {{
                                    itemError(
                                        index,
                                        'product_id'
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label class="form-label">
                                Quantity
                            </label>

                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="form-input"
                            />

                            <p
                                v-if="itemError(index, 'quantity')"
                                class="form-error"
                            >
                                {{
                                    itemError(
                                        index,
                                        'quantity'
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label class="form-label">
                                Unit
                            </label>

                            <input
                                v-model="item.unit"
                                type="text"
                                class="form-input"
                            />
                        </div>
                    </div>
                </article>
            </div>

            <p
                v-if="error('items')"
                class="form-error mt-4"
            >
                {{ error('items') }}
            </p>
        </section>

        <!-- Actions -->
        <div
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <Link
                :href="route('buyer.requests.index')"
                class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                Cancel
            </Link>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-700 disabled:opacity-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
            >
                {{
                    form.processing
                        ? 'Submitting...'
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
