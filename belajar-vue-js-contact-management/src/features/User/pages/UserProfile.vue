<script setup>
import { ref, onBeforeMount } from 'vue';
import { userDetail } from '../api/UserApi.js';
import { useLocalStorage } from '@vueuse/core';
import { alertError } from '../../../shared/lib/alert.js';
import { parseErrors } from '../../../shared/lib/utils/error.js';

import UserNameForm from '../components/UserNameForm.vue';
import UserPasswordForm from '../components/UserPasswordForm.vue';

const token = useLocalStorage("token", "");
const name = ref("");

async function fetchUser() {
    try {
        const response = await userDetail(token.value);
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            name.value = responseBody.data.name;
        } else {
            await alertError(responseBody.errors);
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to fetch user");
    }
}

onBeforeMount(fetchUser);
</script>

<template>
    <div class="flex items-center mb-6">
        <i class="fas fa-user-cog text-blue-400 text-2xl mr-3"></i>
        <h1 class="text-2xl font-bold text-white">My Profile</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Form 1: Edit Name -->
        <div
            class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 overflow-hidden card-hover animate-fade-in p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-user-edit text-white"></i>
                </div>
                <h2 class="text-xl font-semibold text-white">Edit Profile</h2>
            </div>
            <UserNameForm v-model:name="name" />
        </div>

        <!-- Form 2: Edit Password -->
        <div
            class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 overflow-hidden card-hover animate-fade-in p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-key text-white"></i>
                </div>
                <h2 class="text-xl font-semibold text-white">Change Password</h2>
            </div>
            <UserPasswordForm />
        </div>
    </div>
</template>

<style scoped></style>
