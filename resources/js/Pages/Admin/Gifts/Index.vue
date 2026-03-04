<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    gifts: Array,
});

const form = ref({
    name: "",
    price: "",
    image: null,
});

const submit = () => {
    const data = new FormData();
    data.append("name", form.value.name);
    data.append("price", form.value.price);
    data.append("image", form.value.image);

    router.post("/admin/gifts", data);
};

const toggle = (gift) => {
    router.post(`/admin/gifts/${gift.id}/toggle`);
};

const remove = (gift) => {
    if (confirm("削除しますか？")) {
        router.delete(`/admin/gifts/${gift.id}`);
    }
};
</script>

<template>
    <AdminLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">ギフト管理</h1>

            <!-- 登録フォーム -->
            <div class="mb-8 p-4 border rounded bg-gray-50">
                <input
                    v-model="form.name"
                    placeholder="名前"
                    class="border p-2 mr-2"
                />
                <input
                    v-model="form.price"
                    placeholder="価格"
                    type="number"
                    class="border p-2 mr-2"
                />
                <input
                    type="file"
                    @change="(e) => (form.image = e.target.files[0])"
                    class="mr-2"
                />
                <button
                    @click="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    登録
                </button>
            </div>

            <!-- 一覧 -->
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2">画像</th>
                        <th class="p-2">名前</th>
                        <th class="p-2">価格</th>
                        <th class="p-2">状態</th>
                        <th class="p-2">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="gift in gifts" :key="gift.id" class="border-t">
                        <td class="p-2">
                            <img
                                :src="'/storage/' + gift.image_path"
                                class="w-16"
                            />
                        </td>
                        <td class="p-2">{{ gift.name }}</td>
                        <td class="p-2">{{ gift.price }}pt</td>
                        <td class="p-2">
                            <span
                                v-if="gift.is_active"
                                class="text-green-600 font-bold"
                            >
                                利用中
                            </span>
                            <span v-else class="text-red-600 font-bold">
                                停止中
                            </span>
                        </td>
                        <td class="p-2 space-x-2">
                            <button
                                @click="toggle(gift)"
                                class="px-3 py-1 rounded bg-yellow-500 text-white"
                            >
                                切替
                            </button>

                            <button
                                @click="remove(gift)"
                                class="px-3 py-1 rounded bg-red-600 text-white"
                            >
                                削除
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
