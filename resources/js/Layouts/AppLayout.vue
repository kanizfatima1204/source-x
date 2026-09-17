<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const page = usePage()

const unreadNotifications = ref([])
const unreadNotificationCount = ref(0)
const notificationOpen = ref(false)
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

const safeRoute = (name, params = undefined, fallback = '#') => {
    try {
        if (typeof route === 'function' && route().has(name)) {
            return route(name, params)
        }
    } catch {
        // Fallback gracefully
    }
    return fallback
}

const loadNotifications = async () => {
    try {
        if (typeof route !== 'function' || !route().has('admin.notifications.unread')) {
            return
        }

        const response = await fetch(
            route('admin.notifications.unread'),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        )

        if (!response.ok) {
            return
        }

        const data = await response.json()

        unreadNotifications.value =
            data.notifications ?? []

        unreadNotificationCount.value =
            data.unread_count ?? 0
    } catch {
        // Notification polling is non-critical.
    }
}

let notificationTimer = null

onMounted(() => {
    if (user.value?.role === 'admin') {
        loadNotifications()

        notificationTimer = window.setInterval(
            loadNotifications,
            30000
        )
    }
})

onBeforeUnmount(() => {
    if (notificationTimer) {
        window.clearInterval(notificationTimer)
    }
})

const user = computed(() => page.props.auth?.user ?? null)

const navigation = computed(() => {
    if (user.value?.role === 'admin') {
        return [
            {
                label: 'Dashboard',
                href: safeRoute('admin.dashboard', undefined, '/admin'),
                route: 'admin.dashboard',
                icon: '▦',
            },
            {
                label: 'Sources',
                href: safeRoute('admin.sources.index', undefined, '/admin/sources'),
                route: 'admin.sources.*',
                icon: '◈',
            },
            {
                label: 'Match Review',
                href: safeRoute('admin.matches.index', undefined, '/admin/matches'),
                route: 'admin.matches.*',
                icon: '◎',
            },
            {
                label: 'Audit Logs',
                href: safeRoute('admin.audit-logs.index', undefined, '/admin/audit-logs'),
                route: 'admin.audit-logs.*',
                icon: '◌',
            },
            {
                label: 'Operations',
                href: safeRoute('admin.operational.index', undefined, '/admin/operational'),
                route: 'admin.operational',
                icon: '◉',
            },
            {
                label: 'Notifications',
                href: safeRoute('admin.notifications.index', undefined, '/admin/notifications'),
                route: 'admin.notifications.*',
                icon: '🔔',
            },
        ]
    }

    return [
        {
            label: 'Dashboard',
            href: safeRoute('dashboard', undefined, '/dashboard'),
            route: 'dashboard',
            icon: '⌂',
        },
        {
            label: 'My Requests',
            href: safeRoute('buyer.requests.index', undefined, '/buyer/requests'),
            route: 'buyer.requests.*',
            icon: '▤',
        },
    ]
})
const isActive = (item) => {
    try {
        if (typeof route === 'function' && route().has(item.route)) {
            return route().current(item.route)
        }
    } catch {
        // Fallback
    }
    return typeof window !== 'undefined' && window.location.pathname.startsWith(item.href)
}

const closeSidebar = () => {
    sidebarOpen.value = false
}

const logout = () => {
    userMenuOpen.value = false
    router.post(safeRoute('logout', undefined, '/logout'))
}

