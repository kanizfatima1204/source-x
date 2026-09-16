<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SourceForm from '@/Components/Sources/SourceForm.vue'

const props = defineProps({
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
    name: '',
    type: 'supplier',
    status: 'active',
    quality_score: 70,
    internal_notes: '',

    country: 'Bangladesh',
    division: '',
    district: '',
    city: '',
    area: '',
    latitude: '',
    longitude: '',

    verification_status: 'pending',
    verification_type: 'manual',
    verification_notes: '',

    performance: {
        total_orders: 0,
        completed_orders: 0,
        cancelled_orders: 0,
        late_orders: 0,
        rating: 70,
    },

    products: [
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
    form.post(route('admin.sources.store'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AppLayout>
        <Head title="Create Source" />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="mb-8">
                <div
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    Admin / Sources
                </div>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Add New Source
                </h1>

                <p
                    class="mt-2 text-slate-500 dark:text-slate-400"
                >
                    Create a verified source profile for the Source X matching engine.
                </p>
            </div>

            <SourceForm
                :form="form"
                :products="products"
                :source-types="sourceTypes"
                :statuses="statuses"
                :verification-statuses="verificationStatuses"
                :verification-types="verificationTypes"
                submit-label="Create Source"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
