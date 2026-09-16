<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    buyerRequest: {
        type: Object,
        required: true,
    },

    matches: {
        type: Array,
        default: () => [],
    },

    overrides: {
        type: Array,
        default: () => [],
    },
})

const selectedMatch = ref(null)
const showOverrideModal = ref(false)

const form = useForm({
    action: 'select',
    reason: '',
    new_rank: '',
})

function openOverride(match, action = 'select') {
    selectedMatch.value = match

    form.reset()

    form.action = action
    form.reason = ''
    form.new_rank = action === 'change_rank'
        ? match.rank
        : ''

    showOverrideModal.value = true
}

function closeOverride() {
    if (form.processing) {
        return
    }

    showOverrideModal.value = false
    selectedMatch.value = null
    form.reset()
}

function submitOverride() {
    if (!selectedMatch.value) {
        return
    }

    form.post(
        route(
            'admin.matches.override',
            {
                buyerRequest: props.buyerRequest.id,
                matchResult: selectedMatch.value.id,
            }
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                closeOverride()
            },
        }
    )
}

function reviewMatch(match, status) {
    router.patch(
        route(
            'admin.matches.review',
            match.id
        ),
        {
            status,
        },
        {
            preserveScroll: true,
        }
    )
}

function scoreClass(score) {
    if (score >= 85) {
        return 'text-emerald-600 dark:text-emerald-400'
    }

    if (score >= 70) {
        return 'text-amber-600 dark:text-amber-400'
    }

    return 'text-red-600 dark:text-red-400'
}

function confidenceClass(confidence) {
    if (confidence === 'high') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
    }

    if (confidence === 'medium') {
        return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
    }

    return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
}

function statusClass(status) {
    if (status === 'approved') {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
    }

    if (status === 'rejected') {
        return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
    }

    if (status === 'reviewed') {
        return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
    }

    return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}

function factorLabel(factor) {
    return {
        product: 'Product Match',
        category: 'Category',
        location: 'Location',
        price: 'Price',
        quality: 'Quality',
        availability: 'Availability',
        verification: 'Verification',
        performance: 'Performance',
    }[factor] ?? factor
}
</script>

