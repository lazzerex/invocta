<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    invoice: Object,
    stripeEnabled: Boolean,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({});
const processing = ref(false);

const markAsPaid = () => {
    processing.value = true;
    form.post(route('public.invoice.mark-paid', props.invoice.uuid), {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const payWithStripe = () => {
    window.location.href = route('public.invoice.pay', props.invoice.uuid);
};

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
            <div v-if="flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ flash.error }}
            </div>

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
                            <p v-if="invoice.paid_at" class="text-sm text-green-600 mt-1 font-medium">
                                Paid on: {{ invoice.paid_at }}
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

                    <div v-if="invoice.can_pay" class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col items-center">
                            <p class="text-gray-600 mb-4">Ready to complete payment?</p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button
                                    v-if="stripeEnabled"
                                    @click="payWithStripe"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                >
                                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                                    </svg>
                                    Pay with Stripe
                                </button>
                                <button
                                    @click="markAsPaid"
                                    :disabled="processing"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ processing ? 'Processing...' : 'Mark as Paid' }}
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">{{ stripeEnabled ? 'Pay securely via Stripe or mark as paid manually' : 'Click to confirm payment has been made' }}</p>
                        </div>
                    </div>

                    <div v-if="invoice.status === 'paid'" class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Payment Complete</h3>
                            <p class="text-gray-600 mt-1">Thank you for your payment!</p>
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
