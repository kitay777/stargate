<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onBeforeUnmount } from "vue";
import { useForm, Head, Link } from "@inertiajs/vue3";

const fileType = ref(null); // 'vrm' | 'apng'

// プレビュー
const previewBase = ref(null);
const previewEyes = ref(null);
const previewMouth = ref(null);

const form = useForm({
    name: "",
    type: null,

    // VRM
    vrm: null,
    thumbnail: null,

    // APNG parts
    base: null,
    eyes_open: null,
    eyes_close: null,
    mouth_open: null,
    mouth_close: null,
});

function resetPreviews() {
    [previewBase, previewEyes, previewMouth].forEach((p) => {
        if (p.value) URL.revokeObjectURL(p.value);
        p.value = null;
    });
}

function selectType(type) {
    fileType.value = type;
    form.type = type;
    resetPreviews();
}

function onVrmChange(e) {
    form.vrm = e.target.files[0];
}

function onThumbnailChange(e) {
    form.thumbnail = e.target.files[0];
}

function onApngChange(key, e) {
    const file = e.target.files[0];
    if (!file) return;

    form[key] = file;

    if (key === "base") {
        previewBase.value = URL.createObjectURL(file);
    }
    if (key === "eyes_open") {
        previewEyes.value = URL.createObjectURL(file);
    }
    if (key === "mouth_close") {
        previewMouth.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (!fileType.value) {
        alert("アバター種別を選択してください");
        return;
    }

    if (fileType.value === "vrm") {
        if (!form.vrm) {
            alert("VRMファイルを選択してください");
            return;
        }
    }

    if (fileType.value === "apng") {
        const required = [
            "base",
            "eyes_open",
            "eyes_close",
            "mouth_open",
            "mouth_close",
        ];
        const missing = required.filter((k) => !form[k]);
        if (missing.length) {
            alert("APNGは5ファイルすべて必要です");
            return;
        }
    }

    form.post(route("avatar.upload.store"), {
        forceFormData: true,
    });
}

onBeforeUnmount(() => {
    resetPreviews();
});
</script>

<template>
    <AppLayout title="アバターアップロード">
        <Head title="アバターアップロード" />

        <div class="max-w-xl mx-auto p-6 space-y-6">
            <h1 class="text-xl font-bold">アバターアップロード</h1>

            <input
                v-model="form.name"
                type="text"
                placeholder="アバター名（任意）"
                class="w-full border px-3 py-2 rounded"
            />

            <!-- 種別選択 -->
            <div class="flex gap-4">
                <button
                    @click="selectType('vrm')"
                    :class="
                        fileType === 'vrm'
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-200'
                    "
                    class="px-4 py-2 rounded"
                >
                    VRM（3D）
                </button>
                <button
                    @click="selectType('apng')"
                    :class="
                        fileType === 'apng'
                            ? 'bg-green-600 text-white'
                            : 'bg-gray-200'
                    "
                    class="px-4 py-2 rounded"
                >
                    APNG（2D）
                </button>
            </div>

            <!-- VRM -->
            <div v-if="fileType === 'vrm'" class="space-y-3">
                <input type="file" accept=".vrm" @change="onVrmChange" />
                <input
                    type="file"
                    accept="image/png,image/jpeg"
                    @change="onThumbnailChange"
                />
                <p class="text-xs text-gray-500">※ サムネイルは任意</p>
            </div>

            <!-- APNG -->
            <!-- APNG -->
            <div v-if="fileType === 'apng'" class="space-y-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">base.png</label>
                    <input
                        type="file"
                        accept="image/png"
                        class="block w-full"
                        @change="(e) => onApngChange('base', e)"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">eyes_open.png</label>
                    <input
                        type="file"
                        accept="image/png"
                        class="block w-full"
                        @change="(e) => onApngChange('eyes_open', e)"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">eyes_close.png</label>
                    <input
                        type="file"
                        accept="image/png"
                        class="block w-full"
                        @change="(e) => onApngChange('eyes_close', e)"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">mouth_open.png</label>
                    <input
                        type="file"
                        accept="image/png"
                        class="block w-full"
                        @change="(e) => onApngChange('mouth_open', e)"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">mouth_close.png</label>
                    <input
                        type="file"
                        accept="image/png"
                        class="block w-full"
                        @change="(e) => onApngChange('mouth_close', e)"
                    />
                </div>

                <!-- 簡易プレビュー -->
                <div
                    v-if="previewBase"
                    class="relative w-48 h-48 border bg-white"
                >
                    <img :src="previewBase" class="absolute inset-0" />
                    <img
                        v-if="previewEyes"
                        :src="previewEyes"
                        class="absolute inset-0"
                    />
                    <img
                        v-if="previewMouth"
                        :src="previewMouth"
                        class="absolute inset-0"
                    />
                </div>
            </div>

            <button
                @click="submit"
                class="w-full bg-black text-white py-2 rounded"
                :disabled="form.processing"
            >
                保存
            </button>

            <Link href="/dashboard" class="text-sm text-gray-500 underline">
                戻る
            </Link>
        </div>
    </AppLayout>
</template>
