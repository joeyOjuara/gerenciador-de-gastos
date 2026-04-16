<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import DataTable from '@/Components/DataTable.vue';
    import ConfirmModal from '@/Components/ConfirmModal.vue';
    import TextInput from '@/Components/TextInput.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import InputError from '@/Components/InputError.vue';
    import { tableSchemas } from '@/Objects/tableSchemas';

    const props = defineProps({
        payments: Array
    });

    const form = useForm({
        id: null,
        name: ''
    });

    const showConfirm = ref(false)
    const pendingDelete = ref(null)

    const editPayment = (payment) => {
        form.clearErrors()
        form.id = payment.id
        form.name = payment.name
    };

    const savePayment = () => {
        form.clearErrors()
        if (form.id) {
            form.put(route('payments.update', form.id), {
                onSuccess: () => form.reset()
            });
        } else {
            form.post(route('payments.store'), {
                onSuccess: () => form.reset()
            });
        }
    };

    const deletePayment = (payment) => {
        form.reset()
        pendingDelete.value = payment
        showConfirm.value = true
    };

    const confirmDelete = () => {
        form.delete(route('payments.destroy', pendingDelete.value.id))
        showConfirm.value = false
        pendingDelete.value = null
    };

    const cancelDelete = () => {
        showConfirm.value = false
        pendingDelete.value = null
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
    <Head title="Formas de Pagamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-50">Formas de Pagamento</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-gray-900 rounded-lg shadow border border-gray-700">
                    <!-- Formulário -->
                    <form @submit.prevent="savePayment" class="mb-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div class="md:col-span-3">
                                <InputLabel value="Forma de Pagamento" class="mb-2" />
                                <TextInput v-model="form.name" required />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>
                            <div class="flex items-end gap-2">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition"
                                >
                                    {{ form.id ? 'Atualizar' : 'Adicionar' }}
                                </button>
                                <button
                                    v-if="form.id"
                                    @click="form.reset(); form.clearErrors()"
                                    type="button"
                                    class="w-full px-4 py-2 text-white bg-gray-600 rounded-md hover:bg-gray-700 transition"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Formas de Pagamento -->
                    <DataTable
                        :columns="columns"
                        :rows="payments"
                        :perPage="20"
                        :search_field="true"
                    >
                        <template #actions="{row}">
                            <button
                                @click="editPayment(row)"
                                class="mr-2 text-blue-400 hover:text-blue-300 transition"
                            >
                                Editar
                            </button>
                            <button
                                @click="deletePayment(row)"
                                class="text-red-400 hover:text-red-300 transition"
                            >
                                Excluir
                            </button>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <ConfirmModal
        :show="showConfirm"
        message="Tem certeza que deseja excluir esta forma de pagamento?"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
    />
</template>
