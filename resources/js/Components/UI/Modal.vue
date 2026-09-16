<script setup>
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    maxWidth: {
        type: String,
        default: 'lg',
    },

    closeable: {
        type: Boolean,
        default: true,
    },

    title: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['close'])

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
}[props.maxWidth] ?? 'max-w-lg'

const close = () => {
    if (!props.closeable) {
        return
    }

    emit('close')
}

const handleEscape = (event) => {
    if (event.key === 'Escape' && props.show) {
        close()
    }
}

watch(
    () => props.show,
    (value) => {
        document.body.style.overflow = value ? 'hidden' : ''
    },
    { immediate: true }
)

if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleEscape)
}

onBeforeUnmount(() => {
    document.body.style.overflow = ''

    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', handleEscape)
    }
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto p-4 sm:p-6"
            >
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    @click="close"
                />

                <Transition
                    appear
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="translate-y-4 scale-95 opacity-0"
                    enter-to-class="translate-y-0 scale-100 opacity-100"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="translate-y-0 scale-100 opacity-100"
                    leave-to-class="translate-y-4 scale-95 opacity-0"
                >
                    <div
                        v-if="show"
                        class="relative my-auto w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                        :class="maxWidthClass"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div
                            v-if="title || $slots.header"
                            class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                        >
                            <div>
                                <slot name="header">
                                    <h2 class="font-semibold text-slate-900 dark:text-white">
                                        {{ title }}
                                    </h2>
                                </slot>
                            </div>

                            <button
                                v-if="closeable"
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click="close"
                            >
                                ×
                            </button>
                        </div>

                        <div class="p-6">
                            <slot />
                        </div>

                        <div
                            v-if="$slots.footer"
                            class="border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
