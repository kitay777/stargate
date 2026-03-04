<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineProps({
  stats: Object
})
</script>

<template>
  <AdminLayout>
    <h1 class="text-2xl font-bold mb-6">管理ダッシュボード</h1>

    <div class="grid grid-cols-4 gap-6">

      <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-500 text-sm">総ユーザー数</div>
        <div class="text-2xl font-bold">{{ stats.users }}</div>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-500 text-sm">配信中</div>
        <div class="text-2xl font-bold">{{ stats.liveRooms }}</div>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-500 text-sm">商品数</div>
        <div class="text-2xl font-bold">{{ stats.products }}</div>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-500 text-sm">通報件数</div>
        <div class="text-2xl font-bold text-red-500">{{ stats.reports }}</div>
      </div>

    </div>
  </AdminLayout>
</template>
