<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BuyerRequestForm from '@/Components/Buyer/BuyerRequestForm.vue'

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },

    products: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    location: '',
    min_budget: '',
    max_budget: '',
    quality_requirement: '',
    required_by: '',
    notes: '',
    status: 'submitted',

    items: [
        {
            category_id: '',
            product_id: '',
            quantity: 1,
            unit: 'piece',
        },
    ],
})

function submit() {
    form.post(
        route('buyer.requests.store'),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <AppLayout>
        <Head title="Create Sourcing Request" />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="mb-8">
                <p
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    Buyer / Requests
                </p>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Find a Source
                </h1>

                <p
                    class="mt-2 max-w-2xl text-slate-500 dark:text-slate-400"
                >
                    Tell Source X what you need and our matching system will identify suitable sources.
                </p>
            </div>

            <BuyerRequestForm
                :form="form"
                :categories="categories"
                :products="products"
                submit-label="Submit Sourcing Request"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
