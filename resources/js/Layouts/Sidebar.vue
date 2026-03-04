<script setup>
defineProps({
    categories: Array,
});
</script>

<template>
    <nav class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">カテゴリー</h3>

        <div v-for="category in categories" :key="category.id" class="bg-white shadow rounded-lg p-4">
            <img v-if="category.image" :src="'/storage/' + category.image" alt="カテゴリー画像" class="w-full h-24 object-cover rounded">
            <img v-else src="/images/default-category.png" alt="デフォルト画像" class="w-full h-24 object-cover rounded">
            <p class="font-semibold mt-2">{{ category.name }}</p>
            <p class="text-sm text-gray-500">{{ category.message || '説明がありません' }}</p>
        </div>
    </nav>
</template>
