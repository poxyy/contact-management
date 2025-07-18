<script setup>
import { useLocalStorage } from '@vueuse/core';
import { onMounted, reactive } from 'vue';
import { useRoute } from 'vue-router';
import { contactDetail } from '../../Contact/api/ContactApi';
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import { addressDetail, addressUpdate } from '../api/AddressApi';
import AddressForm from '../components/AddressForm.vue';

const token = useLocalStorage("token", "");
const route = useRoute();
const { id, addressId } = route.params;

const contact = reactive({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
});

const address = reactive({
    street: "",
    city: "",
    province: "",
    country: "",
    postal_code: "",
});

async function fetchContact() {
    try {
        const res = await contactDetail(token.value, id);
        const body = await res.json();
        if (res.status === 200) {
            Object.assign(contact, body.data);
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to fetch contact");
    }
}

async function fetchAddress() {
    try {
        const res = await addressDetail(token.value, id, addressId);
        const body = await res.json();
        if (res.status === 200) {
            Object.assign(address, body.data);
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to fetch address");
    }
}

async function handleSubmit() {
    try {
        const res = await addressUpdate(token.value, id, addressId, address);
        const body = await res.json();
        if (res.status === 200) {
            await alertSuccess("Address updated successfully");
        } else {
            await alertError(parseErrors(body.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to update address");
    }
}

onMounted(async () => {
    await fetchContact();
    await fetchAddress();
});
</script>

<template>
    <div class="flex items-center mb-6">
        <RouterLink :to="{ name: 'contact-detail' }" class="text-blue-400 hover:text-blue-300 mr-4 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Contact Details
        </RouterLink>
        <h1 class="text-2xl font-bold text-white flex items-center">
            <i class="fas fa-map-marker-alt text-blue-400 mr-3"></i> Edit Address
        </h1>
    </div>

    <div
        class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 max-w-2xl mx-auto animate-fade-in">
        <div class="p-8">
            <AddressForm :address="address" :on-submit="handleSubmit">
                <template #actions>
                    <RouterLink :to="{ name: 'contact-detail' }"
                        class="px-5 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 flex items-center shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </RouterLink>
                    <button type="submit"
                        class="px-5 py-3 bg-gradient text-white rounded-lg hover:opacity-90 font-medium shadow-lg flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </template>
            </AddressForm>
        </div>
    </div>
</template>
