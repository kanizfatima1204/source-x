<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
    settings: {
        type: Array,
        default: () => [],
    },
})

const localSettings = reactive(
    Object.fromEntries(
        props.settings.map((setting) => [
            setting.id,
            {
                value: setting.value,
                is_active: setting.is_active,
                saving: false,
            },
        ])
    )
)

const generalSettings = computed(() =>
    props.settings.filter((setting) =>
        [
            'algorithm_version',
            'minimum_score',
            'maximum_recommendations',
            'diversity_threshold',
            'cold_start_strategy',
            'minimum_interactions',
            'popularity_fallback_enabled',
        ].includes(setting.key)
    )
)

const experimentSettings = computed(() =>
    props.settings.filter((setting) =>
        [
            'ab_testing_enabled',
            'experiment_name',
            'experiment_variant_a',
            'experiment_variant_b',
            'experiment_traffic_percentage',
        ].includes(setting.key)
    )
)

const formatValue = (setting) => {
    const state = localSettings[setting.id]

    if (setting.type === 'boolean') {
        return state.value ? 'Enabled' : 'Disabled'
    }

    return state.value
}

const updateSetting = (setting) => {
    const state = localSettings[setting.id]

    state.saving = true

    router.patch(
        `/admin/recommendation-control-center/${setting.id}`,
        {
            value: state.value,
            is_active: state.is_active,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                state.saving = false
            },
        }
    )
}

const resetSettings = () => {
    if (!confirm('Reset all recommendation control settings to defaults?')) {
        return
    }

    router.post(
        '/admin/recommendation-control-center/reset',
        {},
        {
            preserveScroll: true,
        }
    )
}

const inputType = (setting) => {
    if (setting.type === 'integer' || setting.type === 'float') {
        return 'number'
    }

    if (setting.type === 'boolean') {
        return 'checkbox'
    }

    return 'text'
}

const selectOptions = (setting) => {
    if (
        setting.options &&
        Array.isArray(setting.options)
    ) {
        return setting.options
    }

    return []
}

const minValue = (setting) => {
    return setting.options?.min ?? undefined
}

const maxValue = (setting) => {
    return setting.options?.max ?? undefined

}

const stepValue = (setting) => {
    return setting.options?.step ?? (
        setting.type === 'float' ? 0.01 : 1
    )
}
</script>