const toggleTheme = () => {
    const isDark = document.documentElement.classList.contains('dark')

    if (isDark) {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('source-x-theme', 'light')
    } else {
        document.documentElement.classList.add('dark')
        localStorage.setItem('source-x-theme', 'dark')
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-white">

        <Head title="Source X" />

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform duration-300 dark:border-slate-800 dark:bg-slate-900 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand -->
            <div class="flex h-20 items-center justify-between border-b border-slate-200 px-6 dark:border-slate-800">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                    @click="closeSidebar"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-lg font-bold text-white shadow-lg dark:bg-white dark:text-slate-900">
                        SX
                    </div>

                    <div>
                        <div class="text-base font-bold tracking-tight text-slate-900 dark:text-white">
                            Source X
                        </div>

                        <div class="text-[10px] font-medium uppercase tracking-[0.18em] text-slate-400">
                            Intelligent Sourcing
                        </div>
                    </div>
                </Link>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200 lg:hidden"
                    @click="closeSidebar"
                >
                    ×
                </button>
            </div>

            <!-- Workspace -->
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <div class="rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/70">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-xs font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-400">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                {{ user?.name ?? 'User' }}
                            </p>

                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ user?.role === 'admin' ? 'Administrator' : 'Buyer' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
<div
    v-if="user?.role === 'admin'"
    class="relative"
>
    <button
        type="button"
        class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
        @click="notificationOpen = !notificationOpen"
    >
        <span class="text-lg">🔔</span>

        <span
            v-if="unreadNotificationCount > 0"
            class="absolute -right-0.5 -top-0.5 flex min-w-[18px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-[18px] text-white"
        >
            {{
                unreadNotificationCount > 99
                    ? '99+'
                    : unreadNotificationCount
            }}
        </span>
    </button>

    <div
        v-if="notificationOpen"
        class="absolute right-0 z-50 mt-3 w-[350px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900"
    >
        <div
            class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-800"
        >
            <div>
                <p
                    class="text-sm font-bold text-slate-900 dark:text-white"
                >
                    Notifications
                </p>

                <p
                    class="text-[11px] text-slate-400"
                >
                    {{ unreadNotificationCount }} unread
                </p>
            </div>

            <button
                type="button"
                class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                @click="notificationOpen = false"
            >
                Close
            </button>
        </div>

        <div
            v-if="!unreadNotifications.length"
            class="p-8 text-center"
        >
            <p
                class="text-sm font-medium text-slate-700 dark:text-slate-300"
            >
                You're all caught up.
            </p>

            <p
                class="mt-1 text-xs text-slate-400"
            >
                No unread operational alerts.
            </p>
        </div>

        <div
            v-else
            class="max-h-[380px] overflow-y-auto"
        >
            <a
                v-for="notification in unreadNotifications"
                :key="notification.id"
                :href="
                    notification.url
                    || route('admin.notifications.index')
                "
                class="block border-b border-slate-100 p-4 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
            >
            
                <div class="flex gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-xs font-bold text-rose-600 dark:bg-rose-500/10 dark:text-rose-400"
                    >
                        !
                    </div>

                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold text-slate-900 dark:text-white"
                        >
                            {{ notification.title }}
                        </p>

                        <p
                            class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                        >
                            {{ notification.message }}
                        </p>

                        <p
                            class="mt-1 text-[10px] text-slate-400"
                        >
                            {{ notification.created_at_human }}
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div
            class="border-t border-slate-200 p-3 dark:border-slate-800"
        >
            <a
                :href="route('admin.notifications.index')"
                class="block rounded-lg bg-slate-100 px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                @click="notificationOpen = false"
            >
                View all notifications
            </a>
        </div>
    </div>
</div>
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-4 py-5">
                <p class="px-3 pb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">
                    Workspace
                </p>

                <div class="space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.label"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="
                            isActive(item)
                                ? 'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'
                        "
                        @click="closeSidebar"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-base"
                            :class="
                                isActive(item)
                                    ? 'bg-white/10 dark:bg-black/5'
                                    : 'bg-slate-100 dark:bg-slate-800'
                            "
                        >
                            {{ item.icon }}
                        </span>

                        <span>{{ item.label }}</span>

                        <span
                            v-if="isActive(item)"
                            class="ml-auto h-1.5 w-1.5 rounded-full bg-current"
                        />
                    </Link>
                </div>

                <div
                    v-if="user?.role === 'admin'"
                    class="mt-8"
                >
                    <p class="px-3 pb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">
                        System
                    </p>

                    <div class="rounded-xl border border-violet-100 bg-violet-50 p-4 dark:border-violet-500/10 dark:bg-violet-500/5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                ✦
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-violet-900 dark:text-violet-200">
                                    Matching Engine
                                </p>

                                <p class="mt-1 text-[11px] leading-5 text-violet-700 dark:text-violet-300">
                                    Source X uses multiple matching factors to identify suitable sources.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Bottom -->
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                    @click="toggleTheme"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800">
                        ◐
                    </span>

                    <span>Toggle appearance</span>
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="lg:pl-72">

            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        ☰
                    </button>

                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            {{ user?.role === 'admin' ? 'Operations Workspace' : 'Buyer Workspace' }}
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Manage your Source X activity
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">

                    <!-- Theme -->
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                        title="Toggle theme"
                        @click="toggleTheme"
                    >
                        ◐
                    </button>

                    <!-- User menu -->
                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-1.5 transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">
                                {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                            </div>

                            <div class="hidden text-left sm:block">
                                <p class="max-w-28 truncate text-xs font-semibold text-slate-900 dark:text-white">
                                    {{ user?.name }}
                                </p>

                                <p class="text-[10px] capitalize text-slate-400">
                                    {{ user?.role }}
                                </p>
                            </div>

                            <span class="hidden text-slate-400 sm:block">
                                ▾
                            </span>
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 opacity-0"
                            enter-to-class="translate-y-0 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 opacity-100"
                            leave-to-class="translate-y-1 opacity-0"
                        >
                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="border-b border-slate-100 px-3 py-3 dark:border-slate-800">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ user?.name }}
                                    </p>

                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                        {{ user?.email }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                    @click="logout"
                                >
                                    <span>↪</span>
                                    Sign out
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Page -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Global toast system -->
        <ToastContainer />
    </div>
</template>
