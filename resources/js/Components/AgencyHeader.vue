<!-- components/AgencyHeader.vue -->
<template>
    <header class="bg-white shadow p-4 flex justify-between items-center mb-6">
      <div class="text-xl font-bold text-gray-800">
        Neoria 代理店管理
      </div>
  
      <div class="flex items-center gap-4">
        <span class="text-gray-700">こんにちは、{{ agencyUser.name }} さん</span>
        <button
          @click="logout"
          class="text-red-600 text-sm hover:underline"
        >
          ログアウト
        </button>
      </div>
    </header>
  </template>
  
  <script setup>
  import { router } from '@inertiajs/vue3'
  import { defineProps } from 'vue'
  
  const props = defineProps({
    agencyUser: Object,
  })
  
  const logout = () => {
    router.post('/agency/logout')
  }
  </script>
  