<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

defineProps({
    officialAvatars: Array,
    userAvatars: Array,
    currentAvatarId: Number,
});

function selectAvatar(avatarId) {
    router.post(route("avatar.select.use", avatarId));
}

function goClothes(avatarId) {
    router.visit(`/avatar/${avatarId}/clothes`);
}
</script>

<template>
    <AppLayout title="アバター選択">
        <div class="max-w-4xl mx-auto p-6">
            <h1 class="text-xl font-bold mb-8">アバターを選択</h1>

            <!-- ================== 公式 ================== -->
            <h2 class="text-lg font-semibold mb-3">公式アバター</h2>

            <div v-if="officialAvatars.length"
                 class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-10">
                <div
                    v-for="avatar in officialAvatars"
                    :key="'official-' + avatar.id"
                    class="border rounded p-3 text-center relative"
                    :class="{ 'ring-2 ring-green-500': avatar.id === currentAvatarId }"
                >
                    <!-- APNG -->
                    <div
                        v-if="avatar.type === 'apng' && avatar.apng_parts_urls"
                        class="relative mx-auto w-32 aspect-[1/2] overflow-hidden"
                    >
                        <img :src="avatar.apng_parts_urls.base" class="absolute inset-0 w-full h-full object-contain" />
                        <img :src="avatar.apng_parts_urls.eyes_open" class="absolute inset-0 w-full h-full object-contain" />
                        <img :src="avatar.apng_parts_urls.mouth_close" class="absolute inset-0 w-full h-full object-contain" />
                    </div>
                    <!-- VRM -->
                    <img
                        v-else-if="avatar.thumbnail_url"
                        :src="avatar.thumbnail_url"
                        class="mx-auto h-32 object-contain"
                    />

                    <!-- fallback -->
                    <div
                        v-else
                        class="h-32 flex items-center justify-center text-xs text-gray-400"
                    >
                        サムネなし
                    </div>

                    <div class="mt-2 text-sm font-semibold">
                        {{ avatar.name }}
                    </div>

                    <div class="text-xs text-gray-500">
                        {{ avatar.type.toUpperCase() }}
                    </div>

                    <button
                        @click="selectAvatar(avatar.id)"
                        class="mt-2 w-full text-sm py-1 rounded bg-gray-800 text-white hover:bg-gray-700"
                    >
                        これを使う
                    </button>

                    <span
                        v-if="avatar.id === currentAvatarId"
                        class="absolute top-1 right-1 text-xs bg-green-600 text-white px-2 py-0.5 rounded"
                    >
                        使用中
                    </span>

                    <button
                        @click="goClothes(avatar.id)"
                        class="mt-1 w-full text-sm py-1 rounded bg-blue-600 text-white hover:bg-blue-500"
                    >
                        👕 服を選ぶ
                    </button>
                </div>
            </div>

            <div v-else class="text-gray-400 mb-10">
                公式アバターはまだありません。
            </div>


            <!-- ================== マイ ================== -->
            <h2 class="text-lg font-semibold mb-3">マイアバター</h2>

            <div v-if="userAvatars.length"
                 class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div
                    v-for="avatar in userAvatars"
                    :key="'user-' + avatar.id"
                    class="border rounded p-3 text-center relative"
                    :class="{ 'ring-2 ring-green-500': avatar.id === currentAvatarId }"
                >
                    <!-- APNG -->
                    <div
                        v-if="avatar.type === 'apng' && avatar.apng_parts_urls"
                        class="relative mx-auto w-32 aspect-[1/2] overflow-hidden"
                    >
                        <img :src="avatar.apng_parts_urls.base" class="absolute inset-0 w-full h-full object-contain" />
                        <img :src="avatar.apng_parts_urls.eyes_open" class="absolute inset-0 w-full h-full object-contain" />
                        <img :src="avatar.apng_parts_urls.mouth_close" class="absolute inset-0 w-full h-full object-contain" />
                    </div>

                    <!-- VRM -->
                    <img
                        v-else-if="avatar.thumbnail_url"
                        :src="avatar.thumbnail_url"
                        class="mx-auto h-32 object-contain"
                    />

                    <div class="mt-2 text-sm font-semibold">
                        {{ avatar.name }}
                    </div>

                    <div class="text-xs text-gray-500">
                        {{ avatar.type.toUpperCase() }}
                    </div>

                    <button
                        @click="selectAvatar(avatar.id)"
                        class="mt-2 w-full text-sm py-1 rounded bg-gray-800 text-white hover:bg-gray-700"
                    >
                        これを使う
                    </button>

                    <span
                        v-if="avatar.id === currentAvatarId"
                        class="absolute top-1 right-1 text-xs bg-green-600 text-white px-2 py-0.5 rounded"
                    >
                        使用中
                    </span>

                    <button
                        @click="goClothes(avatar.id)"
                        class="mt-1 w-full text-sm py-1 rounded bg-blue-600 text-white hover:bg-blue-500"
                    >
                        👕 服を選ぶ
                    </button>
                </div>
            </div>

            <div v-else class="text-gray-400">
                まだアップロードしたアバターがありません。
            </div>

        </div>
    </AppLayout>
</template>