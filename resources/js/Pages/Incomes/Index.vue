<script setup>
import { ref, watch, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { useCurrencyInput } from 'vue-currency-input'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { tableSchemas } from '@/Objects/tableSchemas'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    transactions: Object,   // paginated
    categories:   Array,
    payments:     Array,
    filters:      Object,
})

const toast = useToast()

// ── Seleção em massa ───────────────────────────────────────────────────────────
const selectedIds       = ref([])
const showBulkConfirm   = ref(false)

const isAllSelected = computed(() =>
    props.transactions.data.length > 0 &&
    props.transactions.data.every(t => selectedIds.value.includes(t.id))
)

const toggleAll = () => {
    if (isAllSelected.value) selectedIds.value = []
    else selectedIds.value = props.transactions.data.map(t => t.id)
}

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id)
    if (idx > -1) selectedIds.value.splice(idx, 1)
    else selectedIds.value.push(id)
}

const confirmBulkDelete = () => {
    router.delete(route('transactions.destroyBulk'), {
        data: { ids: selectedIds.value },
        onSuccess: () => {
            toast.show(`${selectedIds.value.length} transação(ões) excluída(s).`)
            selectedIds.value = []
            showBulkConfirm.value = false
        },
        onError: () => toast.show('Erro ao excluir transações.', 'error'),
    })
}

const form = useForm({
    id:          null,
    type:        'income',
    description: '',
    amount:      '',
    date:        new Date().toISOString().split('T')[0],
    number:      ref(1),
    category_id: '',
    payment_id:  '',
})

const edit = ref(false)

const { inputRef, numberValue, setValue } = useCurrencyInput({ locale: 'pt-BR', currency: 'BRL', precision: 2 })

watch(numberValue, (value) => { form.amount = value })

// ── Filters ────────────────────────────────────────────────────────────────────
const filterCategory = ref(props.filters?.category_id ?? '')
const filterPayment  = ref(props.filters?.payment_id  ?? '')
const filterMonth    = ref(props.filters?.month ?? '')
const filterYear     = ref(props.filters?.year  ?? '')
const filterDescription = ref(props.filters?.description ?? '')

const enforceMax = () => {
    form.number = Math.min(12, Math.max(1, form.number));
};

function applyFilters() {
    router.get(route('incomes.index'), {
        category_id: filterCategory.value || undefined,
        payment_id:  filterPayment.value  || undefined,
        month:       filterMonth.value    || undefined,
        year:        filterYear.value     || undefined,
        description: filterDescription.value || undefined,
    }, { preserveState: true })
}

function clearFilters() {
    filterCategory.value = ''
    filterPayment.value  = ''
    filterMonth.value    = ''
    filterYear.value     = ''
    filterDescription.value = ''
    router.get(route('incomes.index'))
}

function editCancel() {
    form.reset()
    form.clearErrors()
    form.type = 'expense'
    setValue(null)
    edit.value = false
}

// ── CRUD ───────────────────────────────────────────────────────────────────────
const showConfirm  = ref(false)
const pendingDelete = ref(null)

const editTransaction = (transaction) => {
    edit.value = true
    form.clearErrors()
    form.id          = transaction.id
    form.description = transaction.description
    form.date        = transaction.date
    form.category_id = transaction.category_id
    form.payment_id  = transaction.payment_id
    setValue(Number(transaction.amount))
}

const saveTransaction = () => {
    form.clearErrors()

    if (!form.description.trim())   form.setError('description', 'A descrição é necessária')
    if (!form.amount || form.amount <= 0) form.setError('amount', 'Informe um valor maior que zero')
    if (!form.date)                 form.setError('date', 'A data é necessária')
    if (!form.category_id)          form.setError('category_id', 'Selecione uma categoria')
    if (!form.payment_id)           form.setError('payment_id', 'Selecione uma forma de pagamento')
    if (form.hasErrors) return

    const opts = {
        onSuccess: () => { form.reset(); form.id = null; form.type = 'income'; setValue(null); toast.show('Entrada salva com sucesso!') },
        onError:   () => toast.show('Erro ao salvar entrada.', 'error'),
    }

    form.id
        ? form.put(route('incomes.update', form.id), opts)
        : form.post(route('incomes.store'), opts)
}

const deleteTransaction = (t) => { form.reset(); pendingDelete.value = t; showConfirm.value = true }

const confirmDelete = () => {
    form.delete(route('incomes.destroy', pendingDelete.value.id), {
        onSuccess: () => toast.show('Entrada excluída.'),
        onError:   () => toast.show('Erro ao excluir.', 'error'),
    })
    showConfirm.value   = false
    pendingDelete.value = null
}

const cancelDelete = () => { showConfirm.value = false; pendingDelete.value = null }

const changePage = (page) => router.get(route('incomes.index'), { ...props.filters, page }, { preserveState: true })

const columns = [
    { label: '', key: 'select' },
    ...tableSchemas.transactions,
    { label: 'Ações', key: 'actions' },
]

const inputClass      = 'w-full p-2 bg-gray-800 border border-gray-700 text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
const inputErrorClass = 'w-full p-2 bg-gray-800 border border-red-500 text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500'
</script>

