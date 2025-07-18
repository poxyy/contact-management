<script setup>

const props = defineProps({
    form: Object,
    fields: Array, // array of { model, label, icon, type, autofocus }
    submitText: String,
    submitIcon: String,
    subtitle: {
        type: String,
        default: 'Please fill in the form',
    },
});

const emit = defineEmits(['submit']);

function handleSubmit() {
    emit('submit');
}

</script>


<template>
    <div
        class="animate-fade-in bg-gray-800 bg-opacity-80 p-8 rounded-xl shadow-custom border border-gray-700 backdrop-blur-sm w-full max-w-md">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-block p-3 bg-gradient rounded-full mb-4">
                <i class="fas fa-address-book text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">Contact Management</h1>
            <p class="text-gray-300 mt-2">{{ subtitle }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" novalidate>
            <div v-for="field in fields" :key="field.model" class="mb-5">
                <label :for="field.model" class="block text-gray-300 text-sm font-medium mb-2">
                    {{ field.label }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i :class="`fas ${field.icon} text-gray-500`"></i>
                    </div>
                    <input :type="field.type" :id="field.model" :name="field.model" v-model="form[field.model]"
                        class="w-full pl-10 pr-3 py-3 bg-gray-700 bg-opacity-50 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                        :placeholder="`Enter ${field.label.toLowerCase()}`" autocomplete="off" required />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-6">
                <button type="submit"
                    class="w-full bg-gradient text-white py-3 px-4 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-lg transform hover:-translate-y-0.5 flex items-center justify-center">
                    <i :class="`${submitIcon} mr-2`"></i> {{ submitText }}
                </button>
            </div>

            <!-- Footer slot (e.g., "Already have an account?") -->
            <div class="text-center text-sm text-gray-400">
                <slot name="footer" />
            </div>
        </form>
    </div>
</template>
