<script setup>
defineProps({
    address: {
        type: Object,
        required: true,
    },
    onSubmit: {
        type: Function,
        required: true,
    },
    isEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <form @submit.prevent="onSubmit" novalidate>
        <div class="mb-5">
            <label for="street" class="block text-gray-300 text-sm font-medium mb-2">Street</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-road text-gray-500"></i>
                </div>
                <input type="text" id="street" v-model="address.street"
                    class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter street address" autocomplete="off" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="city" class="block text-gray-300 text-sm font-medium mb-2">City</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-city text-gray-500"></i>
                    </div>
                    <input type="text" id="city" v-model="address.city"
                        class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter city" autocomplete="off" required>
                </div>
            </div>
            <div>
                <label for="province" class="block text-gray-300 text-sm font-medium mb-2">Province/State</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-map text-gray-500"></i>
                    </div>
                    <input type="text" id="province" v-model="address.province"
                        class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter province or state" autocomplete="off" required>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div>
                <label for="country" class="block text-gray-300 text-sm font-medium mb-2">Country</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-flag text-gray-500"></i>
                    </div>
                    <input type="text" id="country" v-model="address.country"
                        class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter country" autocomplete="off" required>
                </div>
            </div>
            <div>
                <label for="postal_code" class="block text-gray-300 text-sm font-medium mb-2">Postal Code</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-mail-bulk text-gray-500"></i>
                    </div>
                    <input type="text" id="postal_code" v-model="address.postal_code"
                        class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter postal code" autocomplete="off" required>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <slot name="actions" />
        </div>
    </form>
</template>
