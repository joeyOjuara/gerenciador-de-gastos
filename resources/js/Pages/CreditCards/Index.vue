<script setup>
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useCurrencyInput } from 'vue-currency-input'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import { tableSchemas } from '@/Objects/tableSchemas'
import { formatCurrency } from '@/utils/formatCurrency'
import { useToast } from '@/composables/useToast'

defineProps({ creditCards: Array, accounts: Array })
const toast = useToast()
const showConfirm = ref(false)
const pendingDelete = ref(null)
const payingInvoice = ref(null)

const form = useForm({ id: null, name: '', limit_amount: 0, closing_day: 1, due_day: 1 })
const payForm = useForm({ account_id: '', payment_date: new Date().toISOString().split('T')[0] })
const { inputRef, numberValue, setValue } = useCurrencyInput({ locale: 'pt-BR', currency: 'BRL', precision: 2 })
watch(numberValue, value => { form.limit_amount = value ?? 0 })

const reset = () => { form.reset(); form.clearErrors(); setValue(0) }
const editCard = card => { form.id = card.id; form.name = card.name; form.closing_day = card.closing_day; form.due_day = card.due_day; setValue(Number(card.limit_amount)) }
const saveCard = () => {
    const opts = { onSuccess: () => { reset(); toast.show('Cartão salvo!') }, onError: () => toast.show('Erro ao salvar cartão.', 'error') }
    form.id ? form.put(route('credit-cards.update', form.id), opts) : form.post(route('credit-cards.store'), opts)
}
const deleteCard = card => { pendingDelete.value = card; showConfirm.value = true }
const confirmDelete = () => form.delete(route('credit-cards.destroy', pendingDelete.value.id), { onSuccess: () => toast.show('Cartão excluído.'), onFinish: () => { showConfirm.value = false; pendingDelete.value = null } })
const openPay = invoice => { payingInvoice.value = invoice; payForm.reset(); payForm.payment_date = new Date().toISOString().split('T')[0] }
const payInvoice = () => payForm.post(route('credit-card-invoices.pay', payingInvoice.value.id), { onSuccess: () => { payingInvoice.value = null; toast.show('Fatura paga!') }, onError: () => toast.show('Erro ao pagar fatura.', 'error') })
const columns = [...tableSchemas.creditCards, { label: 'Ações', key: 'actions' }]
const inputClass = 'w-full p-2 bg-gray-800 border border-gray-700 text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <Head title="Cartões" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-50">Cartões de Crédito</h2></template>
        <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 bg-gray-900 border border-gray-700 rounded-lg">
                <h3 class="text-sm text-gray-400 uppercase mb-6">Gerenciar Cartão</h3>
                <form @submit.prevent="saveCard" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2"><InputLabel value="Nome" class="mb-1" /><input v-model="form.name" :class="inputClass" required><InputError :message="form.errors.name" /></div>
                    <div><InputLabel value="Limite" class="mb-1" /><input ref="inputRef" :class="inputClass" required><InputError :message="form.errors.limit_amount" /></div>
                    <div><InputLabel value="Fechamento" class="mb-1" /><input v-model="form.closing_day" type="number" min="1" max="31" :class="inputClass" required></div>
                    <div><InputLabel value="Vencimento" class="mb-1" /><input v-model="form.due_day" type="number" min="1" max="31" :class="inputClass" required></div>
                    <div class="flex items-end gap-2"><button class="px-4 py-2 bg-blue-600 text-white rounded">{{ form.id ? 'Atualizar' : 'Adicionar' }}</button><button v-if="form.id" type="button" @click="reset" class="px-4 py-2 bg-gray-700 text-white rounded">Cancelar</button></div>
                </form>
            </div>
            <DataTable :columns="columns" :rows="creditCards" :perPage="20" :search_field="true">
                <template #actions="{ row }"><button @click="editCard(row)" class="mr-2 text-blue-400">Editar</button><button @click="deleteCard(row)" class="text-red-400">Excluir</button></template>
            </DataTable>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="card in creditCards" :key="card.id" class="p-6 bg-gray-900 border border-gray-700 rounded-lg">
                    <h3 class="text-gray-100 font-semibold mb-4">Faturas - {{ card.name }}</h3>
                    <div v-for="invoice in card.invoices" :key="invoice.id" class="flex items-center justify-between py-2 border-b border-gray-800 text-sm">
                        <span class="text-gray-300">{{ String(invoice.reference_month).padStart(2,'0') }}/{{ invoice.reference_year }} · {{ invoice.status }}</span>
                        <div class="flex items-center gap-3"><span class="text-gray-100">{{ formatCurrency(invoice.transactions_sum_amount || 0) }}</span><button v-if="invoice.status !== 'paid'" @click="openPay(invoice)" class="px-3 py-1 bg-green-600 text-white rounded">Pagar</button></div>
                    </div>
                    <p v-if="!card.invoices?.length" class="text-gray-500 text-sm">Sem faturas.</p>
                </div>
            </div>
        </div></div>
    </AuthenticatedLayout>
    <ConfirmModal :show="showConfirm" message="Excluir este cartão?" @confirm="confirmDelete" @cancel="showConfirm = false" />
    <div v-if="payingInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
        <div class="w-full max-w-md p-6 bg-gray-900 border border-gray-700 rounded-lg">
            <h3 class="text-gray-100 font-semibold mb-4">Pagar Fatura</h3>
            <form @submit.prevent="payInvoice" class="space-y-4">
                <div><InputLabel value="Conta de origem" class="mb-1" /><select v-model="payForm.account_id" :class="inputClass" required><option value="" disabled>Selecione...</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select><InputError :message="payForm.errors.account_id" /></div>
                <div><InputLabel value="Data" class="mb-1" /><input v-model="payForm.payment_date" type="date" :class="inputClass"></div>
                <div class="flex justify-end gap-2"><button type="button" @click="payingInvoice = null" class="px-4 py-2 bg-gray-700 text-white rounded">Cancelar</button><button class="px-4 py-2 bg-green-600 text-white rounded">Pagar</button></div>
            </form>
        </div>
    </div>
</template>
