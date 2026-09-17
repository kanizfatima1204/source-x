<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
    experiments: {
        type: Array,
        default: () => [],
    },

    runningExperiment: {
        type: Object,
        default: null,
    },
})

const form = reactive({
    name: '',
    description: '',
    control_algorithm: 'v1',
    variant_algorithm: 'v2',
    traffic_percentage: 50,
    minimum_sample_size: 100,
})

const creating = reactive({
    value: false,
})

const hasRunningExperiment = computed(() => {
    return !!props.runningExperiment
})

const createExperiment = () => {
    creating.value = true

    router.post(
        '/admin/recommendation-experiments',
        form,
        {
            preserveScroll: true,
            onSuccess: () => {
                form.name = ''
                form.description = ''
                form.control_algorithm = 'v1'
                form.variant_algorithm = 'v2'
                form.traffic_percentage = 50
                form.minimum_sample_size = 100
            },
            onFinish: () => {
                creating.value = false
            },
        }
    )
}

const startExperiment = (experiment) => {
    if (
        !confirm(
            `Start experiment "${experiment.name}"?`
        )
    ) {
        return
    }

    router.post(
        `/admin/recommendation-experiments/${experiment.id}/start`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const pauseExperiment = (experiment) => {
    router.post(
        `/admin/recommendation-experiments/${experiment.id}/pause`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const completeExperiment = (experiment) => {
    if (
        !confirm(
            `Complete experiment "${experiment.name}"?`
        )
    ) {
        return
    }

    router.post(
        `/admin/recommendation-experiments/${experiment.id}/complete`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const resetAssignments = (experiment) => {
    if (
        !confirm(
            'Reset all buyer assignments for this experiment?'
        )
    ) {
        return
    }

    router.post(
        `/admin/recommendation-experiments/${experiment.id}/reset-assignments`,
        {},
        {
            preserveScroll: true,
        }
    )
}

const statusClass = (status) => {
    if (status === 'running') {
        return 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20'
    }

    if (status === 'paused') {
        return 'bg-amber-500/10 text-amber-300 border-amber-500/20'
    }

    if (status === 'completed') {
        return 'bg-cyan-500/10 text-cyan-300 border-cyan-500/20'
    }

    return 'bg-slate-800 text-slate-300 border-slate-700'
}
</script>

<template>
    <Head title="Recommendation Experiments" />

    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">

            <div class="mb-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span
                        class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300"
                    >
                        Source X AI
                    </span>

                    <h1 class="mt-4 text-3xl font-bold lg:text-4xl">
                        Recommendation Experiments
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">
                        Compare recommendation algorithms using controlled
                        buyer traffic and behavioural feedback.
                    </p>
                </div>

                <div class="flex gap-3">
                    <a
                        href="/admin/recommendation-control-center"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                    >
                        Control Center
                    </a>

                    <a
                        href="/admin/recommendation-performance"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-800"
                    >
                        Performance
                    </a>
                </div>
            </div>

            <!-- Active Experiment -->
            <div
                class="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-6"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                            Active Experiment
                        </p>

                        <h2 class="mt-2 text-xl font-bold">
                            {{
                                hasRunningExperiment
                                    ? runningExperiment.name
                                    : 'No active experiment'
                            }}
                        </h2>
                    </div>

                    <div
                        v-if="hasRunningExperiment"
                        class="rounded-full bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300"
                    >
                        Running
                    </div>
                </div>
            </div>

            <!-- Create -->
            <section class="mb-10 rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-bold">
                        Create Experiment
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Create a controlled algorithm comparison.
                    </p>
                </div>

                <form
                    class="grid gap-5 lg:grid-cols-2"
                    @submit.prevent="createExperiment"
                >
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Experiment Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="recommendation_v1_v2_test"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Description
                        </label>

                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="Compare algorithm variants"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Control Algorithm
                        </label>

                        <select
                            v-model="form.control_algorithm"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        >
                            <option value="v1">v1</option>
                            <option value="v2">v2</option>
                            <option value="hybrid">hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Variant Algorithm
                        </label>

                        <select
                            v-model="form.variant_algorithm"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        >
                            <option value="v1">v1</option>
                            <option value="v2">v2</option>
                            <option value="hybrid">hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Variant Traffic %
                        </label>

                        <input
                            v-model.number="form.traffic_percentage"
                            type="number"
                            min="1"
                            max="99"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-300">
                            Minimum Sample Size
                        </label>

                        <input
                            v-model.number="form.minimum_sample_size"
                            type="number"
                            min="1"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </div>

                    <div class="lg:col-span-2">
                        <button
                            type="submit"
                            :disabled="creating.value"
                            class="rounded-xl bg-cyan-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400 disabled:opacity-50"
                        >
                            {{
                                creating.value
                                    ? 'Creating...'
                                    : 'Create Experiment'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Experiments -->
            <section>
                <div class="mb-5">
                    <h2 class="text-xl font-bold">
                        Experiments
                    </h2>
                </div>

                <div class="space-y-5">
                    <div
                        v-for="experiment in experiments"
                        :key="experiment.id"
                        class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6"
                    >
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">

                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-lg font-bold">
                                        {{ experiment.name }}
                                    </h3>

                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-semibold uppercase"
                                        :class="statusClass(experiment.status)"
                                    >
                                        {{ experiment.status }}
                                    </span>
                                </div>

                                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                                    {{ experiment.description }}
                                </p>

                                <div class="mt-5 grid gap-3 sm:grid-cols-4">
                                    <div>
                                        <p class="text-xs text-slate-600">
                                            Control
                                        </p>

                                        <p class="mt-1 font-semibold">
                                            {{ experiment.control_algorithm }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-600">
                                            Variant
                                        </p>

                                        <p class="mt-1 font-semibold">
                                            {{ experiment.variant_algorithm }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-600">
                                            Traffic
                                        </p>

                                        <p class="mt-1 font-semibold">
                                            {{ experiment.traffic_percentage }}%
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-600">
                                            Assigned Buyers
                                        </p>

                                        <p class="mt-1 font-semibold">
                                            {{ experiment.assignments_count }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a
                                    :href="`/admin/recommendation-experiments/${experiment.id}/metrics`"
                                    class="rounded-xl border border-cyan-500/20 bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/20"
                                >
                                    Metrics
                                </a>

                                <button
                                    v-if="experiment.status !== 'running'"
                                    type="button"
                                    @click="startExperiment(experiment)"
                                    class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300"
                                >
                                    Start
                                </button>

                                <button
                                    v-if="experiment.status === 'running'"
                                    type="button"
                                    @click="pauseExperiment(experiment)"
                                    class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-300"
                                >
                                    Pause
                                </button>

                                <button
                                    v-if="experiment.status === 'running' || experiment.status === 'paused'"
                                    type="button"
                                    @click="completeExperiment(experiment)"
                                    class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-300"
                                >
                                    Complete
                                </button>

                                <button
                                    type="button"
                                    @click="resetAssignments(experiment)"
                                    class="rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-300"
                                >
                                    Reset Users
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!experiments.length"
                        class="rounded-2xl border border-dashed border-slate-800 p-10 text-center text-slate-500"
                    >
                        No recommendation experiments found.
                    </div>
                </div>
            </section>

        </div>
    </div>
</template>
