<template>
  <AppLayout title="ルーム作成完了">
    <template #default>
      <div class="max-w-xl mx-auto py-10">
        <h1 class="text-2xl font-bold text-green-600 mb-6">ルームを作成しました！</h1>
        <p class="mb-2"><strong>ルーム名:</strong> {{ room.name }}</p>
        <p class="mb-2"><strong>説明:</strong> </p>
        <p class="text-gray-700 leading-relaxed" v-html="formatDescription(room.description)"></p><p class="mb-2"><strong>開始:</strong> {{ formatDate(room.start) }}</p>
        <p class="mb-2"><strong>終了:</strong> {{ formatDate(room.end) }}</p>
        <img
          v-if="room.image_path"
          :src="`/storage/${room.image_path}`"
          class="w-full h-auto rounded"
        />
        <p class="mb-6"><strong>カテゴリー:</strong> {{ room.category?.name ?? '未設定' }}</p>

        <Link :href="route('dashboard')" class="text-blue-500 underline">ダッシュボードに戻る</Link>
      </div>
    </template>
  </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  room: Object,
})

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  const h = String(d.getHours()).padStart(2, '0')
  const min = String(d.getMinutes()).padStart(2, '0')
  return `${y}年${m}月${day}日 ${h}時${min}分`
}
function formatDescription(text) {
  if (!text) return ''
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
    .replace(/\r?\n/g, '<br>') // ← 🔥ここで \n を <br> に変換
}

</script>
