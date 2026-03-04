<template>
  <AppLayout :title="room.name + ' の詳細'">
    <div class="space-y-4">

      <!-- ルーム画像：幅いっぱい -->
      <div>
        <img
          v-if="room.image_path"
          :src="`/storage/${room.image_path}`"
          alt="ルーム画像"
          class="w-full max-h-80 object-cover rounded shadow"
        />
        <div
          v-else
          class="w-full h-40 bg-gray-400 rounded shadow-sm flex items-center justify-center text-white text-sm"
        >
          No Image
        </div>
      </div>

      <!-- 情報セクション -->
<!-- 情報セクション -->
<div class="space-y-2  px-8">
        <!-- タイトルといいねボタンを横並び -->
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold">{{ room.name }}</h1>

          <form @submit.prevent="likeRoom(room.id)">
            <button type="submit" class="flex items-center gap-1">
              <span :class="(room.liked_by && room.liked_by.length > 0) ? 'text-red-500' : 'text-white'">
                {{ (room.liked_by && room.liked_by.length > 0)? '⭐️' : '⭐︎' }}
              </span>
              <span class="text-sm">{{ room.liked_by_count }}</span>
            </button>
          </form>
        </div>

        <!-- その他の情報 -->
        <p class="text-sm text-white-500">📂 カテゴリー: {{ room.category?.name ?? '未分類' }}</p>
        <p class="text-sm text-white-500">👤 配信者: {{ room.user?.name ?? '不明' }}</p>
        <p class="text-sm text-white-500">📅 {{ formatDate(room.start) }} 開始</p>

  <!-- 🔴 放送中ボタン -->
<!-- 配信状態によってボタンを切り替える -->
<div class="mt-4">
  <!-- ✅ 放送中 -->
  <a
    v-if="Boolean(room.streaming)"
    :href="`/viewer/${room.id}`"
    class="inline-block px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700 transition"
  >
    🚨 放送中！視聴する
  </a>

  <!-- ❌ 未放送 -->
  <span
    v-else
    class="inline-block px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded opacity-70 cursor-not-allowed"
  >
    ⏸️ 未放送
  </span>
</div>
</div>





      <!-- 説明 -->
      <div class="text-white-600 text-sm  px-8">
        <hr />
        <p class="whitespace-pre-wrap bg-black/10 p-3 rounded mt-2">{{ room.description ?? '説明なし' }}</p>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps({
  room: Object,
})

const room = props.room

console.log("🔍 room.streaming:", room.streaming)

onMounted(() => {
  setInterval(() => {
    fetch(`/rooms/${room.id}/streaming-status`)
      .then(res => res.json())
      .then(data => room.streaming = data.streaming)
  }, 5000)
})

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}

function likeRoom(roomId) {
  router.post(route('rooms.like', roomId), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      if (room.liked_by?.length > 0) {
        room.liked_by = []
        room.liked_by_count--
      } else {
        room.liked_by = [{}]
        room.liked_by_count++
      }
    }
  })
}
</script>


