<script setup>

defineProps({
    currentPage: Number,
    totalPage: Number,
});

const emit = defineEmits(['change']);

function changePage(p) {
    emit('change', p);
}

</script>

<template>
    <div class="mt-10 flex justify-center">
        <nav
            class="flex items-center space-x-3 bg-gray-800 bg-opacity-80 rounded-xl shadow-custom border border-gray-700 p-3 animate-fade-in">
            <!-- Prev -->
            <a href="#" @click.prevent="() => changePage(currentPage - 1)" v-if="currentPage > 1"
                class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-all duration-200 flex items-center">
                <i class="fas fa-chevron-left mr-2"></i> Previous
            </a>

            <!-- Numbered Pages -->
            <a v-for="p in totalPage" :key="p" href="#" @click.prevent="() => changePage(p)" :class="[
                currentPage === p
                    ? 'px-4 py-2 bg-gradient text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 font-medium shadow-md'
                    : 'px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200',
            ]">
                {{ p }}
            </a>

            <!-- Next -->
            <a href="#" @click.prevent="() => changePage(currentPage + 1)" v-if="currentPage < totalPage"
                class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-all duration-200 flex items-center">
                Next <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </nav>
    </div>
</template>