<script setup>
import { useToast } from '@/composables/useToast'
const { toasts, remove } = useToast()
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2 pointer-events-none">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium min-w-[260px] max-w-sm"
                    :class="{
                        'bg-green-900 border border-green-700 text-green-200': toast.type === 'success',
                        'bg-red-900 border border-red-700 text-red-200': toast.type === 'error',
                        'bg-blue-900 border border-blue-700 text-blue-200': toast.type === 'info',
                    }"
                >
                    <span class="text-base">
                        {{ toast.type === 'success' ? '✓' : toast.type === 'error' ? '✕' : 'ℹ' }}
                    </span>
                    <span class="flex-1">{{ toast.message }}</span>
                    <button
                        @click="remove(toast.id)"
                        class="opacity-60 hover:opacity-100 transition ml-2"
                    >✕</button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
