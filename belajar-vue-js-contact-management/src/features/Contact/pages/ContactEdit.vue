<script setup>
import { onMounted, reactive, ref, toRaw } from 'vue';
import { useRoute } from 'vue-router';
import { useLocalStorage } from '@vueuse/core';
import { contactDetail, contactUpdate } from '../api/ContactApi';
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import ContactForm from '../components/ContactForm.vue';

const route = useRoute();
const { id } = route.params;

const token = useLocalStorage('token', '');

const contact = reactive({
    first_name: '',
    last_name: '',
    email: '',
    phone: ''
});

const originalContact = ref(null);

function isContactChanged() {
    const current = toRaw(contact);
    const original = originalContact.value;
    if (!original) return true;

    return (
        current.first_name !== original.first_name ||
        current.last_name !== original.last_name ||
        current.email !== original.email ||
        current.phone !== original.phone
    );
}

async function fetchContact() {
    try {
        const response = await contactDetail(token.value, id);
        const body = await response.json();

        if (response.status === 200) {
            Object.assign(contact, body.data);
            originalContact.value = structuredClone(body.data);
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || 'Failed to load contact');
    }
}

async function handleSubmit() {
    if (!isContactChanged()) {
        await alertError('No changes to save');
        return;
    }

    try {
        const response = await contactUpdate(token.value, id, contact);
        const body = await response.json();

        if (response.status === 200) {
            await alertSuccess('Contact updated successfully');
            originalContact.value = structuredClone(toRaw(contact));
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || 'Failed to update contact');
    }
}

onMounted(fetchContact);
</script>

<template>
    <div class="flex items-center mb-6">
        <RouterLink :to="{ name: 'contact-list' }"
            class="text-blue-400 hover:text-blue-300 mr-4 flex items-center transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Back to Contacts
        </RouterLink>
        <h1 class="text-2xl font-bold text-white flex items-center">
            <i class="fas fa-user-edit text-blue-400 mr-3"></i> Edit Contact
        </h1>
    </div>

    <div
        class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 overflow-hidden max-w-2xl mx-auto animate-fade-in">
        <div class="p-8">
            <ContactForm v-model="contact" :isEdit="true" @submit="handleSubmit">
                <template #actions>
                    <RouterLink :to="{ name: 'contact-list' }"
                        class="px-5 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 flex items-center shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </RouterLink>
                    <button type="submit"
                        class="px-5 py-3 bg-gradient text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </template>
            </ContactForm>
        </div>
    </div>
</template>
