<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SourceForm from '@/Components/Sources/SourceForm.vue'

const props = defineProps({
    source: {
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
})

const form = useForm({
    name: props.source.name ?? '',
    type: props.source.type ?? 'supplier',
    status: props.source.status ?? 'active',
    quality_score: props.source.quality_score ?? 70,
    internal_notes: props.source.internal_notes ?? '',

    country: props.source.country ?? 'Bangladesh',
    division: props.source.division ?? '',
    district: props.source.district ?? '',
    city: props.source.city ?? '',
    area: props.source.area ?? '',
    latitude: props.source.latitude ?? '',
    longitude: props.source.longitude ?? '',

    verification_status:
        props.source.verification_status ?? 'pending',

    verification_type:
        props.source.verification_type ?? 'manual',

    verification_notes:
        props.source.verification_notes ?? '',

    performance: {
        total_orders:
            props.source.performance?.total_orders ?? 0,

        completed_orders:
            props.source.performance?.completed_orders ?? 0,

        cancelled_orders:
            props.source.performance?.cancelled_orders ?? 0,

        late_orders:
            props.source.performance?.late_orders ?? 0,

        rating:
            props.source.performance?.rating ?? 70,
    },

    products: props.source.products?.length
        ? props.source.products.map(product => ({
            product_id: product.product_id,
            price: Number(product.price ?? 0),
            minimum_order_quantity:
                Number(
                    product.minimum_order_quantity ?? 1
                ),
            quality_grade:
                product.quality_grade ?? 'standard',
            quality_score:
                Number(product.quality_score ?? 70),
            is_available:
                Boolean(product.is_available),
            available_quantity:
                Number(
                    product.available_quantity ?? 0
                ),
            unit:
                product.unit ?? 'piece',
            available_from:
                product.available_from ?? '',
            available_until:
                product.available_until ?? '',
        }))
        : [
            {
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
            },
        ],
})

function submit() {
    form.put(
        route(
            'admin.sources.update',
            props.source.id
        ),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <AppLayout>
        <Head
            :title="`Edit ${source.reference_code}`"
        />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="mb-8">
                <div
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    Admin / Sources /
                    {{ source.reference_code }}
                </div>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Edit Source
                </h1>

                <p
                    class="mt-2 text-slate-500 dark:text-slate-400"
                >
                    Update source data, products, availability and performance.
                </p>
            </div>

            <SourceForm
                :form="form"
                :products="products"
                :source-types="sourceTypes"
                :statuses="statuses"
                :verification-statuses="verificationStatuses"
                :verification-types="verificationTypes"
                submit-label="Update Source"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
