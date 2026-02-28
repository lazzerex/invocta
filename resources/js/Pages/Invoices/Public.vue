<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
});

const getStatusClasses = (color) => {
    const classes = {
        gray: 'bg-gray-100 text-gray-800',
        blue: 'bg-blue-100 text-blue-800',
        green: 'bg-green-100 text-green-800',
        red: 'bg-red-100 text-red-800',
    };
    return classes[color] || classes.gray;
};
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <div class="min-h-screen bg-gray-100 py-8">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <div class="flex justify-between mb-8">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">INVOICE</h1>
                            <p class="text-lg text-gray-600 mt-1">{{ invoice.invoice_number }}</p>
                            <p class="text-sm text-gray-500 mt-1">From: {{ invoice.tenant.name }}</p>
                        </div>
                        <div class="text-right">
                            <span :class="['px-3 py-1 text-sm font-semibold rounded-full', getStatusClasses(invoice.status_color)]">
                                {{ invoice.status_label }}
                            </span>
                            <p class="text-sm text-gray-600 mt-4">
                                <span class="font-medium">Issue Date:</span> {{ invoice.issue_date }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-medium">Due Date:</span> {{ invoice.due_date }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-8 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Bill To</h3>
                        <div class="text-gray-900">
                            <p class="font-semibold">{{ invoice.client.name }}</p>
                            <p v-if="invoice.client.email" class="text-sm text-gray-600">{{ invoice.client.email }}</p>
                            <p v-if="invoice.client.phone" class="text-sm text-gray-600">{{ invoice.client.phone }}</p>
                            <p v-if="invoice.client.address" class="text-sm text-gray-600 whitespace-pre-line mt-1">{{ invoice.client.address }}</p>
                            <p v-if="invoice.client.tax_id" class="text-sm text-gray-600 mt-1">Tax ID: {{ invoice.client.tax_id }}</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-3 text-sm font-semibold text-gray-900">Description</th>
                                    <th class="text-center py-3 text-sm font-semibold text-gray-900 w-24">Qty</th>
                                    <th class="text-right py-3 text-sm font-semibold text-gray-900 w-28">Unit Price</th>
                                    <th class="text-center py-3 text-sm font-semibold text-gray-900 w-20">Tax %</th>
                                    <th class="text-right py-3 text-sm font-semibold text-gray-900 w-28">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in invoice.items" :key="index" class="border-b border-gray-100">
                                    <td class="py-4 text-sm text-gray-900">{{ item.description }}</td>
                                    <td class="py-4 text-sm text-gray-600 text-center">{{ item.quantity }}</td>
                                    <td class="py-4 text-sm text-gray-600 text-right">${{ item.unit_price }}</td>
                                    <td class="py-4 text-sm text-gray-600 text-center">{{ item.tax_rate }}%</td>
                                    <td class="py-4 text-sm text-gray-900 text-right font-medium">${{ item.amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mb-8">
                        <div class="w-64 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">${{ invoice.subtotal }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax</span>
                                <span class="text-gray-900">${{ invoice.tax_amount }}</span>
                            </div>
                            <div class="flex justify-between text-xl font-bold border-t-2 border-gray-200 pt-3">
                                <span class="text-gray-900">Total</span>
                                <span class="text-gray-900">${{ invoice.total }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="invoice.notes || invoice.terms" class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-200">
                        <div v-if="invoice.notes">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Notes</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.notes }}</p>
                        </div>
                        <div v-if="invoice.terms">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Terms & Conditions</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.terms }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-6 text-sm text-gray-500">
                Thank you for your business!
            </div>
        </div>
    </div>
</template>
