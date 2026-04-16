<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-md bg-gray-800 border border-gray-700 text-gray-200 placeholder-gray-500 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
        v-model="model"
        ref="input"
    />
</template>
