<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
});
</script>

<template>
    <Head :title="`Payment Successful - Invoice ${invoice.invoice_number}`" />

    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-6">
                Your payment for invoice <span class="font-semibold">{{ invoice.invoice_number }}</span> has been processed successfully.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Invoice Number</span>
                    <span class="font-medium text-gray-900">{{ invoice.invoice_number }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Company</span>
                    <span class="font-medium text-gray-900">{{ invoice.tenant.name }}</span>
                </div>
                <div class="flex justify-between text-sm border-t border-gray-200 pt-2 mt-2">
                    <span class="text-gray-600">Amount Paid</span>
                    <span class="font-bold text-green-600">${{ invoice.total }}</span>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-6">
                A receipt has been sent to your email address. Thank you for your payment!
            </p>

            <Link
                :href="route('public.invoice', invoice.uuid)"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            >
                View Invoice
            </Link>
        </div>
    </div>
</template>
