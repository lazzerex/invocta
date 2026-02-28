<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('invitations.accept', props.invitation.token), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Accept Invitation" />

        <div class="mb-4 text-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Join {{ invitation.tenant_name }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                You've been invited to join as a <span class="font-medium capitalize">{{ invitation.role }}</span>.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                    :model-value="invitation.email"
                    disabled
                />
            </div>

            <div class="mt-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                This invitation expires on {{ invitation.expires_at }}.
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton class="w-full justify-center" :disabled="form.processing">
                    Accept Invitation
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
