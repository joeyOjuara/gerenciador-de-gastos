<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: 'Tem certeza que deseja excluir este item?',
    },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div
                class="absolute inset-0 bg-black/60"
                @click="emit('cancel')"
            />

            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="show"
                    class="relative z-10 w-full max-w-sm rounded-xl bg-gray-900 border border-gray-700 p-6 shadow-xl"
                >
                    <h3 class="text-base font-semibold text-gray-100 mb-2">Confirmar exclusão</h3>
                    <p class="text-sm text-gray-400 mb-6">{{ message }}</p>

                    <div class="flex justify-end gap-3">
                        <button
                            @click="emit('cancel')"
                            class="px-4 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="emit('confirm')"
                            class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                        >
                            Excluir
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
