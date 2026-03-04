<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'

defineProps({
  users: Object,
  filters: Object
})

const messages = reactive({})

const banUser = (user) => {
  const reason = prompt('BAN理由を入力')
  if (!reason) return

  router.post(`/admin/users/${user.id}/ban`, {
    reason: reason
  })
}

const unbanUser = (user) => {
  router.post(`/admin/users/${user.id}/unban`)
}

const sendLine = (user) => {
  if (!messages[user.id]) return

  router.post(`/admin/users/${user.id}/send-line`, {
    message: messages[user.id]
  })

  messages[user.id] = ''
}
</script>

<template>
  <AdminLayout>
    <h1 class="text-2xl font-bold mb-6">ユーザー管理</h1>

    <!-- 検索 -->
    <div class="mb-4 flex gap-4">
      <input
        v-model="filters.search"
        @input="$inertia.get('/admin/users', filters)"
        placeholder="検索"
        class="border p-2 rounded"
      />

      <select
        v-model="filters.type"
        @change="$inertia.get('/admin/users', filters)"
        class="border p-2 rounded"
      >
        <option value="">全て</option>
        <option value="customer">customer</option>
        <option value="agency">agency</option>
        <option value="admin">admin</option>
      </select>
    </div>

    <!-- テーブル -->
    <div class="bg-white shadow rounded overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">名前</th>
            <th class="p-3 text-left">Type</th>
            <th class="p-3 text-left">状態</th>
            <th class="p-3 text-left">操作</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="user in users.data"
            :key="user.id"
            class="border-t"
          >
            <td class="p-3">{{ user.id }}</td>
            <td class="p-3">
              {{ user.name }}
              <div v-if="user.is_line_friend" class="text-xs text-green-500">
                LINE接続済
              </div>
              <div v-else class="text-xs text-gray-400">
                LINE未接続
              </div>
            </td>

            <td class="p-3">{{ user.type }}</td>

            <td class="p-3">
              <span
                v-if="user.is_banned"
                class="text-red-500 font-bold"
              >
                BAN
              </span>
              <span v-else class="text-green-500">
                正常
              </span>
            </td>

            <td class="p-3 space-y-2">

              <!-- BAN -->
              <div class="flex gap-2">
                <button
                  v-if="!user.is_banned"
                  @click="banUser(user)"
                  class="bg-red-500 text-white px-3 py-1 rounded"
                >
                  BAN
                </button>

                <button
                  v-else
                  @click="unbanUser(user)"
                  class="bg-green-500 text-white px-3 py-1 rounded"
                >
                  解除
                </button>
              </div>

              <!-- LINE送信 -->
              <div v-if="user.is_line_friend" class="flex gap-2">
                <input
                  v-model="messages[user.id]"
                  placeholder="LINEメッセージ"
                  class="border p-1 rounded text-sm w-48"
                />

                <button
                  @click="sendLine(user)"
                  class="bg-blue-500 text-white px-3 py-1 rounded text-sm"
                >
                  送信
                </button>
              </div>

            </td>

          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>
