<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Invalid Invitation" />

        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Invalid Invitation
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                This invitation link is invalid or has expired.
            </p>
            <div class="mt-6">
                <Link :href="route('login')">
                    <PrimaryButton>
                        Go to Login
                    </PrimaryButton>
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
