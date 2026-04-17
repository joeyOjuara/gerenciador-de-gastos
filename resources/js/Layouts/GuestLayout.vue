<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const appName = usePage().props.appName;
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 border-gray-700 bg-gray-900"
    >
        <div class="flex flex-col items-center gap-2">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
            <span class="text-xl font-bold tracking-wide text-gray-100">{{ appName }}</span>
            <span class="text-sm font-bold tracking-wide text-gray-100">Seu Gerenciador Financeiro</span>
        </div>

        <div
            class="mt-6 w-full overflow-hidden px-6 py-4 sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
