<template>
    <AppLayout title="ルーム作成">
        <template #default>
                <div class="max-w-6xl mx-auto px-4 py-12">
                
                <!-- タイトル -->

        <div class="flex justify-center -mb-12 relative z-10">
                <div class="flex justify-center mb-8">
                    <img
                        src="/assets/imgs/make_room_icon.png"
                        alt="ルーム作成"
                        class="w-48 h-auto drop-shadow-lg"
                    />
                </div>

        </div>
            




                <!-- カード -->
                <div
                    class="bg-white/10 backdrop-blur rounded-2xl shadow-xl p-8"
                >
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- ルーム名 -->
                        <div>
                            <label class="label">ルーム名</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="input"
                                placeholder="例：深夜まったり雑談"
                            />
                            <p v-if="form.errors.name" class="error">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- 説明 -->
                        <div>
                            <label class="label">説明</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                maxlength="1024"
                                class="input"
                                placeholder="このルームについて説明してください"
                            />
                            <p v-if="form.errors.description" class="error">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- 時刻 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label">開始時刻</label>
                                <input
                                    v-model="form.start"
                                    type="datetime-local"
                                    class="input"
                                />
                                <p v-if="form.errors.start" class="error">
                                    {{ form.errors.start }}
                                </p>
                            </div>

                            <div>
                                <label class="label">終了予定時刻</label>
                                <input
                                    v-model="form.end"
                                    type="datetime-local"
                                    class="input"
                                />
                                <p v-if="form.errors.end" class="error">
                                    {{ form.errors.end }}
                                </p>
                            </div>
                        </div>

                        <!-- カテゴリー -->
                        <div>
                            <label class="label">カテゴリー</label>
                            <select v-model="form.category_id" class="input">
                                <option value="" disabled>
                                    選択してください
                                </option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="error">
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <!-- 画像 -->
                        <div>
                            <label class="label"
                                >ルーム画像
                                <span class="text-sm text-gray-400"
                                    >(1240×744 推奨)</span
                                ></label
                            >

                            <div
                                class="mt-2 border-2 border-dashed border-white/30 rounded-xl p-4 text-center hover:border-blue-400 transition"
                            >
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    class="w-full text-sm text-gray-300"
                                />
                            </div>
                        </div>

                        <!-- ボタン -->
                        <div class="pt-6">
                            <button
                                type="submit"
                                class="w-full py-3 rounded-xl font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:scale-[1.02] hover:shadow-lg transition"
                            >
                                ルームを作成する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { onMounted } from "vue";
import axios from "axios";

const categories = ref([]);

// Inertia用フォーム
const form = useForm({
    name: "",
    description: "",
    start: "",
    end: "",
    category_id: "",
    image: null,
});

// サブミット処理
// 送信処理（POST）
const submit = () => {
    console.log("送信データ:", form);
    form.post(route("rooms.store"), {
        forceFormData: true,
    });
};

function handleImageUpload(event) {
    form.image = event.target.files[0];
}
// カテゴリー一覧の取得（mounted時に取得）
onMounted(async () => {
    const response = await axios.get("/categories");
    categories.value = response.data;
    console.log("カテゴリ一覧:", categories.value);
});
</script>

<style scoped>
.label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #e5e7eb;
    margin-bottom: 0.25rem;
}

.input {
    width: 100%;
    padding: 0.65rem 0.75rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.9);
    color: #111;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.input:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.4);
}

.error {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #f87171;
}
</style>
