<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Array,
    categories: Array
});

const form = useForm({
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    category_id: ''
});

const editingTransaction = ref(null);

const editTransaction = (transaction) => {
    editingTransaction.value = { ...transaction };
};

const saveTransaction = () => {
    if (editingTransaction.value.id) {
        form.put(route('transactions.update', editingTransaction.value.id), {
            onSuccess: () => {
                editingTransaction.value = null;
                form.reset();
            }
        });
    } else {
        form.post(route('transactions.store'), {
            onSuccess: () => form.reset()
        });
    }
};

const deleteTransaction = (transaction) => {
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
                                    v-if="editingTransaction"
                                    v-model="editingTransaction.description"
                                    type="text"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <input
                                    v-else
                                    v-model="form.description"
                                    type="text"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                            </div>

                            <!-- Valor -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Valor (R$)</label>
                                <input
                                    v-if="editingTransaction"
                                    v-model="editingTransaction.amount"
                                    type="number"
                                    step="0.01"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <input
                                    v-else
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                            </div>

                            <!-- Data -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Data</label>
                                <input
                                    v-if="editingTransaction"
                                    v-model="editingTransaction.date"
                                    type="date"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <input
                                    v-else
                                    v-model="form.date"
                                    type="date"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Categoria</label>
                                <select
                                    v-if="editingTransaction"
                                    v-model="editingTransaction.category_id"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <select
                                    v-else
                                    v-model="form.category_id"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Botões -->
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ editingTransaction ? 'Atualizar' : 'Adicionar' }}
                                </button>
                                <button
                                    v-if="editingTransaction"
                                    @click="editingTransaction = null; form.reset()"
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
                                    <td class="px-6 py-4 whitespace-nowrap">{{ new Date(transaction.date).toLocaleDateString() }}</td>
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
