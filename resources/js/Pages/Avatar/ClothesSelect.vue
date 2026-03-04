<script setup>

import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const page = usePage();
const avatar = page.props.avatar; // Controllerから渡す

const clothesList = ref([]);
const selectedClothesId = ref(null);
const loading = ref(true);

async function loadClothes() {
    const res = await axios.get(`/api/avatar/${avatar.id}/clothes`);
    clothesList.value = res.data;
}

async function loadCurrent() {
    const res = await axios.get(`/api/avatar/${avatar.id}/clothes/current`);
    if (res.data) {
        selectedClothesId.value = res.data.id;
    }
}

async function selectClothes(id) {
    await axios.post(`/api/avatar/${avatar.id}/clothes/select`, {
        clothes_id: id,
    });

    selectedClothesId.value = id;
}

onMounted(async () => {
    await loadClothes();
    await loadCurrent();
    loading.value = false;
});
</script>

<template>
    <AppLayout title="アバターの服を選択">
        <div class="min-h-screen text-black p-6">
            <h1 class="text-xl font-bold mb-6">
                👗 {{ avatar.name }} の服を選択
            </h1>

            <div v-if="loading">Loading...</div>

            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div
                    v-for="cloth in clothesList"
                    :key="cloth.id"
                    class="cursor-pointer border rounded-lg p-3 transition"
                    :class="{
                        'border-pink-500 scale-105':
                            selectedClothesId === cloth.id,
                        'border-gray-700 hover:border-white':
                            selectedClothesId !== cloth.id,
                    }"
                    @click="selectClothes(cloth.id)"
                >
                    <img
                        :src="`/storage/${cloth.thumbnail}`"
                        class="w-full rounded mb-2"
                    />

                    <div class="text-sm text-center">
                        {{ cloth.name }}
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <a href="/avatar/select" class="text-black hover:text-white">
                    ← 戻る
                </a>
            </div>
        </div>
    </AppLayout>
</template>
