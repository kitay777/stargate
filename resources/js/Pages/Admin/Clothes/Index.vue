<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  clothes: Array,
  avatars: Array
})

const toggleActive = (item) => {
  router.patch(`/admin/clothes/${item.id}/toggle-active`)
}

const toggleVisible = (item) => {
  router.patch(`/admin/clothes/${item.id}/toggle-visible`)
}

const deleteItem = (item) => {
  if (!confirm('削除しますか？')) return
  router.delete(`/admin/clothes/${item.id}`)
}
</script>

<template>
  <AdminLayout>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">服管理</h1>
      <a
        href="/admin/clothes/create"
        class="bg-blue-600 text-white px-4 py-2 rounded"
      >
        新規登録
      </a>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-200">
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">サムネ</th>
            <th class="p-3 text-left">名前</th>
            <th class="p-3 text-left">価格</th>
            <th class="p-3 text-left">順番</th>
            <th class="p-3 text-left">公開</th>
            <th class="p-3 text-left">有効</th>
            <th class="p-3"></th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="item in clothes"
            :key="item.id"
            class="border-t"
          >
            <td class="p-3">{{ item.id }}</td>

            <td class="p-3">
              <img :src="`/storage/${item.thumbnail}`"
                   class="w-16 rounded" />
            </td>

            <td class="p-3">{{ item.name }}</td>

            <td class="p-3">
              {{ item.price }} pt
            </td>

            <td class="p-3">
              {{ item.sort_order }}
            </td>

            <td class="p-3">
              <button
                @click="toggleVisible(item)"
                :class="item.is_visible ? 'bg-green-500' : 'bg-gray-400'"
                class="text-white px-2 py-1 rounded"
              >
                {{ item.is_visible ? '公開中' : '非公開' }}
              </button>
            </td>

            <td class="p-3">
              <button
                @click="toggleActive(item)"
                :class="item.is_active ? 'bg-green-500' : 'bg-red-500'"
                class="text-white px-2 py-1 rounded"
              >
                {{ item.is_active ? '有効' : '停止' }}
              </button>
            </td>

            <td class="p-3">
              <button
                @click="deleteItem(item)"
                class="bg-red-500 text-white px-3 py-1 rounded"
              >
                削除
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>
