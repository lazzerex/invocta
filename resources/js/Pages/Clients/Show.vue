<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    client: Object,
    stats: Object,
    recentInvoices: Array,
    canEdit: Boolean,
    canDelete: Boolean,
});

const showDeleteModal = ref(false);

const confirmDelete = () => {
    useForm({}).delete(route('clients.destroy', props.client.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount || 0);
};

const statusColors = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    sent: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    cancelled: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
};
</script>

<template>
    <Head :title="client.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('clients.index')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ client.name }}
                    </h2>
                </div>
                <div class="flex gap-2">
                    <Link v-if="canEdit" :href="route('clients.edit', client.id)">
                        <SecondaryButton>Edit</SecondaryButton>
                    </Link>
                    <DangerButton v-if="canDelete" @click="showDeleteModal = true">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Billed</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ formatCurrency(stats.total_billed) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</div>
                        <div class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                            {{ formatCurrency(stats.total_paid) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Outstanding</div>
                        <div class="mt-1 text-2xl font-semibold text-orange-600 dark:text-orange-400">
                            {{ formatCurrency(stats.total_outstanding) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoices</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ stats.invoice_count }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Client Details
                        </h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ client.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <a v-if="client.email" :href="`mailto:${client.email}`" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        {{ client.email }}
                                    </a>
                                    <span v-else class="text-gray-400 dark:text-gray-500">Not provided</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <a v-if="client.phone" :href="`tel:${client.phone}`" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        {{ client.phone }}
                                    </a>
                                    <span v-else class="text-gray-400 dark:text-gray-500">Not provided</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tax ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ client.tax_id || 'Not provided' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">
                                    {{ client.address || 'Not provided' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Client Since</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ client.created_at }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Recent Invoices
                            </h3>
                        </div>
                        
                        <div v-if="recentInvoices.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No invoices yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Create an invoice for this client to see it here.
                            </p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Created
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invoice in recentInvoices" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                {{ invoice.number }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                                :class="statusColors[invoice.status]">
                                                {{ invoice.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatCurrency(invoice.total) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ invoice.due_date || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ invoice.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                    Are you sure you want to delete {{ client.name }}? This action cannot be undone.
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
