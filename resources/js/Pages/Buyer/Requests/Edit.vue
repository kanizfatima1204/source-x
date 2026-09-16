<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BuyerRequestForm from '@/Components/Buyer/BuyerRequestForm.vue'

const props = defineProps({
    buyerRequest: {
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
})

const form = useForm({
    location:
        props.buyerRequest.location ?? '',

    min_budget:
        props.buyerRequest.min_budget ?? '',

    max_budget:
        props.buyerRequest.max_budget ?? '',

    quality_requirement:
        props.buyerRequest.quality_requirement ?? '',

    required_by:
        props.buyerRequest.required_by ?? '',

    notes:
        props.buyerRequest.notes ?? '',

    status:
        props.buyerRequest.status ?? 'draft',

    items:
        props.buyerRequest.items?.length
            ? props.buyerRequest.items.map(
                item => ({
                    category_id:
                        item.category_id,

                    product_id:
                        item.product_id,

                    quantity:
                        Number(
                            item.quantity ?? 1
                        ),

                    unit:
                        item.unit ?? 'piece',
                })
            )
            : [
                {
                    category_id: '',
                    product_id: '',
                    quantity: 1,
                    unit: 'piece',
                },
            ],
})

function submit() {
    form.put(
        route(
            'buyer.requests.update',
            props.buyerRequest.id
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
            :title="`Edit ${buyerRequest.reference_code}`"
        />

        <div
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="mb-8">
                <p
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    Buyer / Requests /
                    {{ buyerRequest.reference_code }}
                </p>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Edit Sourcing Request
                </h1>
            </div>

            <BuyerRequestForm
                :form="form"
                :categories="categories"
                :products="products"
                submit-label="Update Request"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
