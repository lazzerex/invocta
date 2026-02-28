<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoice: Object,
    clients: Array,
});

const form = useForm({
    client_id: props.invoice.client_id,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    notes: props.invoice.notes || '',
    terms: props.invoice.terms || '',
    items: props.invoice.items.map(item => ({
        id: item.id,
        description: item.description,
        quantity: parseFloat(item.quantity),
        unit_price: parseFloat(item.unit_price),
        tax_rate: parseFloat(item.tax_rate),
    })),
});

const addItem = () => {
    form.items.push({ id: null, description: '', quantity: 1, unit_price: 0, tax_rate: 0 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const getItemAmount = (item) => {
    return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
};

const getItemTaxAmount = (item) => {
    return getItemAmount(item) * ((parseFloat(item.tax_rate) || 0) / 100);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + getItemAmount(item), 0);
});

const taxAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + getItemTaxAmount(item), 0);
});

const total = computed(() => {
    return subtotal.value + taxAmount.value;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const submit = () => {
    form.put(route('invoices.update', props.invoice.id));
};
</script>

<template>
    <Head :title="`Edit Invoice ${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit Invoice
                </h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ invoice.invoice_number }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <InputLabel for="client_id" value="Client" />
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="">Select a client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }} {{ client.email ? `(${client.email})` : '' }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.client_id" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="issue_date" value="Issue Date" />
                                        <TextInput
                                            id="issue_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.issue_date"
                                        />
                                        <InputError class="mt-2" :message="form.errors.issue_date" />
                                    </div>

                                    <div>
                                        <InputLabel for="due_date" value="Due Date" />
                                        <TextInput
                                            id="due_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.due_date"
                                        />
                                        <InputError class="mt-2" :message="form.errors.due_date" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Line Items</h3>

                            <InputError v-if="form.errors.items" class="mb-4" :message="form.errors.items" />

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="text-left py-2 text-sm font-medium text-gray-700 dark:text-gray-300 w-2/5">Description</th>
                                            <th class="text-center py-2 text-sm font-medium text-gray-700 dark:text-gray-300 w-1/6">Qty</th>
                                            <th class="text-center py-2 text-sm font-medium text-gray-700 dark:text-gray-300 w-1/6">Unit Price</th>
                                            <th class="text-center py-2 text-sm font-medium text-gray-700 dark:text-gray-300 w-1/6">Tax %</th>
                                            <th class="text-right py-2 text-sm font-medium text-gray-700 dark:text-gray-300 w-1/6">Amount</th>
                                            <th class="w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index" class="border-b border-gray-100 dark:border-gray-700">
                                            <td class="py-2 pr-2">
                                                <TextInput
                                                    v-model="item.description"
                                                    type="text"
                                                    class="w-full"
                                                    placeholder="Item description"
                                                />
                                                <InputError class="mt-1" :message="form.errors[`items.${index}.description`]" />
                                            </td>
                                            <td class="py-2 px-2">
                                                <TextInput
                                                    v-model="item.quantity"
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    class="w-full text-center"
                                                />
                                                <InputError class="mt-1" :message="form.errors[`items.${index}.quantity`]" />
                                            </td>
                                            <td class="py-2 px-2">
                                                <TextInput
                                                    v-model="item.unit_price"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="w-full text-center"
                                                />
                                                <InputError class="mt-1" :message="form.errors[`items.${index}.unit_price`]" />
                                            </td>
                                            <td class="py-2 px-2">
                                                <TextInput
                                                    v-model="item.tax_rate"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    class="w-full text-center"
                                                />
                                            </td>
                                            <td class="py-2 pl-2 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ formatCurrency(getItemAmount(item)) }}
                                            </td>
                                            <td class="py-2 pl-2">
                                                <button
                                                    type="button"
                                                    @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                                                    :disabled="form.items.length === 1"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <SecondaryButton type="button" @click="addItem">
                                    Add Line Item
                                </SecondaryButton>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Tax</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(taxAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold border-t border-gray-200 dark:border-gray-700 pt-2">
                                        <span class="text-gray-900 dark:text-gray-100">Total</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formatCurrency(total) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <InputLabel for="notes" value="Notes" />
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        placeholder="Additional notes for the client..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.notes" />
                                </div>

                                <div>
                                    <InputLabel for="terms" value="Terms & Conditions" />
                                    <textarea
                                        id="terms"
                                        v-model="form.terms"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                        placeholder="Payment terms and conditions..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.terms" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <Link :href="route('invoices.show', invoice.id)">
                            <SecondaryButton type="button">Cancel</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            Update Invoice
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
