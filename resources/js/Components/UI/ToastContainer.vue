<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const toasts = ref([])

let nextId = 1

const addToast = (message, type = 'success') => {
    if (!message) {
        return
    }

    const id = nextId++

    toasts.value.push({
        id,
        message,
        type,
    })

    window.setTimeout(() => {
        removeToast(id)
    }, 4500)
}

const removeToast = (id) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

const typeClass = (type) => {
    const classes = {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300',
        error: 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300',
        warning: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300',
        info: 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300',
    }

    return classes[type] ?? classes.info
}

const icon = (type) => {
    const icons = {
        success: '✓',
        error: '!',
        warning: '!',
        info: 'i',
    }

    return icons[type] ?? 'i'
}

onMounted(() => {
    const flash = page.props.flash ?? {}

    if (flash.success) {
        addToast(flash.success, 'success')
    }

    if (flash.error) {
        addToast(flash.error, 'error')
    }

    if (flash.warning) {
        addToast(flash.warning, 'warning')
    }

    if (flash.info) {
        addToast(flash.info, 'info')
    }
})
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-4 sm:items-end">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="-translate-y-3 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="translate-x-3 opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur"
                    :class="typeClass(toast.type)"
                >
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/60 text-sm font-bold dark:bg-black/20">
                        {{ icon(toast.type) }}
                    </div>

                    <p class="flex-1 pt-1 text-sm font-medium leading-5">
                        {{ toast.message }}
                    </p>

                    <button
                        type="button"
                        class="rounded-lg p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/5"
                        @click="removeToast(toast.id)"
                    >
                        ×
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
