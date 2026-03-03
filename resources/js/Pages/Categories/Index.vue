<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array
});

const form = useForm({
    name: ''
});

const editingCategory = ref(null);

const editCategory = (category) => {
    editingCategory.value = { ...category };
};

const saveCategory = () => {
    if (editingCategory.value.id) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => {
                editingCategory.value = null;
                form.reset();
            }
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => form.reset()
        });
    }
};

const deleteCategory = (category) => {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        form.delete(route('categories.destroy', category.id));
    }
};
</script>

<template>
    <Head title="Categorias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Categorias</h2>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-900">Voltar ao Dashboard</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 bg-white rounded-lg shadow">
                    <!-- Formulário -->
                    <form @submit.prevent="saveCategory" class="mb-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div class="md:col-span-3">
                                <label class="block mb-2 text-sm font-medium text-gray-700">Nome da Categoria</label>
                                <input
                                    v-if="editingCategory"
                                    v-model="editingCategory.name"
                                    type="text"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                                <input
                                    v-else
                                    v-model="form.name"
                                    type="text"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                    required
                                >
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    {{ editingCategory ? 'Atualizar' : 'Adicionar' }}
                                </button>
                                <button
                                    v-if="editingCategory"
                                    @click="editingCategory = null; form.reset()"
                                    type="button"
                                    class="w-full px-4 py-2 ml-2 text-white bg-gray-500 rounded-md hover:bg-gray-600"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabela de Categorias -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="category in categories" :key="category.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="editCategory(category)"
                                            class="mr-2 text-blue-600 hover:text-blue-900"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            @click="deleteCategory(category)"
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
