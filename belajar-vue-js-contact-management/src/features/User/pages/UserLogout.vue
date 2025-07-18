<script setup>

import { useLocalStorage } from "@vueuse/core";
import { onBeforeMount } from "vue";
import { userLogout } from "../api/UserApi";
import { alertError } from "../../../shared/lib/alert";
import { parseErrors } from "../../../shared/lib/utils/error";
import { useRouter } from "vue-router";

const token = useLocalStorage("token", "");
const router = useRouter();

async function handleLogout() {
    try {
        const response = await userLogout(token.value);
        const responseBody = await response.json();
        console.log(responseBody);

        if (response.status === 200) {
            token.value = "";
            await router.push({
                name: "login"
            });
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to logout");
    }
}

onBeforeMount(async () => {
    await handleLogout();
})

</script>

<template>
    <div class="text-center text-white mt-20 animate-fade-in">
        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
        <p>Logging out...</p>
    </div>
</template>


<style scoped></style>