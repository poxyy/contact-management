<script setup>

import { reactive } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { useLocalStorage } from "@vueuse/core";
import { alertError } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import { userLogin } from '../api/UserApi';
import UserForm from '../components/UserForm.vue';

const router = useRouter();
const token = useLocalStorage("token", "");
const user = reactive({
    username: "",
    password: ""
});

async function handleSubmit() {
    try {
        const response = await userLogin(user);
        const responseBody = await response.json();
        console.log(responseBody);
        if (response.status === 200) {
            token.value = responseBody.data.token;
            await router.push({ name: "contact-list" });
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Something went wrong");
    }
}

</script>

<template>
    <UserForm :form="user" :fields="[
        {
            model: 'username',
            label: 'Username',
            icon: 'fa-user',
            type: 'text'
        },
        {
            model: 'password',
            label: 'Password',
            icon: 'fa-lock',
            type: 'password'
        }
    ]" submitText="Sign In" submitIcon="fas fa-sign-in-alt" subtitle="Sign in to your account" @submit="handleSubmit">
        <template #footer>
            Don't have an account?
            <RouterLink :to="{ name: 'register' }" class="text-blue-400 hover:text-blue-300 font-medium">
                Sign up
            </RouterLink>
        </template>
    </UserForm>
</template>

<style scoped></style>
