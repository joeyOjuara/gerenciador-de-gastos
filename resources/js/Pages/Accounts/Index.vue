<script setup>
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useCurrencyInput } from 'vue-currency-input'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import { tableSchemas } from '@/Objects/tableSchemas'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    accounts: Array,
})

const toast = useToast()

const form = useForm({
    id: null,
    name: '',
    initial_balance: 0,
})

const transferForm = useForm({
    from_account_id: '',
    to_account_id: '',
    amount: 0,
})

const showConfirm = ref(false)
const pendingDelete = ref(null)

const { inputRef, numberValue, setValue } = useCurrencyInput({ locale: 'pt-BR', currency: 'BRL', precision: 2 })
const { inputRef: transferInputRef, numberValue: transferNumberValue, setValue: setTransferValue } = useCurrencyInput({ locale: 'pt-BR', currency: 'BRL', precision: 2 })

watch(numberValue, (value) => { form.initial_balance = value ?? 0 })
watch(transferNumberValue, (value) => { transferForm.amount = value ?? 0 })

const editAccount = (account) => {
    form.clearErrors()
    form.id = account.id
    form.name = account.name
    setValue(Number(account.initial_balance))
}

const resetForm = () => {
    form.reset()
    form.clearErrors()
    setValue(0)
}

const saveAccount = () => {
    form.clearErrors()

    const options = {
        onSuccess: () => { resetForm(); toast.show('Conta salva com sucesso!') },
        onError: () => toast.show('Erro ao salvar conta.', 'error'),
    }

    form.id
        ? form.put(route('accounts.update', form.id), options)
        : form.post(route('accounts.store'), options)
}

const deleteAccount = (account) => {
    resetForm()
    pendingDelete.value = account
    showConfirm.value = true
}

const confirmDelete = () => {
    form.delete(route('accounts.destroy', pendingDelete.value.id), {
        onSuccess: () => toast.show('Conta excluída.'),
        onError: () => toast.show('Não foi possível excluir a conta.', 'error'),
    })
    showConfirm.value = false
    pendingDelete.value = null
}

const transfer = () => {
    transferForm.post(route('accounts.transfer'), {
        onSuccess: () => { transferForm.reset(); setTransferValue(0); toast.show('Transferência realizada!') },
        onError: () => toast.show('Erro ao transferir.', 'error'),
    })
}

const columns = [
    ...tableSchemas.accounts,
    { label: 'Ações', key: 'actions' },
]

const inputClass = 'w-full p-2 bg-gray-800 border border-gray-700 text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <Head title="Contas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-50">Contas</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="p-6 bg-gray-900 rounded-lg shadow border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide mb-6">Gerenciar Conta</h3>
                    <form @submit.prevent="saveAccount" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div class="md:col-span-2">
                            <InputLabel value="Nome da Conta" class="mb-1" />
                            <input v-model="form.name" type="text" :class="inputClass" placeholder="Banco, Caixinha, Cartão..." required>
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Saldo Inicial" class="mb-1" />
                            <input ref="inputRef" type="text" :class="inputClass" required>
                            <InputError :message="form.errors.initial_balance" class="mt-1" />
                        </div>

                        <div class="flex items-end gap-2 md:col-span-2">
                            <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                                {{ form.id ? 'Atualizar' : 'Adicionar' }}
                            </button>
                            <button v-if="form.id" type="button" @click="resetForm" class="px-6 py-2 text-white bg-gray-600 rounded-md hover:bg-gray-700 transition">Cancelar</button>
                        </div>
                    </form>
                </div>

                <div class="p-6 bg-gray-900 rounded-lg shadow border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wide mb-6">Transferência entre Contas</h3>
                    <form @submit.prevent="transfer" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <InputLabel value="Conta de Origem" class="mb-1" />
                            <select v-model="transferForm.from_account_id" :class="inputClass" required>
                                <option value="" disabled>Selecione...</option>
                                <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                            </select>
                            <InputError :message="transferForm.errors.from_account_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Conta de Destino" class="mb-1" />
                            <select v-model="transferForm.to_account_id" :class="inputClass" required>
                                <option value="" disabled>Selecione...</option>
                                <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                            </select>
                            <InputError :message="transferForm.errors.to_account_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Valor" class="mb-1" />
                            <input ref="transferInputRef" type="text" :class="inputClass" required>
                            <InputError :message="transferForm.errors.amount" class="mt-1" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-6 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 transition">Transferir</button>
                        </div>
                    </form>
                </div>

                <DataTable :columns="columns" :rows="accounts" :perPage="20" :search_field="true">
                    <template #actions="{ row }">
                        <button @click="editAccount(row)" class="mr-2 text-blue-400 hover:text-blue-300 transition">Editar</button>
                        <button @click="deleteAccount(row)" class="text-red-400 hover:text-red-300 transition">Excluir</button>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>

    <ConfirmModal
        :show="showConfirm"
        message="Tem certeza que deseja excluir esta conta?"
        @confirm="confirmDelete"
        @cancel="showConfirm = false"
    />
</template>
