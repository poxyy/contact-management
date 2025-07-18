<script setup>
defineProps({
    contact: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div>
        <!-- Contact Header -->
        <div class="mb-8 text-center">
            <div class="w-20 h-20 bg-gradient rounded-full mx-auto flex items-center justify-center mb-4 shadow-lg">
                <i class="fas fa-user text-3xl text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">
                {{ contact.first_name }} {{ contact.last_name }}
            </h2>
            <div class="w-24 h-1 bg-gradient mx-auto rounded-full"></div>
        </div>

        <!-- Contact Information -->
        <div class="space-y-5 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div
                    class="bg-gray-700 bg-opacity-50 p-5 rounded-lg shadow-md border border-gray-600 transition-all duration-200 hover:bg-opacity-70">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user-tag text-blue-400 mr-2"></i>
                        <h3 class="text-gray-300 text-sm font-medium">First Name</h3>
                    </div>
                    <p class="text-white text-lg ml-6">{{ contact.first_name }}</p>
                </div>

                <div
                    class="bg-gray-700 bg-opacity-50 p-5 rounded-lg shadow-md border border-gray-600 transition-all duration-200 hover:bg-opacity-70">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user-tag text-blue-400 mr-2"></i>
                        <h3 class="text-gray-300 text-sm font-medium">Last Name</h3>
                    </div>
                    <p class="text-white text-lg ml-6">{{ contact.last_name }}</p>
                </div>
            </div>

            <div
                class="bg-gray-700 bg-opacity-50 p-5 rounded-lg shadow-md border border-gray-600 transition-all duration-200 hover:bg-opacity-70">
                <div class="flex items-center mb-2">
                    <i class="fas fa-envelope text-blue-400 mr-2"></i>
                    <h3 class="text-gray-300 text-sm font-medium">Email</h3>
                </div>
                <p class="text-white text-lg ml-6">{{ contact.email }}</p>
            </div>

            <div
                class="bg-gray-700 bg-opacity-50 p-5 rounded-lg shadow-md border border-gray-600 transition-all duration-200 hover:bg-opacity-70">
                <div class="flex items-center mb-2">
                    <i class="fas fa-phone text-blue-400 mr-2"></i>
                    <h3 class="text-gray-300 text-sm font-medium">Phone</h3>
                </div>
                <p class="text-white text-lg ml-6">{{ contact.phone }}</p>
            </div>
        </div>
    </div>
</template>
