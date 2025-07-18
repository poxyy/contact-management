<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useLocalStorage } from '@vueuse/core';
import { contactCreate } from '../api/ContactApi';
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import ContactForm from '../components/ContactForm.vue';

const router = useRouter();
const token = useLocalStorage('token', '');
const contact = reactive({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
});

async function handleSubmit() {
    try {
        const response = await contactCreate(token.value, contact);
        const body = await response.json();

        if (response.status === 201) {
            await alertSuccess('Contact created successfully');
            await router.push({ name: 'contact-list' });
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || 'Something went wrong');
    }
}
</script>

<template>
    <div class="flex items-center mb-6">
        <RouterLink :to="{ name: 'contact-list' }"
            class="text-blue-400 hover:text-blue-300 mr-4 flex items-center transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Back to Contacts
        </RouterLink>
        <h1 class="text-2xl font-bold text-white flex items-center">
            <i class="fas fa-user-plus text-blue-400 mr-3"></i> Create New Contact
        </h1>
    </div>

    <div
        class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 overflow-hidden max-w-2xl mx-auto animate-fade-in">
        <div class="p-8">
            <ContactForm v-model="contact" :isEdit="false" @submit="handleSubmit">
                <template #actions>
                    <RouterLink :to="{ name: 'contact-list' }"
                        class="px-5 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 flex items-center shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </RouterLink>
                    <button type="submit"
                        class="px-5 py-3 bg-gradient text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Create Contact
                    </button>
                </template>
            </ContactForm>
        </div>
    </div>
</template>
