<template>
  <AppLayout :title="user.name + 'のプロフィール'">
    <div class="p-4 text-white space-y-6">
      <!-- ユーザー基本情報 -->
      <div class="flex items-center gap-4">
        <img
          :src="user.profile_photo_url || '/images/default-profile.png'"
          alt="ユーザー画像"
          class="w-20 h-20 rounded-full object-cover border shadow"
        />
        <div>
          <h1 class="text-xl font-bold">{{ user.name }}</h1>
          <p class="text-sm text-white-500">登録日: {{ formatDate(user.created_at) }}</p>
        </div>
      </div>

      <!-- プロフィール情報 -->
      <div class="bg-white bg-opacity-10 p-4 rounded shadow text-sm space-y-2">
        <p><span class="font-semibold">性別：</span>{{ user.profile?.sex ?? '未設定' }}</p>
        <p><span class="font-semibold">年齢：</span>{{ getAge(user.profile?.birthday) ?? '未設定' }}</p>
        <p><span class="font-semibold">タイトル：</span>{{ user.profile?.title ?? 'なし' }}</p>
        <p><span class="font-semibold">メッセージ：</span></p>
        <p class="whitespace-pre-wrap bg-black/10 p-2 rounded">
          {{ user.profile?.message ?? 'メッセージなし' }}
        </p>
      </div>
    </div>
    <!-- 🔽 ユーザーの予約ルーム一覧 -->
    <ul class="space-y-1 text-sm text-white-700">
      <li
        v-for="room in reservedRooms"
        :key="room.id"
        class="p-0 flex gap-0 items-start"
        style="background-color: #111111; padding: 5px;"
      >
        <!-- ルーム画像 -->
        <a :href="`/viewer/${room.id}`">
          <img
            v-if="room.image_path"
            :src="`/storage/${room.image_path}`"
            alt="ルーム画像"
            class="w-32 h-20 object-cover rounded shadow-sm hover:opacity-80 transition"
          />
          <div
            v-else
            class="w-32 h-20 bg-gray-400 rounded shadow-sm flex items-center justify-center text-white text-xs"
          >
            No Image
          </div>
        </a>

        <!-- ユーザー画像 -->
        <div class="flex items-center" style="margin: 0 5px;">
          <a :href="route('users.show', room.user.id)">
            <img
              :src="room.user?.profile_photo_url || '/images/default-profile.png'"
              alt="ユーザー画像"
              class="w-10 h-10 rounded-full object-cover border shadow"
              :class="room.streaming ? 'border-4 border-green-500' : 'border-4 border-red-500'"
            />
          </a>
        </div>

        <!-- ルーム情報 -->
        <div class="flex flex-col flex-1">
          <a :href="route('rooms.show', room.id)">
            <p class="text-lg font-semibold text-white">{{ room.name }}</p>
            <p class="text-white-600">📅 {{ formatDateTime(room.start) }} 開始</p>
          </a>
          <p class="text-white-500">📂 {{ room.category?.name ?? '未分類' }}</p>
        </div>

        <!-- ⭐️ いいねボタン -->
        <form @submit.prevent="likeRoom(room)" class="ml-2">
          <button type="submit" class="flex items-center gap-1">
            <span :class="room.liked_by.length > 0 ? 'text-red-500' : 'text-white'">
              {{ room.liked_by.length > 0 ? '⭐️' : '⭐︎' }}
            </span>
            <span class="text-sm">{{ room.liked_by_count }}</span>
          </button>
        </form>
      </li>
    </ul>


  </AppLayout>
</template>

<script setup>
import { usePage, router } from '@inertiajs/vue3' 
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const user = page.props.user
const reservedRooms = usePage().props.reservedRooms

function formatDateTime(date) {
  if (!date) return ''
  const d = new Date(date)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}

function formatDate(date) {
  const d = new Date(date)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日`
}
function likeRoom(room) {
  router.post(route('rooms.like', room.id), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      if (room.liked_by.length > 0) {
        room.liked_by = []
        room.liked_by_count--
      } else {
        room.liked_by = [{}]
        room.liked_by_count++
      }
    }
  })
}
function getAge(birthday) {
  if (!birthday) return null
  const birth = new Date(birthday)
  const today = new Date()
  let age = today.getFullYear() - birth.getFullYear()
  const m = today.getMonth() - birth.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return `${age}歳`
}
</script>
