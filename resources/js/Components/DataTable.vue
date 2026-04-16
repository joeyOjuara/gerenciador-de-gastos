<script setup>
import { ref, computed } from "vue"

const props = defineProps({
    columns:    Array,
    rows:       Array,
    perPage:    Number,
    search_field: { type: Boolean, default: false },
    // server-side pagination
    serverSide: { type: Boolean, default: false },
    meta:       { type: Object, default: null },  // { current_page, last_page, per_page, total }
})

const emit = defineEmits(['page-change'])

const search        = ref("")
const currentPage   = ref(1)
const sortKey       = ref(null)
const sortDirection = ref("asc")

function getValue(obj, path) {
    return path.split('.').reduce((o, i) => (o ? o[i] : null), obj)
}

function sortBy(key) {
    if (props.serverSide) return
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc"
    } else {
        sortKey.value = key
        sortDirection.value = "asc"
    }
}

const filteredRows = computed(() => {
    if (props.serverSide) return props.rows
    if (!search.value) return props.rows
    return props.rows.filter(row =>
        props.columns.some(col => {
            const value = getValue(row, col.key)
            return String(value ?? "").toLowerCase().includes(search.value.toLowerCase())
        })
    )
})

const sortedRows = computed(() => {
    if (props.serverSide || !sortKey.value) return filteredRows.value
    return [...filteredRows.value].sort((a, b) => {
        const valA = getValue(a, sortKey.value)
        const valB = getValue(b, sortKey.value)
        if (valA === valB) return 0
        return sortDirection.value === "asc" ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1)
    })
})

const totalPages = computed(() => {
    if (props.serverSide) return props.meta?.last_page ?? 1
    if (!props.perPage) return 1
    return Math.ceil(sortedRows.value.length / props.perPage)
})

const activePage = computed(() => {
    if (props.serverSide) return props.meta?.current_page ?? 1
    return currentPage.value
})

const paginatedRows = computed(() => {
    if (props.serverSide) return props.rows
    if (!props.perPage) return sortedRows.value
    const start = (currentPage.value - 1) * props.perPage
    return sortedRows.value.slice(start, start + props.perPage)
})

function prevPage() {
    if (props.serverSide) {
        if ((props.meta?.current_page ?? 1) > 1) emit('page-change', props.meta.current_page - 1)
    } else {
        if (currentPage.value > 1) currentPage.value--
    }
}

function nextPage() {
    if (props.serverSide) {
        if ((props.meta?.current_page ?? 1) < (props.meta?.last_page ?? 1)) emit('page-change', props.meta.current_page + 1)
    } else {
        if (currentPage.value < totalPages.value) currentPage.value++
    }
}
</script>

<template>
<div class="p-6 bg-gray-900 border border-gray-700 rounded-lg shadow">
    <slot name="title"/>

    <div class="flex justify-between mb-4">
        <input
            v-if="search_field"
            v-model="search"
            type="text"
            placeholder="Buscar..."
            class="px-3 py-2 text-sm text-gray-200 bg-gray-800 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-800">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        @click="sortBy(col.key)"
                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-50 uppercase select-none"
                        :class="!serverSide ? 'cursor-pointer' : ''"
                    >
                        {{ col.label }}
                        <span v-if="!serverSide && sortKey === col.key">
                            {{ sortDirection === 'asc' ? '▲' : '▼' }}
                        </span>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                <tr v-if="paginatedRows.length === 0">
                    <td :colspan="columns.length" class="px-6 py-8 text-center text-sm text-gray-500">
                        Nenhum registro encontrado.
                    </td>
                </tr>

                <tr
                    v-for="row in paginatedRows"
                    :key="row.id"
                    class="transition hover:bg-gray-800"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        class="px-6 py-4 text-gray-200 whitespace-nowrap"
                    >
                        <slot v-if="$slots[col.key]" :name="col.key" :row="row" />
                        <span v-else>
                            {{ col.format ? col.format(getValue(row, col.key), row) : getValue(row, col.key) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="totalPages > 1" class="flex items-center justify-between mt-4">
        <button
            @click="prevPage"
            :disabled="activePage === 1"
            class="px-3 py-1 text-sm text-gray-200 bg-gray-800 border border-gray-700 rounded hover:bg-gray-700 disabled:opacity-40"
        >Anterior</button>

        <span class="text-sm text-gray-400">
            Página {{ activePage }} de {{ totalPages }}
            <span v-if="serverSide && meta"> · {{ meta.total }} registro(s)</span>
        </span>

        <button
            @click="nextPage"
            :disabled="activePage === totalPages"
            class="px-3 py-1 text-sm text-gray-200 bg-gray-800 border border-gray-700 rounded hover:bg-gray-700 disabled:opacity-40"
        >Próxima</button>
    </div>
</div>
</template>
