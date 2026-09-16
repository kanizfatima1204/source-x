<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
    layout: AppLayout,
})

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },

    unreadCount: {
        type: Number,
        default: 0,
    },
})

const unreadNotifications = computed(() =>
    props.notifications.filter(
        (notification) => !notification.read_at
    )
)

const severityClass = (severity) => {
    if (severity === 'high') {
        return 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'
    }

    if (severity === 'medium') {
        return 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400'
    }

    return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
}

const icon = (type) => {
    return {
        verification: '✓',
        availability: '◈',
        performance: '↗',
        matching: '◎',
    }[type] ?? '!'
}

const markRead = (notification) => {
    if (notification.read_at) {
        if (notification.url) {
            window.location.href = notification.url
        }

        return
    }

    router.post(
        route(
            'admin.notifications.read',
            notification.id
        ),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.url) {
                    window.location.href =
                        notification.url
                }
            },
        }
    )
}

const markAllRead = () => {
    router.post(
        route('admin.notifications.read-all'),
        {},
        {
            preserveScroll: true,
        }
    )
}

const generate = () => {
    router.post(
        route('admin.notifications.generate'),
        {},
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 dark:bg-slate-950"
    >
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-slate-400"
                    >
                        Admin Center
                    </span>

                    <h1
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        Notifications
                    </h1>

                    <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Operational alerts and administrative system
                        notifications.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="generate"
                    >
                        Check Alerts
                    </button>

                    <button
                        v-if="unreadCount"
                        type="button"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                        @click="markAllRead"
                    >
                        Mark All Read
                    </button>
                </div>
            </div>

            <div
                class="mb-5 grid gap-4 sm:grid-cols-2"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Total
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ notifications.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Unread
                    </p>

                    <p
                        class="mt-2 text-3xl font-bold text-rose-600 dark:text-rose-400"
                    >
                        {{ unreadCount }}
                    </p>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    v-if="!notifications.length"
                    class="p-12 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                    >
                        ✓
                    </div>

                    <h2
                        class="mt-4 font-semibold text-slate-900 dark:text-white"
                    >
                        No notifications
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        There are currently no operational notifications.
                    </p>
                </div>

                <div
                    v-else
                    class="divide-y divide-slate-100 dark:divide-slate-800"
                >
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        type="button"
                        class="flex w-full gap-4 p-5 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        :class="{
                            'bg-slate-50/80 dark:bg-slate-800/30':
                                !notification.read_at,
                        }"
                        @click="markRead(notification)"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold"
                            :class="severityClass(notification.severity)"
                        >
                            {{ icon(notification.type) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div
                                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ notification.title }}
                                    </h3>

                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                        :class="severityClass(notification.severity)"
                                    >
                                        {{ notification.severity }}
                                    </span>

                                    <span
                                        v-if="!notification.read_at"
                                        class="h-2 w-2 rounded-full bg-blue-500"
                                    />
                                </div>

                                <span
                                    class="shrink-0 text-xs text-slate-400"
                                >
                                    {{ notification.created_at_human }}
                                </span>
                            </div>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                            >
                                {{ notification.message }}
                            </p>

                            <p
                                v-if="notification.reference_code"
                                class="mt-2 text-xs font-semibold text-slate-400"
                            >
                                {{ notification.reference_code }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            <p
                class="mt-4 text-center text-xs text-slate-400"
            >
                Notifications are generated from live Source X operational
                data.
            </p>
        </div>
    </div>
</template>