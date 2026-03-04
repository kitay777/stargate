<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="max-w-md w-full bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">代理店ログイン</h2>
  
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="email">メールアドレス</label>
            <input
              v-model="form.email"
              id="email"
              type="email"
              required
              class="w-full border border-gray-300 p-2 rounded"
            />
          </div>
  
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="password">パスワード</label>
            <input
              v-model="form.password"
              id="password"
              type="password"
              required
              class="w-full border border-gray-300 p-2 rounded"
            />
          </div>
  
          <div class="mb-4 flex items-center">
            <input
              v-model="form.remember"
              id="remember"
              type="checkbox"
              class="mr-2"
            />
            <label for="remember" class="text-sm">ログイン情報を記憶する</label>
          </div>
  
          <div class="mb-4 text-red-600 text-sm" v-if="errors.email">{{ errors.email }}</div>
  
          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
          >
            ログイン
          </button>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { reactive } from 'vue'
  import { useForm } from '@inertiajs/vue3'
  
  const form = useForm({
    email: '',
    password: '',
    remember: false,
  })
  
  const submit = () => {
    form.post('/agency/login', {
      preserveScroll: true,
      onError: () => {
        // エラーハンドリングはフォームバリデーションで表示済み
      },
    })
  }
  
  const errors = form.errors
  </script>
  