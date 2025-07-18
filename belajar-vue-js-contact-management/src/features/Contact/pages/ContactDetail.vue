<script setup>

import { useLocalStorage } from '@vueuse/core';
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { contactDetail } from '../api/ContactApi';
import { alertConfirm, alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import { addressDelete, addressList } from '../../Address/api/AddressApi';
import AddressCardCarousel from '../../Address/components/AddressCardCarousel.vue';
import ContactInfo from '../components/ContactInfo.vue';

const route = useRoute();
const { id } = route.params;
const token = useLocalStorage("token", "");
const contact = reactive({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
})
const addresses = ref([]);

async function handleDeleteAddress(addressId) {
    if (!await alertConfirm("Are you sure you want to delete this address?")) {
        return;
    }

    try {
        const response = await addressDelete(token.value, id, addressId);
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            await alertSuccess("Address deleted successfully");
            await fetchAddresses();
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to delete addresses");
    }
}

async function fetchAddresses() {
    try {
        const response = await addressList(token.value, id);
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            addresses.value = responseBody.data;
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to fetch addresses");
    }
}

async function fetchContact() {
    try {
        const response = await contactDetail(token.value, id);
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            contact.first_name = responseBody.data.first_name;
            contact.last_name = responseBody.data.last_name;
            contact.email = responseBody.data.email;
            contact.phone = responseBody.data.phone;
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to load specific contact");
    }
}

onMounted(async () => {
    await fetchContact();
    await fetchAddresses();
})

</script>

<template>
    <div class="flex items-center mb-6">
        <RouterLink :to="{ name: 'contact-list' }"
            class="text-blue-400 hover:text-blue-300 mr-4 flex items-center transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Back to Contacts
        </RouterLink>
        <h1 class="text-2xl font-bold text-white flex items-center">
            <i class="fas fa-id-card text-blue-400 mr-3"></i> Contact Details
        </h1>
    </div>

    <div
        class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 overflow-hidden max-w-2xl mx-auto animate-fade-in">
        <div class="p-8">
            <!-- Contact Information -->
            <ContactInfo :contact="contact" />

            <!-- Addresses Section -->
            <div class="mb-8">
                <div class="flex items-center mb-5">
                    <i class="fas fa-map-marker-alt text-blue-400 mr-3"></i>
                    <h3 class="text-xl font-semibold text-white">Addresses</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Add Address Card -->
                    <div
                        class="bg-gray-700 bg-opacity-50 p-5 rounded-lg border-2 border-dashed border-gray-600 shadow-md card-hover">
                        <RouterLink :to="{ name: 'address-create' }" class="block h-full">
                            <div class="flex flex-col items-center justify-center h-full text-center py-4">
                                <div
                                    class="w-16 h-16 bg-gradient rounded-full flex items-center justify-center mb-4 shadow-lg transform transition-transform duration-300 hover:scale-110">
                                    <i class="fas fa-plus text-2xl text-white"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-white">Add Address</h4>
                            </div>
                        </RouterLink>
                    </div>

                    <!-- Address Card -->
                    <AddressCardCarousel :addresses="addresses" :contact-id="id"
                        @delete-address="handleDeleteAddress" />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <RouterLink :to="{ name: 'contact-list' }"
                    class="px-5 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 flex items-center shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </RouterLink>
                <RouterLink :to="{ name: 'contact-edit' }"
                    class="px-5 py-3 bg-gradient text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center">
                    <i class="fas fa-user-edit mr-2"></i> Edit Contact
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped></style>