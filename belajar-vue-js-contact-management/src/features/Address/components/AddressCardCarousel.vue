<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    addresses: {
        type: Array,
        required: true,
    },
    contactId: [String, Number],
});

defineEmits(['delete-address']);

const currentIndex = ref(0);

const current = computed(() => {
    return props.addresses[currentIndex.value] || {};
});

function next() {
    if (currentIndex.value < props.addresses.length - 1) {
        currentIndex.value++;
    }
}

function prev() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
}
</script>

<template>
    <div class="relative">
        <!-- Address Card -->
        <div class="transition-all duration-300">
            <div class="bg-gray-700 bg-opacity-50 p-5 rounded-lg shadow-md border border-gray-600"
                v-if="addresses.length">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white text-lg font-semibold">Address {{ currentIndex + 1 }} of {{
                        props.addresses.length
                        }}</h3>
                    <div class="space-x-2">
                        <button @click="prev" class="text-white hover:text-blue-400"><i
                                class="fas fa-chevron-left"></i></button>
                        <button @click="next" class="text-white hover:text-blue-400"><i
                                class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <div class="text-gray-300 space-y-2">
                    <p><strong>Street:</strong> {{ current.street }}</p>
                    <p><strong>City:</strong> {{ current.city }}</p>
                    <p><strong>Province:</strong> {{ current.province }}</p>
                    <p><strong>Country:</strong> {{ current.country }}</p>
                    <p><strong>Postal:</strong> {{ current.postal_code }}</p>
                </div>

                <div class="flex justify-end mt-4 space-x-3">
                    <RouterLink :to="{ name: 'address-edit', params: { id: contactId, addressId: current.id } }"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </RouterLink>
                    <button @click="$emit('delete-address', current.id)"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        <i class="fas fa-trash-alt mr-2"></i> Delete
                    </button>
                </div>
            </div>

            <p v-else class="text-gray-400 text-center">No addresses available.</p>
        </div>
    </div>
</template>
