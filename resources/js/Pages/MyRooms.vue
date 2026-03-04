<template>
  <AppLayout title="マイページ">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold text-white-800">📖 あなたの予約ルーム一覧</h1>
      <ul class="space-y-4 text-sm text-white-700">
        <!-- データがあるとき -->
        {{ myRooms.total }}件のルームが予約されています
        <template v-if="myRooms && myRooms.data && myRooms.data.length > 0">
          <ul class="space-y-4 text-sm text-white-700">
            <li
              v-for="room in myRooms.data"
              :key="room.id"
              class="border-b pb-2 flex items-start space-x-4"
            >
              <!-- ✅ 画像エリア -->
              <div class="w-28 h-16 rounded overflow-hidden bg-gray-300 flex items-center justify-center">
                <img
                  v-if="room.image_path"
                  :src="`/storage/${room.image_path}`"
                  alt="ルーム画像"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-xs text-gray-600">No Image</div>
              </div>

              <!-- ✅ テキストエリア -->
              <div class="flex-1">
                <p class="text-lg font-semibold text-blue-800">{{ room.name }}</p>
                <p class="text-white-600">📅 {{ formatDate(room.start) }} 開始</p>
                <p class="text-white-500">📂 {{ room.category?.name ?? '未分類' }}</p>

                <a
                  :href="`/presenter/${room.id}`"
                  class="inline-block mt-2 px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                >
                  📡 配信する
                </a>
                <a
                  :href="`/rooms/${room.id}/edit`"
                  class="inline-block mt-2 ml-2 px-3 py-1 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600"
                >
                  ✏️ 編集
                </a>
              </div>
            </li>


          </ul>
        </template>

        <template v-else>
          <p class="text-sm text-white-400">まだ予約されたルームはありません</p>
        </template>


  <!-- データがないとき -->
</ul>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue' // ← これ必要！

const myRooms = computed(() => usePage().props.myRooms)


function goToPage(pageNumber) {
  router.get(route('myrooms'), { page: String(pageNumber) }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}
</script>
