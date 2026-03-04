<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  schedules: Array
})

const groupedByDate = computed(() => {
  const groups = {}
  for (const item of props.schedules) {
    const date = new Date(item.start)
    const key = `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`
    if (!groups[key]) groups[key] = []
    groups[key].push(item)
  }
  return groups
})

const formatTime = (datetime) => {
  const d = new Date(datetime)
  return `${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}
</script>

<template>
  <AppLayout title="ライブ配信予定">
  <Head title="ライブ配信予定" />
  <div class="max-w-4xl mx-auto p-6 text-white">
    <h1 class="text-2xl font-bold mb-6">📺 ライブ配信予定</h1>

    <div v-for="(items, date) in groupedByDate" :key="date" class="mb-6">
      <h2 class="text-lg font-semibold text-yellow-300 mb-2">📅 {{ date }}</h2>
      <ul>
        <li v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 border-b border-gray-700 py-3">
          <div class="flex items-center gap-4">
            <!-- 商品画像 -->
            <img :src="item.product_image" alt="商品" class="w-16 h-16 object-cover rounded" />

            <div>
              <div class="font-semibold">{{ item.product_name }}
              <span class="bg-gray-700 text-xs text-white px-2 py-1 rounded">
                {{ item.category_name }}
              </span>
              </div>
              <div class="text-sm text-gray-300 flex items-center gap-2">
                <!-- ライバー顔 -->
                <img :src="item.user_photo" alt="ライバー" class="w-6 h-6 rounded-full" />
                {{ item.user_name }}（{{ formatTime(item.start) }}〜）
              </div>
            </div>
          </div>

          <a :href="`/viewer/${item.room_id}`" class="text-blue-400 hover:underline whitespace-nowrap">▶ 視聴</a>
        </li>

      </ul>
    </div>
  </div>
  </AppLayout>
</template>