<template>
    <Head title="Entradas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-50">Entradas</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Formulário -->
                <div class="p-6 bg-gray-900 rounded-lg shadow border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide mb-6">Nova Entrada</h3>
                    <form @submit.prevent="saveTransaction">
                        <input type="hidden" v-model="form.type" />
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                            <!-- Descrição -->
                            <div class="md:col-span-2">
                                <InputLabel value="Descrição" class="mb-1" />
                                <input v-model="form.description" type="text" :class="form.errors.description ? inputErrorClass : inputClass" required>
                                <InputError :message="form.errors.description" class="mt-1" />
                            </div>

                            <!-- Valor -->
                            <div>
                                <InputLabel value="Valor (R$)" class="mb-1" />
                                <input ref="inputRef" type="text" :class="form.errors.amount ? inputErrorClass : inputClass" required>
                                <InputError :message="form.errors.amount" class="mt-1" />
                            </div>

                            <!-- Data -->
                            <div>
                                <InputLabel value="Data" class="mb-1" />
                                <input v-model="form.date" type="date" :class="form.errors.date ? inputErrorClass : inputClass" required>
                                <InputError :message="form.errors.date" class="mt-1" />
                            </div>

                            <!-- Categoria -->
                            <div>
                                <InputLabel value="Categoria" class="mb-1" />
                                <select v-model="form.category_id" :class="form.errors.category_id ? inputErrorClass : inputClass" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-1" />
                            </div>

                            <!-- Forma de Pagamento -->
                            <div>
                                <InputLabel value="Conta / Origem" class="mb-1" />
                                <select v-model="form.payment_id" :class="form.errors.payment_id ? inputErrorClass : inputClass" required>
                                    <option value="" disabled>Selecione...</option>
                                    <option v-for="p in payments" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <InputError :message="form.errors.payment_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- Repetição -->
                        <div class="mt-4">
                            <InputLabel value="Repetir Transacao" class="max-w-xs" />
                            <input
                                v-model="form.number"
                                type="number"
                                :class="form.errors.number ? inputErrorClass : inputClass + ' max-w-24'"
                                required
                                @input="enforceMax"
                            >
                        </div>

                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                                {{ form.id ? 'Atualizar' : 'Adicionar' }}
                            </button>
                            <button v-if="form.id" type="button"
                                @click="editCancel"
                                class="px-6 py-2 text-white bg-gray-600 rounded-md hover:bg-gray-700 transition"
                            >Cancelar</button>
                        </div>
                    </form>
                </div>

                <!-- Filtros -->
                <div class="p-4 bg-gray-900 rounded-lg border border-gray-700 flex flex-wrap gap-3 items-end">
                    <div>
                        <InputLabel value="Descrição" class="mb-1" />
                        <input v-model="filterDescription" type="text" placeholder="Descrição" :class="inputClass + ' text-sm w-24'">
                    </div>
                    <div>
                        <InputLabel value="Categoria" class="mb-1" />
                        <select v-model="filterCategory" :class="inputClass + ' text-sm'">
                            <option value="">Todas</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Conta / Origem" class="mb-1" />
                        <select v-model="filterPayment" :class="inputClass + ' text-sm'">
                            <option value="">Todas</option>
                            <option v-for="p in payments" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Mês" class="mb-1" />
                        <select v-model="filterMonth" :class="inputClass + ' text-sm'">
                            <option value="">Todos</option>
                            <option v-for="m in 12" :key="m" :value="m">{{ m.toString().padStart(2,'0') }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Ano" class="mb-1" />
                        <input v-model="filterYear" type="number" placeholder="2026" :class="inputClass + ' text-sm w-24'">
                    </div>
                    <button @click="applyFilters" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">Filtrar</button>
                    <button @click="clearFilters" class="px-4 py-2 text-sm text-gray-300 bg-gray-700 rounded-md hover:bg-gray-600 transition">Limpar</button>
                </div>

                <!-- Tabela -->
                <DataTable
                    :columns="columns"
                    :rows="transactions.data"
                    :meta="transactions"
                    :serverSide="true"
                    @page-change="changePage"
                >
                    <template #title>
                        <div class="flex items-center justify-between mb-4">
                            <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer select-none">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleAll" class="w-4 h-4 accent-blue-500" />
                                Selecionar todos
                            </label>
                            <button
                                v-if="selectedIds.length > 0"
                                @click="showBulkConfirm = true"
                                class="px-4 py-1.5 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 transition"
                            >
                                Excluir selecionados ({{ selectedIds.length }})
                            </button>
                        </div>
                    </template>

                    <template #select="{ row }">
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(row.id)"
                            @change="toggleSelect(row.id)"
                            class="w-4 h-4 accent-blue-500"
                        />
                    </template>

                    <template #actions="{ row }">
                        <button @click="editTransaction(row)" class="mr-2 text-blue-400 hover:text-blue-300 transition">Editar</button>
                        <button @click="deleteTransaction(row)" class="text-red-400 hover:text-red-300 transition">Excluir</button>
                    </template>
                </DataTable>

            </div>
        </div>
    </AuthenticatedLayout>

    <ConfirmModal
        :show="showConfirm"
        message="Tem certeza que deseja excluir esta entrada?"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
    />

    <ConfirmModal
        :show="showBulkConfirm"
        :message="`Tem certeza que deseja excluir ${selectedIds.length} transação(ões)?`"
        @confirm="confirmBulkDelete"
        @cancel="showBulkConfirm = false"
    />
</template>
