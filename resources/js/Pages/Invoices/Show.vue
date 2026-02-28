<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    invoice: Object,
    canEdit: Boolean,
    canDelete: Boolean,
    canSend: Boolean,
});

const showDeleteModal = ref(false);
const showCancelModal = ref(false);

const deleteForm = useForm({});
const statusForm = useForm({});

const confirmDelete = () => {
    deleteForm.delete(route('invoices.destroy', props.invoice.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const markAsSent = () => {
    statusForm.post(route('invoices.send', props.invoice.id));
};

const markAsPaid = () => {
    statusForm.post(route('invoices.mark-paid', props.invoice.id));
};

const markAsCancelled = () => {
    statusForm.post(route('invoices.cancel', props.invoice.id), {
        onSuccess: () => {
            showCancelModal.value = false;
        },
    });
};

const duplicate = () => {
    statusForm.post(route('invoices.duplicate', props.invoice.id));
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
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('invoices.index')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ invoice.invoice_number }}
                    </h2>
                    <span :class="['px-3 py-1 text-sm font-semibold rounded-full', getStatusClasses(invoice.status_color)]">
                        {{ invoice.status_label }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <SecondaryButton @click="duplicate" :disabled="statusForm.processing">
                        Duplicate
                    </SecondaryButton>
                    <Link v-if="canEdit && invoice.can_edit" :href="route('invoices.edit', invoice.id)">
                        <SecondaryButton>Edit</SecondaryButton>
                    </Link>
                    <PrimaryButton v-if="canSend && invoice.can_send" @click="markAsSent" :disabled="statusForm.processing">
                        Mark as Sent
                    </PrimaryButton>
                    <PrimaryButton v-if="canEdit && invoice.can_mark_paid" @click="markAsPaid" :disabled="statusForm.processing" class="bg-green-600 hover:bg-green-700">
                        Mark as Paid
                    </PrimaryButton>
                    <DangerButton v-if="canEdit && invoice.can_cancel" @click="showCancelModal = true">
                        Cancel
                    </DangerButton>
                    <DangerButton v-if="canDelete" @click="showDeleteModal = true">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-8">
                        <div class="flex justify-between mb-8">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">INVOICE</h1>
                                <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">{{ invoice.invoice_number }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Issue Date:</span> {{ invoice.issue_date }}
                                </p>
                                <p class="text-sm mt-1" :class="invoice.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-400'">
                                    <span class="font-medium">Due Date:</span> {{ invoice.due_date }}
                                    <span v-if="invoice.is_overdue" class="ml-2">(Overdue)</span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Bill To</h3>
                                <div class="text-gray-900 dark:text-gray-100">
                                    <p class="font-semibold">{{ invoice.client.name }}</p>
                                    <p v-if="invoice.client.email" class="text-sm text-gray-600 dark:text-gray-400">{{ invoice.client.email }}</p>
                                    <p v-if="invoice.client.phone" class="text-sm text-gray-600 dark:text-gray-400">{{ invoice.client.phone }}</p>
                                    <p v-if="invoice.client.address" class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line mt-1">{{ invoice.client.address }}</p>
                                    <p v-if="invoice.client.tax_id" class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tax ID: {{ invoice.client.tax_id }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div v-if="invoice.sent_at" class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Sent:</span> {{ invoice.sent_at }}
                                </div>
                                <div v-if="invoice.paid_at" class="text-sm text-green-600 dark:text-green-400 mt-1">
                                    <span class="font-medium">Paid:</span> {{ invoice.paid_at }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Description</th>
                                        <th class="text-center py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 w-24">Qty</th>
                                        <th class="text-right py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 w-28">Unit Price</th>
                                        <th class="text-center py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 w-20">Tax %</th>
                                        <th class="text-right py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 w-28">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-4 text-sm text-gray-900 dark:text-gray-100">{{ item.description }}</td>
                                        <td class="py-4 text-sm text-gray-600 dark:text-gray-400 text-center">{{ item.quantity }}</td>
                                        <td class="py-4 text-sm text-gray-600 dark:text-gray-400 text-right">${{ item.unit_price }}</td>
                                        <td class="py-4 text-sm text-gray-600 dark:text-gray-400 text-center">{{ item.tax_rate }}%</td>
                                        <td class="py-4 text-sm text-gray-900 dark:text-gray-100 text-right font-medium">${{ item.amount }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mb-8">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                    <span class="text-gray-900 dark:text-gray-100">${{ invoice.subtotal }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Tax</span>
                                    <span class="text-gray-900 dark:text-gray-100">${{ invoice.tax_amount }}</span>
                                </div>
                                <div class="flex justify-between text-xl font-bold border-t-2 border-gray-200 dark:border-gray-700 pt-3">
                                    <span class="text-gray-900 dark:text-gray-100">Total</span>
                                    <span class="text-gray-900 dark:text-gray-100">${{ invoice.total }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="invoice.notes || invoice.terms" class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div v-if="invoice.notes">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Notes</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ invoice.notes }}</p>
                            </div>
                            <div v-if="invoice.terms">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Terms & Conditions</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ invoice.terms }}</p>
                            </div>
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
                    Are you sure you want to delete {{ invoice.invoice_number }}? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton class="ml-3" @click="confirmDelete" :disabled="deleteForm.processing">
                        Delete Invoice
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Cancel Invoice
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to cancel {{ invoice.invoice_number }}? This will mark the invoice as cancelled and it won't be editable.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showCancelModal = false">
                        No, Keep It
                    </SecondaryButton>
                    <DangerButton class="ml-3" @click="markAsCancelled" :disabled="statusForm.processing">
                        Yes, Cancel Invoice
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
