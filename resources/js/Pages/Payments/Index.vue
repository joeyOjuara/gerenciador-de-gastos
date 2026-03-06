<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import DataTable from '@/Components/DataTable.vue';
    import { tableSchemas } from '@/Objects/tableSchemas';

    const props = defineProps({
        payments: Array
    });

    const form = useForm({
        id: null,
        name: ''
    });

    const editPayment = (payment) => {
        form.errors = []
        form.id = payment.id
        form.name = payment.name
    };

    const savePayment = () => {
        form.errors = []
        if (form.id) {
            form.put(route('payments.update', form.id), {
                onSuccess: () => {
                    form.reset()
                    form.errors = []
                }
            });
        } else {
            form.post(route('payments.store'), {
                onSuccess: () => form.reset()
            });
        }
    };

    const deletePayment = (payment) => {
        form.reset()
        form.errors = []
        if (confirm('Tem certeza que deseja excluir esta forma de pagamento?')) {
            form.delete(route('payments.destroy', payment.id))
        }
    };

    const columns = [
        ...tableSchemas.payments,
        {
            label: "Ações",
            key: "actions",
        }
    ]

</script>

<template>
    <Head title="Pagamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-50">Pagamentos</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-gray rounded-lg shadow">
                    <!-- Formulário -->
                    <form @submit.prevent="savePayment" class="mb-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div class="md:col-span-3">
                                <label class="block mb-2 text-sm font-medium text-gray-50">Forma de Pagamento</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    :class="[
                                        'w-full p-2 border rounded-md',
                                        form.errors.name ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    required
                                >
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 bg-red-100 border border-red-300 rounded-md">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ form.id ? 'Atualizar' : 'Adicionar' }}
                                </button>
                                <button
                                    v-if="form.id"
                                    @click="form.reset(); form.errors = []"
                                    type="button"
                                    class="w-full px-4 py-2 ml-2 text-white bg-gray-500 rounded-md hover:bg-gray-600"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Categorias -->
                    <DataTable
                        :columns="columns"
                        :rows="payments"
                        :perPage="20"
                    >
                        <template #actions="{row}">
                            <button
                                @click="editPayment(row)"
                                class="mr-2 text-blue-600 hover:text-blue-900"
                            >
                                Editar
                            </button>
                            <button
                                @click="deletePayment(row)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Excluir
                            </button>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
