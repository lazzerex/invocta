<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    members: Array,
    invitations: Array,
    roles: Array,
    canManageTeam: Boolean,
});

const showInviteModal = ref(false);
const showRoleModal = ref(false);
const showDeleteModal = ref(false);
const selectedMember = ref(null);

const inviteForm = useForm({
    email: '',
    role: 'staff',
});

const roleForm = useForm({
    role: '',
});

const submitInvite = () => {
    inviteForm.post(route('invitations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const openRoleModal = (member) => {
    selectedMember.value = member;
    roleForm.role = member.role;
    showRoleModal.value = true;
};

const submitRoleChange = () => {
    roleForm.patch(route('team.update-role', selectedMember.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRoleModal.value = false;
            selectedMember.value = null;
        },
    });
};

const openDeleteModal = (member) => {
    selectedMember.value = member;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    useForm({}).delete(route('team.destroy', selectedMember.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedMember.value = null;
        },
    });
};

const cancelInvitation = (invitation) => {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        useForm({}).delete(route('invitations.destroy', invitation.id), {
            preserveScroll: true,
        });
    }
};

const resendInvitation = (invitation) => {
    useForm({}).post(route('invitations.resend', invitation.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Team" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Team Members
                </h2>
                <PrimaryButton v-if="canManageTeam" @click="showInviteModal = true">
                    Invite Member
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Members
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Joined
                                        </th>
                                        <th v-if="canManageTeam" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="member in members" :key="member.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ member.name }}
                                                    <span v-if="member.is_current_user" class="ml-2 text-xs text-gray-500">(you)</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ member.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                                :class="{
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': member.role === 'admin',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': member.role === 'manager',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': member.role === 'staff',
                                                }">
                                                {{ member.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ member.created_at }}
                                        </td>
                                        <td v-if="canManageTeam" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <template v-if="!member.is_current_user">
                                                <button @click="openRoleModal(member)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">
                                                    Change Role
                                                </button>
                                                <button @click="openDeleteModal(member)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Remove
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-if="canManageTeam && invitations.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Pending Invitations
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Invited By
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Expires
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invitation in invitations" :key="invitation.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ invitation.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                {{ invitation.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ invitation.invited_by }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ invitation.expires_at }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="resendInvitation(invitation)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4">
                                                Resend
                                            </button>
                                            <button @click="cancelInvitation(invitation)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Cancel
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showInviteModal" @close="showInviteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Invite Team Member
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Send an invitation email to add a new team member.
                </p>
                <form @submit.prevent="submitInvite" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="inviteForm.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="inviteForm.errors.email" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="role" value="Role" />
                        <select
                            id="role"
                            v-model="inviteForm.role"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option v-for="role in roles" :key="role" :value="role" class="capitalize">
                                {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                            </option>
                        </select>
                        <InputError :message="inviteForm.errors.role" class="mt-2" />
                    </div>
                    <div class="flex justify-end">
                        <SecondaryButton @click="showInviteModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton class="ml-3" :disabled="inviteForm.processing">
                            Send Invitation
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showRoleModal" @close="showRoleModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Change Role
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update the role for {{ selectedMember?.name }}.
                </p>
                <form @submit.prevent="submitRoleChange" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="change-role" value="Role" />
                        <select
                            id="change-role"
                            v-model="roleForm.role"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <SecondaryButton @click="showRoleModal = false">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton class="ml-3" :disabled="roleForm.processing">
                            Update Role
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Remove Team Member
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to remove {{ selectedMember?.name }} from the team? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false">
                        Cancel
                    </SecondaryButton>
                    <DangerButton class="ml-3" @click="confirmDelete">
                        Remove Member
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
