<template>
    <nav class="bg-white border-b border-gray-100 shadow-sm px-4 py-3 flex justify-between items-center">
      <div class="flex gap-6 items-center">
        <Link href="/dashboard" class="text-blue-600 font-semibold hover:underline">Dashboard</Link>
        <Link href="/profile" class="text-blue-600 font-semibold hover:underline">Profile</Link>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-gray-700 font-medium">{{ user?.name ?? 'ゲスト' }}</span>
        <button @click="logout" class="text-red-600 hover:underline">Log Out</button>
      </div>
    </nav>
  </template>
  
  <script setup>
  import { Link, router } from '@inertiajs/vue3'
  import { ref } from 'vue'
  
  const props = defineProps({
    user: {
      type: Object,
      required: true
    }
  })
  
  const logout = () => {
    router.post('/logout')
  }
  </script>
  