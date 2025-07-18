<script setup>
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import { userUpdateName } from '../api/UserApi';
import { useLocalStorage } from '@vueuse/core';

const token = useLocalStorage("token", "");
const name = defineModel('name');

async function handleChangeName() {
    try {
        const response = await userUpdateName(token.value, { name: name.value });
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            await alertSuccess("Name updated successfully");
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to update name");
    }
}
</script>

<template>
    <form @submit.prevent="handleChangeName">
        <div class="mb-5">
            <label for="name" class="block text-gray-300 text-sm font-medium mb-2">Full Name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-500"></i>
                </div>
                <input type="text" id="name" name="name" v-model="name"
                    class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    placeholder="Enter your full name" autocomplete="off" required>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full bg-gradient text-white py-3 px-4 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center justify-center">
                <i class="fas fa-save mr-2"></i> Update Profile
            </button>
        </div>
    </form>
</template>
