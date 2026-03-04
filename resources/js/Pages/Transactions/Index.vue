<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useCurrencyInput } from 'vue-currency-input';

const props = defineProps({
    transactions: Array,
    categories: Array
});

const form = useForm({
    id: null,
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    category_id: ''
});

const { inputRef, numberValue, setValue} = useCurrencyInput({
    locale: 'pt-BR',
    currency: 'BRL',
    precision: 2
})

watch(numberValue, (value) => {
    form.amount = value
})

const editTransaction = (transaction) => {
    console.log(transaction.amount)
    form.errors = []
    form.id = transaction.id
    form.description = transaction.description
    form.date = transaction.date
    form.category_id = transaction.category_id

    console.log(numberValue.value)
    setValue(Number(transaction.amount))
}

const saveTransaction = () => {
    form.errors = []
    if (form.id) {
        form.put(route('transactions.update', form.id), {
            onSuccess: () => {
                form.reset()
                form.errors = []
                setValue(null)
            }
        });
    } else {
        form.post(route('transactions.store'), {
            onSuccess: () => {
                form.reset()
                form.errors = []
                setValue(null)
            }
        });
    }
};

const deleteTransaction = (transaction) => {
    form.reset()
    form.errors = []
    if (confirm('Tem certeza que deseja excluir esta transação?')) {
        form.delete(route('transactions.destroy', transaction.id));
    }
};
</script>

<template>
    <Head title="Transações" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Transações</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-900">Voltar ao Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-white rounded-lg shadow">
                    <!-- Formulário -->
                    <form @submit.prevent="saveTransaction" class="mb-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-6">
                            <!-- Descrição -->
                            <div class="md:col-span-3">
                                <label class="block mb-2 text-sm font-medium text-gray-700">Descrição</label>
                                <input
                                    v-model="form.description"
                                    type="text"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 bg-red-100 border border-red-300 rounded-md">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Valor -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Valor (R$)</label>
                                <input
                                    ref="inputRef"
                                    type="text"
                                    step="0.01"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600 bg-red-100 border border-red-300 rounded-md">
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <!-- Data -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Data</label>
                                <input
                                    v-model="form.date"
                                    type="date"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <p v-if="form.errors.date" class="mt-1 text-sm text-red-600 bg-red-100 border border-red-300 rounded-md">
                                    {{ form.errors.date }}
                                </p>
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Categoria</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600 bg-red-100 border border-red-300 rounded-md">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Botões -->
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ form.id ? 'Atualizar' : 'Adicionar' }}
                                </button>
                                <button
                                    v-if="form.id"
                                    @click="form.reset(); form.errors = []; setValue(null)"
                                    type="button"
                                    class="w-full px-4 py-2 ml-2 text-white bg-gray-500 rounded-md hover:bg-gray-600"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Transações -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descrição</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Categoria</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Valor</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transaction in transactions" :key="transaction.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ transaction.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ transaction.category.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ {{ parseFloat(transaction.amount).toFixed(2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ transaction.date.split('-').reverse().join('/') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="editTransaction(transaction)"
                                            class="mr-2 text-blue-600 hover:text-blue-900"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            @click="deleteTransaction(transaction)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
