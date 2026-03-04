<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
  avatars: Array
})

const form = useForm({
  avatar_id: '',
  name: '',
  price: 0,
  sort_order: 0,
  is_visible: true,
  file: null,
  thumbnail: null
})

const submit = () => {
  form.post('/admin/clothes')
}
</script>

<template>
  <AdminLayout>
    <h1 class="text-2xl font-bold mb-6">服登録</h1>

    <form @submit.prevent="submit" class="space-y-4 max-w-lg">

      <select v-model="form.avatar_id" class="border p-2 rounded w-full">
        <option disabled value="">アバター選択</option>
        <option v-for="a in avatars" :key="a.id" :value="a.id">
          {{ a.name }}
        </option>
      </select>

      <input v-model="form.name"
             placeholder="服の名前"
             class="border p-2 rounded w-full" />

      <input type="number"
             v-model="form.price"
             placeholder="価格"
             class="border p-2 rounded w-full" />

      <input type="number"
             v-model="form.sort_order"
             placeholder="表示順"
             class="border p-2 rounded w-full" />

      <label class="flex items-center gap-2">
        <input type="checkbox" v-model="form.is_visible" />
        公開する
      </label>

      <div>
        <label>VRMファイル</label>
        <input type="file"
               @change="e => form.file = e.target.files[0]" />
      </div>

      <div>
        <label>サムネイル</label>
        <input type="file"
               @change="e => form.thumbnail = e.target.files[0]" />
      </div>

      <button
        type="submit"
        class="bg-blue-600 text-white px-4 py-2 rounded"
      >
        登録
      </button>

    </form>
  </AdminLayout>
</template>
