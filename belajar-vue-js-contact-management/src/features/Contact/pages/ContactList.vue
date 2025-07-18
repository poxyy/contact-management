<script setup>
import { onBeforeMount, reactive, ref } from 'vue';
import { useLocalStorage } from "@vueuse/core";
import { contactDelete, contactList } from '../api/ContactApi';
import { alertConfirm, alertError, alertSuccess } from "../../../shared/lib/alert";
import { parseErrors } from "../../../shared/lib/utils/error";
import SearchForm from '../components/SearchForm.vue';
import ContactCard from '../components/ContactCard.vue';
import Pagination from '../components/Pagination.vue';

const token = useLocalStorage("token", "");
const search = reactive({
    name: "",
    email: "",
    phone: "",
});
const page = ref(1);
const totalPage = ref(1);
const contacts = ref([]);

async function handleChangePage(p) {
    if (p !== page.value) {
        page.value = p;
        await fetchContact();
    }
}

async function handleSearch() {
    page.value = 1;
    await fetchContact();
}

async function handleClear() {
    search.name = "";
    search.email = "";
    search.phone = "";
    page.value = 1;
    await fetchContact();
}

async function handleDelete(id) {
    if (! await alertConfirm("Are you sure want to delete this contact?")) return;

    try {
        const response = await contactDelete(token.value, id);
        const responseBody = await response.json();

        if (response.status === 200) {
            await alertSuccess("Contact deleted successfully");
            await fetchContact();
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to delete contact");
    }
}

async function fetchContact() {
    try {
        const response = await contactList(token.value, {
            name: search.name,
            email: search.email,
            phone: search.phone,
            page: page.value,
        });
        const responseBody = await response.json();

        if (response.status === 200) {
            contacts.value = responseBody.data;
            totalPage.value = Math.ceil(responseBody.meta.total / responseBody.meta.size);
        } else {
            await alertError(parseErrors(responseBody.errors));
        }
    } catch (error) {
        await alertError(parseErrors(error) || "Failed to load contacts");
    }
}

onBeforeMount(fetchContact);
</script>


<template>
    <div class="flex items-center mb-6">
        <i class="fas fa-users text-blue-400 text-2xl mr-3"></i>
        <h1 class="text-2xl font-bold text-white">My Contacts</h1>
    </div>

    <!-- Search form -->
    <SearchForm v-model="search" @search="handleSearch" @clear="handleClear" />

    <!-- Contact cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Create New Contact Card -->
        <div
            class="bg-gray-800 bg-opacity-80 rounded-xl shadow-custom overflow-hidden border-2 border-dashed border-gray-700 card-hover animate-fade-in">
            <RouterLink :to="{ name: 'contact-create' }" class="block p-6 h-full">
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <div
                        class="w-20 h-20 bg-gradient rounded-full flex items-center justify-center mb-5 shadow-lg transform transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-user-plus text-3xl text-white"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-white mb-3">Create New Contact</h2>
                    <p class="text-gray-300">Add a new contact to your list</p>
                </div>
            </RouterLink>
        </div>

        <!-- Contact Card -->
        <ContactCard v-for="contact in contacts" :key="contact.id" :contact="contact" @delete="handleDelete" />
    </div>

    <!-- Pagination -->
    <Pagination :currentPage="page" :totalPage="totalPage" @change="handleChangePage" />

</template>

<style scoped></style>