<script setup>
import AppAdminLayout from "@/Layouts/AdminLayout.vue";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { watch } from "vue";

const page = usePage();
const props = defineProps({
  avatars: Array
});

const deleteAvatar = (id) => {
  if (!confirm("本当に削除しますか？")) return;

  router.delete(route("admin.avatars.destroy", id));
};


watch(
  () => page.props.flash.success,
  (message) => {
    if (message) {
      alert(message);
    }
  },
  { immediate: true }
);
</script>

<template>
  <appAdminLayout>
  <div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">公式アバター一覧</h1>

    <div class="grid grid-cols-4 gap-6">
      <div
        v-for="avatar in avatars"
        :key="avatar.id"
        class="border rounded-lg p-4 bg-white shadow"
      >
        <img
          v-if="avatar.thumbnail_path"
          :src="`/storage/${avatar.thumbnail_path}`"
          class="w-full h-40 object-cover mb-2"
        />

        <div class="font-semibold">{{ avatar.name }}</div>

        <div class="text-sm text-gray-500">
          type: {{ avatar.type }}
        </div>

        <div class="text-sm text-gray-500">
          sort: {{ avatar.sort }}
        </div>

        <button
          @click="deleteAvatar(avatar.id)"
          class="mt-3 bg-red-500 text-white px-3 py-1 rounded"
        >
          削除
        </button>
      </div>
    </div>
  </div>
  </appAdminLayout> 
</template>