<template>
    <Head title="Recommendation Control Center" />

    <div class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">

            <!-- Header -->
            <div class="mb-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <span
                            class="rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-cyan-300"
                        >
                            Source X AI
                        </span>

                        <span
                            class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                        >
                            Recommendation Engine
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold tracking-tight lg:text-4xl">
                        Recommendation Control Center
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-400">
                        Manage recommendation algorithms, score thresholds,
                        personalization behaviour, diversity and experiments
                        from one central configuration panel.
                    </p>
                </div>

                <div class="flex gap-3">
                    <a
                        href="/admin/recommendation-rules"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-200 transition hover:border-slate-600 hover:bg-slate-800"
                    >
                        Scoring Rules
                    </a>

                    <a
                        href="/admin/recommendation-performance"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-200 transition hover:border-slate-600 hover:bg-slate-800"
                    >
                        Performance
                    </a>

                    <button
                        type="button"
                        @click="resetSettings"
                        class="rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-slate-200"
                    >
                        Reset Defaults
                    </button>
                </div>
            </div>

            <!-- System Overview -->
            <div class="mb-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Algorithm
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{ localSettings[
                            props.settings.find(
                                item => item.key === 'algorithm_version'
                            )?.id
                        ]?.value ?? 'v1' }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Active recommendation engine
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Minimum Score
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{
                            localSettings[
                                props.settings.find(
                                    item => item.key === 'minimum_score'
                                )?.id
                            ]?.value ?? 35
                        }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Recommendation quality threshold
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        A/B Testing
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{
                            localSettings[
                                props.settings.find(
                                    item => item.key === 'ab_testing_enabled'
                                )?.id
                            ]?.value
                                ? 'ON'
                                : 'OFF'
                        }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Experiment control
                    </p>
                </div>
            </div>

            <!-- General Configuration -->
            <section class="mb-8">
                <div class="mb-5">
                    <h2 class="text-xl font-bold">
                        Recommendation Configuration
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Configure the core recommendation engine behaviour.
                    </p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div
                        v-for="setting in generalSettings"
                        :key="setting.id"
                        class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">
                                    {{ setting.name }}
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    {{ setting.description }}
                                </p>
                            </div>

                            <label class="relative inline-flex cursor-pointer items-center">
                                <input
                                    v-model="localSettings[setting.id].is_active"
                                    type="checkbox"
                                    class="peer sr-only"
                                />

                                <div
                                    class="h-6 w-11 rounded-full bg-slate-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-600 after:bg-white after:transition-all peer-checked:bg-cyan-600 peer-checked:after:translate-x-full peer-checked:after:border-white"
                                ></div>
                            </label>
                        </div>

                        <div class="mt-5">
                            <select
                                v-if="selectOptions(setting).length"
                                v-model="localSettings[setting.id].value"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-500"
                            >
                                <option
                                    v-for="option in selectOptions(setting)"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>

                            <label
                                v-else-if="setting.type === 'boolean'"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 p-4"
                            >
                                <input
                                    v-model="localSettings[setting.id].value"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-slate-600 bg-slate-900 text-cyan-500"
                                />

                                <span class="text-sm text-slate-300">
                                    {{ formatValue(setting) }}
                                </span>
                            </label>

                            <input
                                v-else
                                v-model="localSettings[setting.id].value"
                                :type="inputType(setting)"
                                :min="minValue(setting)"
                                :max="maxValue(setting)"
                                :step="stepValue(setting)"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-500"
                            />

                            <button
                                type="button"
                                :disabled="localSettings[setting.id].saving"
                                @click="updateSetting(setting)"
                                class="mt-4 rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    localSettings[setting.id].saving
                                        ? 'Saving...'
                                        : 'Save Setting'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- A/B Experiment -->
            <section>
                <div class="mb-5">
                    <h2 class="text-xl font-bold">
                        A/B Experiment Configuration
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Test recommendation algorithm variants without changing
                        the entire system configuration.
                    </p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div
                        v-for="setting in experimentSettings"
                        :key="setting.id"
                        class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">
                                    {{ setting.name }}
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    {{ setting.description }}
                                </p>
                            </div>

                            <label class="relative inline-flex cursor-pointer items-center">
                                <input
                                    v-model="localSettings[setting.id].is_active"
                                    type="checkbox"
                                    class="peer sr-only"
                                />

                                <div
                                    class="h-6 w-11 rounded-full bg-slate-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-600 after:bg-white after:transition-all peer-checked:bg-cyan-600 peer-checked:after:translate-x-full peer-checked:after:border-white"
                                ></div>
                            </label>
                        </div>

                        <div class="mt-5">
                            <select
                                v-if="selectOptions(setting).length"
                                v-model="localSettings[setting.id].value"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-500"
                            >
                                <option
                                    v-for="option in selectOptions(setting)"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>

                            <label
                                v-else-if="setting.type === 'boolean'"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 p-4"
                            >
                                <input
                                    v-model="localSettings[setting.id].value"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-slate-600 bg-slate-900 text-cyan-500"
                                />

                                <span class="text-sm text-slate-300">
                                    {{ formatValue(setting) }}
                                </span>
                            </label>

                            <input
                                v-else
                                v-model="localSettings[setting.id].value"
                                :type="inputType(setting)"
                                :min="minValue(setting)"
                                :max="maxValue(setting)"
                                :step="stepValue(setting)"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-500"
                            />

                            <button
                                type="button"
                                :disabled="localSettings[setting.id].saving"
                                @click="updateSetting(setting)"
                                class="mt-4 rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400 disabled:opacity-50"
                            >
                                {{
                                    localSettings[setting.id].saving
                                        ? 'Saving...'
                                        : 'Save Setting'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Architecture Note -->
            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-cyan-500/5 p-6">
                <div class="flex gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300"
                    >
                        AI
                    </div>

                    <div>
                        <h3 class="font-semibold text-cyan-200">
                            Control Center Architecture
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Admin configuration is stored in the database,
                            allowing recommendation behaviour to be changed
                            without deploying new application code.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
