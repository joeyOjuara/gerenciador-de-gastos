<script setup>
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BarChart from '@/Components/BarChart.vue'
import LineChart from '@/Components/LineChart.vue'
import DataTable from '@/Components/DataTable.vue'
import { tableSchemas } from '@/Objects/tableSchemas'
import { formatCurrency } from '@/utils/formatCurrency'

const props = defineProps({
    categoriesData: { type: Object, required: true },
    monthlyData:    { type: Array,  required: true },
    transactions:   Array,
    totalIncome:    { type: Number, required: true, default: 0 },
    totalExpenses:  { type: Number, required: true },
    currentMonth:   { type: Number, required: true },
    currentYear:    { type: Number, required: true },
})

// ── Period navigation ──────────────────────────────────────────────────────────
const MONTHS = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']
const periodLabel = computed(() => `${MONTHS[props.currentMonth - 1]} ${props.currentYear}`)

function navigate(delta) {
    let month = props.currentMonth + delta
    let year = props.currentYear
    if (month < 1)  { month = 12; year-- }
    if (month > 12) { month = 1;  year++ }
    router.get('/dashboard', (!delta ? {} : { month: month, year: year }), { preserveState: false })
}

// ── Bar chart ──────────────────────────────────────────────────────────────────
const CHART_COLORS = [
    'rgba(99,179,237,.85)', 'rgba(154,117,234,.85)', 'rgba(72,187,120,.85)',
    'rgba(246,173,85,.85)', 'rgba(252,129,74,.85)',  'rgba(237,100,166,.85)',
    'rgba(99,210,197,.85)', 'rgba(239,68,68,.85)',
]

const coloredChartData = computed(() => {
    if (!props.categoriesData?.datasets) return props.categoriesData
    return {
        ...props.categoriesData,
        datasets: props.categoriesData.datasets.map(ds => ({
            ...ds,
            backgroundColor: props.categoriesData.labels.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]),
            borderRadius: 4,
            borderSkipped: false,
        })),
    }
})

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { labels: { color: '#d1d5db' } },
        tooltip: { callbacks: { label: ctx => ` ${formatCurrency(ctx.raw)}` } },
    },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
        y: { ticks: { color: '#9ca3af', callback: v => formatCurrency(v) }, grid: { color: '#374151' } },
    },
}

// ── Line chart ─────────────────────────────────────────────────────────────────
const lineChartData = computed(() => ({
    labels: props.monthlyData.map(d => d.month),
    datasets: [
        {
            label: 'Receitas',
            data: props.monthlyData.map(d => d.income),
            borderColor: 'rgb(74,222,128)',
            backgroundColor: 'rgba(74,222,128,.12)',
            fill: true,
            tension: 0.3,
            pointBackgroundColor: 'rgb(74,222,128)',
        },
        {
            label: 'Despesas',
            data: props.monthlyData.map(d => d.expense),
            borderColor: 'rgb(248,113,113)',
            backgroundColor: 'rgba(248,113,113,.12)',
            fill: true,
            tension: 0.3,
            pointBackgroundColor: 'rgb(248,113,113)',
        },
    ],
}))

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { labels: { color: '#d1d5db' } },
        tooltip: { callbacks: { label: ctx => ` ${formatCurrency(ctx.raw)}` } },
    },
    scales: {
        x: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
        y: { ticks: { color: '#9ca3af', callback: v => formatCurrency(v) }, grid: { color: '#374151' } },
    },
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <!-- Period navigation -->
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-200">{{ periodLabel }}</h3>
                    <div class="flex gap-2">
                        <button
                            @click="navigate(-1)"
                            class="px-3 py-1 text-sm bg-gray-800 border border-gray-700 rounded-lg text-gray-300 hover:bg-gray-700 transition"
                        >← Anterior</button>
                        <button
                            @click="navigate(0)"
                            class="px-3 py-1 text-sm bg-gray-800 border border-gray-700 rounded-lg text-gray-300 hover:bg-gray-700 transition"
                        >Hoje</button>
                        <button
                            @click="navigate(1)"
                            class="px-3 py-1 text-sm bg-gray-800 border border-gray-700 rounded-lg text-gray-300 hover:bg-gray-700 transition"
                        >Próximo →</button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="p-6 bg-green-900/30 border border-green-800 rounded-lg shadow">
                        <div class="text-sm font-medium text-green-400 mb-1">Receitas</div>
                        <div class="text-2xl font-bold text-green-300">{{ formatCurrency(totalIncome) }}</div>
                    </div>
                    <div class="p-6 bg-red-900/30 border border-red-800 rounded-lg shadow">
                        <div class="text-sm font-medium text-red-400 mb-1">Despesas</div>
                        <div class="text-2xl font-bold text-red-300">{{ formatCurrency(totalExpenses) }}</div>
                    </div>
                    <div class="p-6 bg-blue-900/30 border border-blue-800 rounded-lg shadow">
                        <div class="text-sm font-medium text-blue-400 mb-1">Saldo</div>
                        <div class="text-2xl font-bold text-blue-300">{{ formatCurrency(totalIncome - totalExpenses) }}</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="p-6 bg-gray-900 border border-gray-700 rounded-lg shadow">
                        <h3 class="mb-4 text-sm font-medium text-gray-400 uppercase tracking-wide">Despesas por Categoria</h3>
                        <div class="h-64">
                            <BarChart
                                v-if="categoriesData && categoriesData.labels"
                                :chartData="coloredChartData"
                                :options="barOptions"
                            />
                            <p v-else class="text-gray-500 text-sm text-center mt-20">Sem dados no período.</p>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-900 border border-gray-700 rounded-lg shadow">
                        <h3 class="mb-4 text-sm font-medium text-gray-400 uppercase tracking-wide">Evolução dos últimos 6 meses</h3>
                        <div class="h-64">
                            <LineChart :chartData="lineChartData" :options="lineOptions" />
                        </div>
                    </div>
                </div>

                <!-- Últimas Transações -->
                <DataTable :columns="columnsTransactions" :rows="transactions">
                    <template #title>
                        <span class="text-gray-50 text-base font-semibold block mb-4">Últimas Saídas</span>
                    </template>
                </DataTable>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
