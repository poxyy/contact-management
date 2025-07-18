<script setup>
import { ref } from 'vue';
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import { userUpdatePassword } from '../api/UserApi';
import { useLocalStorage } from '@vueuse/core';

const token = useLocalStorage("token", "");
const password = ref("");
const confirm_password = ref("");

async function handleChangePassword() {
    if (password.value !== confirm_password.value) {
        await alertError("Password do not match");
        return;
    }

    try {
        const response = await userUpdatePassword(token.value, { password: password.value });
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            password.value = "";
            confirm_password.value = "";
            await alertSuccess("Password updated successfully");
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to update password");
    }
}
</script>

<template>
    <form @submit.prevent="handleChangePassword">
        <div class="mb-5">
            <label for="new_password" class="block text-gray-300 text-sm font-medium mb-2">New Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-500"></i>
                </div>
                <input type="password" id="new_password" name="new_password" v-model="password"
                    class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    placeholder="Enter your new password" required>
            </div>
        </div>

        <div class="mb-5">
            <label for="confirm_password" class="block text-gray-300 text-sm font-medium mb-2">Confirm New
                Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-check-double text-gray-500"></i>
                </div>
                <input type="password" id="confirm_password" name="confirm_password" v-model="confirm_password"
                    class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    placeholder="Confirm your new password" required>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full bg-gradient text-white py-3 px-4 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center justify-center">
                <i class="fas fa-key mr-2"></i> Update Password
            </button>
        </div>
    </form>
</template>
