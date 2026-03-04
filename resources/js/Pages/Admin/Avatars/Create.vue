<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  name: "",
  type: "vrm",
  file: null,
  thumbnail: null,
  sort: 0,
});

const submit = () => {
  form.post(route("admin.avatars.store"));
};
</script>

<template>

  <AdminLayout>
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">公式アバター登録</h1>

    <div class="space-y-4">

      <input v-model="form.name" placeholder="アバター名" class="w-full border p-2 rounded" />

      <select v-model="form.type" class="w-full border p-2 rounded">
        <option value="vrm">VRM</option>
        <option value="apng">APNG</option>
      </select>

      <input type="file" @change="e => form.file = e.target.files[0]" />

      <input type="file" @change="e => form.thumbnail = e.target.files[0]" />

      <input type="number" v-model="form.sort" placeholder="表示順" class="w-full border p-2 rounded" />

      <button @click="submit"
        class="bg-indigo-600 text-white px-4 py-2 rounded">
        登録
      </button>

    </div>
  </div>
  </AdminLayout>
</template>