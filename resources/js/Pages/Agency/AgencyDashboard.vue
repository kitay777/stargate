<template>
  <div class="min-h-screen bg-gray-100 p-6">  
    <!-- ✅ ヘッダー -->
    <AgencyHeader :agencyUser="agencyUser" />
    <h1 class="text-3xl font-bold mb-6">代理店ダッシュボード</h1>

    <div class="bg-white p-4 rounded-xl shadow-md mb-6">
      <h2 class="text-xl font-semibold mb-4">ようこそ、{{ agencyUser.name }} さん</h2>
      <p class="text-gray-700">所属代理店: {{ agency.name }}</p>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold mb-4">所属ライバー一覧</h2>

      <!-- ✅ フラッシュメッセージ -->
      <div v-if="$page.props.flash?.success" class="mb-4 text-green-600 font-medium">
        {{ $page.props.flash.success }}
      </div>

      <!-- 登録フォーム開閉ボタン -->
      <div class="mb-4">
        <button @click="showForm = !showForm" class="text-blue-600 hover:underline text-sm">
          {{ showForm ? '▲ 登録フォームを閉じる' : '＋ 新規ライバー登録' }}
        </button>
      </div>

      <!-- 新規ライバー登録フォーム（折りたたみ） -->
      <div v-if="showForm" class="mb-6">
        <NewStreamer />
      </div>

      <ul v-if="streamers.length > 0" class="divide-y divide-gray-200">
        <li
          v-for="user in streamers"
          :key="user.id"
          class="py-2 flex justify-between items-center"
        >
          <div>
            <div class="text-lg font-medium">{{ user.name }}</div>
            <div class="text-sm text-gray-600">メール: {{ user.email }}</div>
          </div>

          <div class="flex gap-2 items-center">
            <!-- ✏️ 編集モーダル -->
            <EditProfileModal :user="user" :profile="user.profile" />

            <!-- 🗑️ 削除 -->
            <button
              @click="deleteUser(user.id)"
              class="text-red-600 hover:underline text-sm"
            >削除</button>
          </div>
        </li>
      </ul>

      <div v-else class="text-gray-500">所属ライバーはいません。</div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref } from 'vue'
import NewStreamer from './NewStreamer.vue'
import AgencyHeader from '@/Components/AgencyHeader.vue'
import EditProfileModal from '@/Components/EditProfile.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  agencyUser: Object,
  agency: Object,
  streamers: Array,
})

const showForm = ref(false)

const deleteUser = (userId) => {
  if (confirm('本当にこのライバーを削除しますか？')) {
    router.delete(`/streamers/${userId}`, {
      onSuccess: () => {
        console.log('削除成功')
      },
    })
  }
}
</script>
