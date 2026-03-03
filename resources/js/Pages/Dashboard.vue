<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BarChart from '@/Components/BarChart.vue';

defineProps({
  categoriesData: {
    type: Object,
    required: true
  },
  transactions: {
    type: Array,
    required: true
  },
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
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Financeiro</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
          <!-- Income Card -->
          <div class="p-6 bg-green-100 rounded-lg shadow">
            <div class="text-lg font-medium text-green-700">Receitas</div>
            <div class="text-2xl font-bold">R$ {{ totalIncome }}</div>
          </div>

          <!-- Expenses Card -->
          <div class="p-6 bg-red-100 rounded-lg shadow">
            <div class="text-lg font-medium text-red-700">Despesas</div>
            <div class="text-2xl font-bold">R$ {{ totalExpenses }}</div>
          </div>

          <!-- Balance Card -->
          <div class="p-6 bg-blue-100 rounded-lg shadow">
            <div class="text-lg font-medium text-blue-700">Saldo</div>
            <div class="text-2xl font-bold">R$ {{ (totalIncome - totalExpenses) }}</div>
          </div>
        </div>

        <!-- Chart -->
        <div class="p-6 bg-white rounded-lg shadow mb-8">
          <h3 class="mb-4 text-lg font-medium text-gray-800">Despesas por Categoria</h3>
          <div class="h-64">
            <BarChart v-if="categoriesData && categoriesData.labels" :chartData="categoriesData" />
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="p-6 bg-white rounded-lg shadow">
          <h3 class="mb-4 text-lg font-medium text-gray-800">Últimas Transações</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descrição</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Categoria</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Valor</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in transactions" :key="transaction.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ transaction.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ transaction.category.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">R$ {{ transaction.amount.toFixed(2) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ new Date(transaction.date).toLocaleDateString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
