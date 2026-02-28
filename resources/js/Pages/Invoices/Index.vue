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
    invoices: Object,
    filters: Object,
    statuses: Array,
    canCreate: Boolean,
    canEdit: Boolean,
    canDelete: Boolean,
});

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const showDeleteModal = ref(false);
const invoiceToDelete = ref(null);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(statusFilter, () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(route('invoices.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        sort: props.filters.sort,
        direction: props.filters.direction,
    }, {
        preserveState: true,
        replace: true,
    });
};

const sort = (field) => {
    const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('invoices.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
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

const openDeleteModal = (invoice) => {
    invoiceToDelete.value = invoice;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    useForm({}).delete(route('invoices.destroy', invoiceToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            invoiceToDelete.value = null;
        },
    });
};

const getStatusClasses = (color) => {
    const classes = {
        gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[color] || classes.gray;
};
</script>

<template>
    <Head title="Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Invoices
                </h2>
                <Link v-if="canCreate" :href="route('invoices.create')">
                    <PrimaryButton>Create Invoice</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4 flex flex-col sm:flex-row gap-4">
                            <TextInput
                                v-model="search"
                                type="text"
                                class="w-full sm:max-w-md"
                                placeholder="Search by invoice number or client..."
                            />
                            <select
                                v-model="statusFilter"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="">All Statuses</option>
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div v-if="invoices.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No invoices</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ search || statusFilter ? 'No invoices match your filters.' : 'Get started by creating your first invoice.' }}
                            </p>
                            <div v-if="canCreate && !search && !statusFilter" class="mt-6">
                                <Link :href="route('invoices.create')">
                                    <PrimaryButton>Create Invoice</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th @click="sort('invoice_number')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Invoice{{ getSortIcon('invoice_number') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th @click="sort('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Status{{ getSortIcon('status') }}
                                        </th>
                                        <th @click="sort('issue_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Issue Date{{ getSortIcon('issue_date') }}
                                        </th>
                                        <th @click="sort('due_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Due Date{{ getSortIcon('due_date') }}
                                        </th>
                                        <th @click="sort('total')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-100">
                                            Total{{ getSortIcon('total') }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                {{ invoice.invoice_number }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('clients.show', invoice.client.id)" class="text-sm text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ invoice.client.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusClasses(invoice.status_color)]">
                                                {{ invoice.status_label }}
                                            </span>
                                            <span v-if="invoice.is_overdue && invoice.status !== 'overdue'" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                                Overdue
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ invoice.issue_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="invoice.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400'">
                                            {{ invoice.due_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                                            ${{ invoice.total }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 mr-4">
                                                View
                                            </Link>
                                            <Link v-if="canEdit && invoice.status === 'draft'" :href="route('invoices.edit', invoice.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">
                                                Edit
                                            </Link>
                                            <button v-if="canDelete" @click="openDeleteModal(invoice)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="invoices.data.length > 0 && invoices.links.length > 3" class="mt-4 flex justify-center">
                            <nav class="flex space-x-1">
                                <template v-for="link in invoices.links" :key="link.label">
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
                    Delete Invoice
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete {{ invoiceToDelete?.invoice_number }}? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton class="ml-3" @click="confirmDelete">
                        Delete Invoice
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
