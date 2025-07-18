<script setup>

import { reactive } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { userRegister } from '../api/UserApi';
import { alertError, alertSuccess } from '../../../shared/lib/alert';
import { parseErrors } from '../../../shared/lib/utils/error';
import UserForm from '../components/UserForm.vue';

const router = useRouter();
const user = reactive({
    username: "",
    name: "",
    password: "",
    confirm_password: ""
});

async function handleSubmit() {
    if (user.password !== user.confirm_password) {
        await alertError("Password do not match");
        return;
    }

    try {
        const response = await userRegister(user);
        const responseBody = await response.json();
        if (response.status === 201) {
            await alertSuccess("User created successfully");
            await router.push({ name: "login" });
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
            model: 'name',
            label: 'Full Name',
            icon: 'fa-id-card',
            type: 'text'
        },
        {
            model: 'password',
            label: 'Password',
            icon: 'fa-lock',
            type: 'password'
        },
        {
            model: 'confirm_password',
            label: 'Confirm Password',
            icon: 'fa-check-double',
            type: 'password'
        }
    ]" submitText="Register" submitIcon="fas fa-user-plus" subtitle="Create a new account" @submit="handleSubmit">
        <template #footer>
            Already have an account?
            <RouterLink :to="{ name: 'login' }" class="text-blue-400 hover:text-blue-300 font-medium">
                Sign in
            </RouterLink>
        </template>
    </UserForm>
</template>

<style scoped></style>
