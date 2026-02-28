<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clients: Object,
    filters: Object,
    canCreate: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const search = ref(props.filters.search ?? '');
const showDeleteModal = ref(false);
const clientToDelete = ref(null);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('clients.index'), { search: value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const sort = (field) => {
    const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('clients.index'), {
        search: search.value,
        sort: field,
        direction: direction,
    }, {
        preserveState: true,
        replace: true,
    });
};

const getSortIcon = (field) => {
    if (props.filters.sort !== field) return '';
    return props.filters.direction === 'asc' ? ' ↑' : ' ↓';
};

const openDeleteModal = (client) => {
    clientToDelete.value = client;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    useForm({}).delete(route('clients.destroy', clientToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            clientToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clients
                </h2>
                <Link v-if="canCreate" :href="route('clients.create')">
                    <PrimaryButton>Add Client</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4">
                            <TextInput
                                v-model="search"
                                type="text"
                                class="w-full max-w-md"
                                placeholder="Search clients by name, email, or phone..."
                            />
                        </div>

                        <div v-if="clients.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No clients</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ search ? 'No clients match your search.' : 'Get started by creating a new client.' }}
                            </p>
                            <div v-if="canCreate && !search" class="mt-6">
                                <Link :href="route('clients.create')">
                                    <PrimaryButton>Add Client</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Name{{ getSortIcon('name') }}
                                        </th>
                                        <th @click="sort('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Email{{ getSortIcon('email') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Phone
                                        </th>
                                        <th @click="sort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Created{{ getSortIcon('created_at') }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="client in clients.data" :key="client.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('clients.show', client.id)" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                {{ client.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ client.email || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ client.phone || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(client.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('clients.show', client.id)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
                                                View
                                            </Link>
                                            <Link v-if="canEdit" :href="route('clients.edit', client.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">
                                                Edit
                                            </Link>
                                            <button v-if="canDelete" @click="openDeleteModal(client)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="clients.data.length > 0 && clients.links.length > 3" class="mt-4 flex justify-center">
                            <nav class="flex space-x-1">
                                <template v-for="link in clients.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-2 text-sm rounded-md"
                                        :class="{
                                            'bg-indigo-600 text-white': link.active,
                                            'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active,
                                        }"
                                        v-html="link.label"
                                        preserve-state
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Delete Client
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete {{ clientToDelete?.name }}? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton class="ml-3" @click="confirmDelete">
                        Delete Client
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
