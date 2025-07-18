<script setup>

import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
    isEdit: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'submit']);

// Biarkan parent yang pegang state dengan computed getter/setter
const form = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

function onSubmit() {
    emit('submit');
}

</script>


<template>
    <form @submit.prevent="onSubmit" novalidate>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="first_name" class="block text-gray-300 text-sm font-medium mb-2">First Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-tag text-gray-500"></i>
                    </div>
                    <input type="text" id="first_name" class="w-full pl-10 pr-3 py-3 bg-gray-700 text-white rounded-lg"
                        placeholder="Enter first name" v-model="form.first_name" autocomplete="off" required />
                </div>
            </div>

            <div>
                <label for="last_name" class="block text-gray-300 text-sm font-medium mb-2">Last Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-tag text-gray-500"></i>
                    </div>
                    <input type="text" id="last_name" class="w-full pl-10 pr-3 py-3 bg-gray-700 text-white rounded-lg"
                        placeholder="Enter last name" v-model="form.last_name" autocomplete="off" required />
                </div>
            </div>
        </div>

        <div class="mb-5">
            <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-500"></i>
                </div>
                <input type="email" id="email" class="w-full pl-10 pr-3 py-3 bg-gray-700 text-white rounded-lg"
                    placeholder="Enter email address" v-model="form.email" autocomplete="off" required />
            </div>
        </div>

        <div class="mb-6">
            <label for="phone" class="block text-gray-300 text-sm font-medium mb-2">Phone</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-phone text-gray-500"></i>
                </div>
                <input type="tel" id="phone" class="w-full pl-10 pr-3 py-3 bg-gray-700 text-white rounded-lg"
                    placeholder="Enter phone number" v-model="form.phone" autocomplete="off" required />
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <slot name="actions" />
        </div>
    </form>
</template>