<template>
    <Head :title="`Match Review — ${buyerRequest.reference_code}`" />

    <AppLayout>
        <div class="space-y-8">

            <!-- Header -->
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <Link
                            :href="route('admin.matches.index')"
                            class="hover:text-indigo-600"
                        >
                            Match Review
                        </Link>

                        <span>/</span>

                        <span>{{ buyerRequest.reference_code }}</span>
                    </div>

                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">
                        Match Decision
                    </h1>

                    <p class="mt-2 text-slate-500 dark:text-slate-400">
                        Review algorithmic recommendations and record the final admin decision.
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-xs uppercase tracking-wide text-slate-400">
                        Request
                    </div>

                    <div class="mt-1 font-bold text-slate-900 dark:text-white">
                        {{ buyerRequest.reference_code }}
                    </div>
                </div>
            </div>

            <!-- Request information -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Buyer
                    </div>

                    <div class="mt-2 font-semibold text-slate-900 dark:text-white">
                        {{ buyerRequest.user?.name }}
                    </div>

                    <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ buyerRequest.user?.email }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Location
                    </div>

                    <div class="mt-2 font-semibold text-slate-900 dark:text-white">
                        {{ buyerRequest.location }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Request Status
                    </div>

                    <div class="mt-2 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold capitalize text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                        {{ buyerRequest.status }}
                    </div>
                </div>
            </div>

            <!-- Requested products -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white">
                        Requested Products
                    </h2>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="item in buyerRequest.items"
                        :key="item.id"
                        class="rounded-xl border border-slate-200 p-4 dark:border-slate-700"
                    >
                        <div class="font-semibold text-slate-900 dark:text-white">
                            {{ item.product?.name }}
                        </div>

                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ item.category?.name }}
                        </div>

                        <div class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                            {{ item.quantity }} {{ item.unit }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Match results -->
            <section>
                <div class="mb-5 flex items-end justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Algorithm Results
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Source X generated these recommendations automatically.
                        </p>
                    </div>

                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        {{ matches.length }} matches
                    </div>
                </div>

                <div class="space-y-6">
                    <article
                        v-for="match in matches"
                        :key="match.id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Match top -->
                        <div class="p-6">
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">

                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-lg font-black text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        #{{ match.rank }}
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                                {{ match.source?.reference_code }}
                                            </h3>

                                            <span
                                                v-if="match.is_algorithm_selected"
                                                class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"
                                            >
                                                Algorithm Selected
                                            </span>

                                            <span
                                                v-if="match.is_admin_selected"
                                                class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                            >
                                                Final Admin Selection
                                            </span>

                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                                :class="statusClass(match.status)"
                                            >
                                                {{ match.status }}
                                            </span>
                                        </div>

                                        <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                                            {{ match.summary }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div>
                                        <div
                                            class="text-3xl font-black"
                                            :class="scoreClass(Number(match.total_score))"
                                        >
                                            {{ Number(match.total_score).toFixed(2) }}
                                        </div>

                                        <div class="text-right text-xs uppercase tracking-wide text-slate-400">
                                            Score
                                        </div>
                                    </div>

                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                        :class="confidenceClass(match.confidence)"
                                    >
                                        {{ match.confidence }}
                                    </span>
                                </div>
                            </div>

                            <!-- Source internal details -->
                            <div class="mt-6 grid gap-4 border-t border-slate-100 pt-6 dark:border-slate-800 md:grid-cols-3">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-slate-400">
                                        Source Type
                                    </div>

                                    <div class="mt-1 font-semibold capitalize text-slate-800 dark:text-slate-200">
                                        {{ match.source?.type?.replace('_', ' ') }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs uppercase tracking-wide text-slate-400">
                                        Quality
                                    </div>

                                    <div class="mt-1 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ match.source?.quality_score }}/100
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs uppercase tracking-wide text-slate-400">
                                        Performance
                                    </div>

                                    <div class="mt-1 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ match.source?.performance_score }}/100
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Factors -->
                        <div class="border-t border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-950/40">
                            <h4 class="mb-5 font-bold text-slate-900 dark:text-white">
                                Explainable Score
                            </h4>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div
                                    v-for="factor in match.explanation?.factors || []"
                                    :key="factor.id"
                                >
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {{ factorLabel(factor.factor) }}
                                        </span>

                                        <span class="text-sm font-bold text-slate-900 dark:text-white">
                                            {{ Number(factor.score).toFixed(0) }}
                                        </span>
                                    </div>

                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div
                                            class="h-full rounded-full bg-indigo-500"
                                            :style="{
                                                width: `${Math.min(100, Number(factor.score))}%`
                                            }"
                                        />
                                    </div>

                                    <div class="mt-2 flex justify-between text-xs text-slate-400">
                                        <span>
                                            Weight {{ factor.weight }}%
                                        </span>

                                        <span>
                                            +{{ Number(factor.weighted_score).toFixed(2) }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        {{ factor.message }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-3 border-t border-slate-200 p-6 dark:border-slate-800">
                            <button
                                type="button"
                                class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                @click="openOverride(match, 'approve')"
                            >
                                Approve as Final
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-900/50 dark:bg-indigo-900/20 dark:text-indigo-300"
                                @click="openOverride(match, 'select')"
                            >
                                Select Source
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="openOverride(match, 'change_rank')"
                            >
                                Change Rank
                            </button>

                            <button
                                type="button"
                                class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300"
                                @click="openOverride(match, 'reject')"
                            >
                                Reject Match
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Audit history -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-white">
                        Decision Audit History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Every manual decision is preserved for traceability.
                    </p>
                </div>

                <div
                    v-if="!overrides.length"
                    class="p-8 text-center text-sm text-slate-500 dark:text-slate-400"
                >
                    No admin decisions have been recorded yet.
                </div>

                <div
                    v-else
                    class="divide-y divide-slate-100 dark:divide-slate-800"
                >
                    <div
                        v-for="override in overrides"
                        :key="override.id"
                        class="p-6"
                    >
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold capitalize text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ override.action.replace('_', ' ') }}
                                    </span>

                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                        {{ override.match?.source_reference }}
                                    </span>
                                </div>

                                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    {{ override.reason }}
                                </p>
                            </div>

                            <div class="shrink-0 text-left text-xs text-slate-400 lg:text-right">
                                <div>
                                    {{ override.admin?.name }}
                                </div>

                                <div class="mt-1">
                                    {{ override.created_at }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="override.previous_rank || override.new_rank"
                            class="mt-4 flex gap-4 text-xs text-slate-500 dark:text-slate-400"
                        >
                            <span>
                                Previous rank:
                                <strong class="text-slate-800 dark:text-slate-200">
                                    #{{ override.previous_rank }}
                                </strong>
                            </span>

                            <span>
                                New rank:
                                <strong class="text-slate-800 dark:text-slate-200">
                                    #{{ override.new_rank }}
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Override modal -->
        <div
            v-if="showOverrideModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Record Admin Decision
                            </h3>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ selectedMatch?.source?.reference_code }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="text-2xl text-slate-400 hover:text-slate-600"
                            @click="closeOverride"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <form
                    class="space-y-5 p-6"
                    @submit.prevent="submitOverride"
                >
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            Action
                        </label>

                        <select
                            v-model="form.action"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="select">
                                Select as Final Source
                            </option>

                            <option value="approve">
                                Approve as Final
                            </option>

                            <option value="change_rank">
                                Change Rank
                            </option>

                            <option value="reject">
                                Reject Match
                            </option>
                        </select>

                        <div
                            v-if="form.errors.action"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.action }}
                        </div>
                    </div>

                    <div v-if="form.action === 'change_rank'">
                        <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            New Rank
                        </label>

                        <input
                            v-model="form.new_rank"
                            type="number"
                            min="1"
                            max="100"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />

                        <div
                            v-if="form.errors.new_rank"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.new_rank }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            Decision Reason
                        </label>

                        <textarea
                            v-model="form.reason"
                            rows="5"
                            placeholder="Explain why this administrative decision was made..."
                            class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        />

                        <div
                            v-if="form.errors.reason"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.reason }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            :disabled="form.processing"
                            @click="closeOverride"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Decision' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
