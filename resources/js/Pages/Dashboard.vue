<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
    import BarChart from '@/Components/BarChart.vue';
    import DataTable from '@/Components/DataTable.vue';
    import { tableSchemas } from '@/Objects/tableSchemas';

    defineProps({
        categoriesData: {
            type: Object,
            required: true
        },
        transactions: Array,
        totalIncome: {
            type: Number,
            required: true,
            default: 0
        },
        totalExpenses: {
            type: Number,
            required: true
        }
    });

    const formatValue = (value) => {
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
    }

    const columnsTransactions = tableSchemas.transactions

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-50">Dashboard Financeiro</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
                    <!-- Income Card -->
                    <div class="p-6 bg-green-100 rounded-lg shadow">
                        <div class="text-lg font-medium text-green-700">Receitas</div>
                        <div class="text-2xl font-bold">{{ formatValue(totalIncome) }}</div>
                    </div>

                    <!-- Expenses Card -->
                    <div class="p-6 bg-red-100 rounded-lg shadow">
                        <div class="text-lg font-medium text-red-700">Despesas</div>
                        <div class="text-2xl font-bold">{{ formatValue(totalExpenses) }}</div>
                    </div>

                    <!-- Balance Card -->
                    <div class="p-6 bg-blue-100 rounded-lg shadow">
                        <div class="text-lg font-medium text-blue-700">Saldo</div>
                        <div class="text-2xl font-bold">{{ formatValue(totalIncome - totalExpenses) }}</div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="p-6 bg-white rounded-lg shadow mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-800">Despesas por Categoria</h3>
                    <div class="h-64">
                        <BarChart v-if="categoriesData && categoriesData.labels" :chartData="categoriesData" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
                    <!-- Transações Recentes -->
                    <DataTable
                        :columns="columnsTransactions"
                        :rows="transactions"
                    >
                        <template #title>
                            <span class="text-gray-50">Últimas Entradas</span>
                        </template>

                    </DataTable>

                    <!-- Últimas Transações -->
                    <DataTable
                        :columns="columnsTransactions"
                        :rows="transactions"
                    >
                        <template #title>
                            <span class="text-gray-50">Últimas Entradas</span>
                        </template>

                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
