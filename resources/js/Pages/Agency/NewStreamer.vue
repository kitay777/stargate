<!-- NewStreamer.vue -->
<template>
  <form @submit.prevent="submit">
    <!-- ✅ 重複エラーが出るここが重要！ -->
    <div v-if="form.errors.email" class="mb-4 text-red-600 font-medium">
      {{ form.errors.email }}
    </div>

    <!-- 名前 -->
    <div class="mb-4">
      <label>名前</label>
      <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
    </div>

    <!-- メール -->
    <div class="mb-4">
      <label>メール</label>
      <input v-model="form.email" type="email" class="w-full border p-2 rounded" />
    </div>

    <!-- パスワード -->
    <div class="mb-4">
      <label>パスワード</label>
      <input v-model="form.password" type="password" class="w-full border p-2 rounded" />
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
      登録する
    </button>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
})

const submit = () => {
  form.post('/streamers', {
    preserveScroll: true,
    onError: () => {
      console.log('エラー内容:', form.errors)
    },
  })
}
</script>
