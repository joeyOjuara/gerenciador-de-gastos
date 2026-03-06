<script setup>
import { ref, computed } from "vue"

const props = defineProps({
    columns: Array,
    rows: Array,
    perPage: Number,
    search_field: {
        type: Boolean,
        default: false
    }
})

const search = ref("")
const currentPage = ref(1)
const sortKey = ref(null)
const sortDirection = ref("asc")

function getValue(obj, path) {
    return path.split('.').reduce((o, i) => (o ? o[i] : null), obj)
}

function sortBy(key) {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc"
    } else {
        sortKey.value = key
        sortDirection.value = "asc"
    }
}

const filteredRows = computed(() => {
    if (!search.value) return props.rows

    return props.rows.filter(row => {
        return props.columns.some(col => {
            const value = getValue(row, col.key)
            return String(value ?? "")
                .toLowerCase()
                .includes(search.value.toLowerCase())
        })
    })
})

const sortedRows = computed(() => {
    if (!sortKey.value) return filteredRows.value

    return [...filteredRows.value].sort((a, b) => {
        const valA = getValue(a, sortKey.value)
        const valB = getValue(b, sortKey.value)

        if (valA === valB) return 0

        if (sortDirection.value === "asc") {
            return valA > valB ? 1 : -1
        }

        return valA < valB ? 1 : -1
    })
})

const totalPages = computed(() =>
    Math.ceil(sortedRows.value.length / props.perPage)
)

const paginatedRows = computed(() => {
    if(!props.perPage) {
        return props.rows
    }
    const start = (currentPage.value - 1) * props.perPage
    console.log(start)
    return sortedRows.value.slice(start, start + props.perPage)
})

function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage() {
    if (currentPage.value > 1) currentPage.value--
}
</script>

<template>
<div class="p-6 bg-gray-900 border border-gray-700 rounded-lg shadow">
    <slot name="title"/>

    <!-- SEARCH -->
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

            <!-- HEADER -->
            <thead class="bg-gray-800">

                <tr>

                    <th
                        v-for="col in columns"
                        :key="col.key"
                        @click="sortBy(col.key)"
                        class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-50 uppercase cursor-pointer select-none"
                    >

                        {{ col.label }}

                        <span v-if="sortKey === col.key">
                            {{ sortDirection === 'asc' ? '▲' : '▼' }}
                        </span>

                    </th>

                </tr>

            </thead>

            <!-- BODY -->
            <tbody class="divide-y divide-gray-800">

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

                        <slot
                            v-if="$slots[col.key]"
                            :name="col.key"
                            :row="row"
                        />

                        <span v-else>
                            {{ col.format
                                ? col.format(getValue(row, col.key), row)
                                : getValue(row, col.key)
                            }}
                        </span>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->
    <div v-if="perPage && totalPages" class="flex items-center justify-between mt-4">

        <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm text-gray-200 bg-gray-800 border border-gray-700 rounded hover:bg-gray-700 disabled:opacity-40"
        >
            Anterior
        </button>

        <span class="text-sm text-gray-400">
            Página {{ currentPage }} de {{ totalPages }}
        </span>

        <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm text-gray-200 bg-gray-800 border border-gray-700 rounded hover:bg-gray-700 disabled:opacity-40"
        >
            Próxima
        </button>

    </div>

</div>
</template>
