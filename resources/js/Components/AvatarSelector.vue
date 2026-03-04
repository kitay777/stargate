<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const avatars = ref([]);
const loading = ref(false);
const selectedId = ref(null);

// 親（Presenter.vue）に通知
const emit = defineEmits(["selected"]);

onMounted(async () => {
    const res = await axios.get("/api/avatars");
    avatars.value = res.data;
});

async function selectAvatar(avatar) {
    if (loading.value) return;

    loading.value = true;
    selectedId.value = avatar.id;

    try {
        // DBに保存
        await axios.post("/api/avatars/select", {
            avatar_id: avatar.id,
        });

        // 最新の自分情報を取得
        const me = await axios.get("/api/me");

        // 親に vrm_url を渡す
        emit("selected", me.data.avatar.vrm_url);
    } catch (e) {
        console.error("❌ avatar select failed", e);
        alert("アバターの切替に失敗しました");
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div
            v-for="avatar in avatars"
            :key="avatar.id"
            @click="selectAvatar(avatar)"
            class="cursor-pointer rounded-lg border p-2 transition"
            :class="{
                'border-blue-500 ring-2 ring-blue-300': avatar.id === selectedId,
                'hover:bg-gray-100': avatar.id !== selectedId
            }"
        >
            <img
                v-if="avatar.thumbnail_url"
                :src="avatar.thumbnail_url"
                class="w-full h-32 object-cover rounded"
            />
            <div class="mt-2 text-center text-sm font-semibold">
                {{ avatar.name }}
            </div>
        </div>
    </div>

    <div v-if="loading" class="mt-3 text-center text-sm text-gray-500">
        切替中…
    </div>
</template>
