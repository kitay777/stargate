<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  product: Object,
  allUsers: Array,
})

const selectedUserId = ref('')

function addSeller() {
  if (!selectedUserId.value) return
  router.post(`/products/${props.product.id}/sellers`, {
    user_id: selectedUserId.value
  }, {
    preserveScroll: true,
    onSuccess: () => selectedUserId.value = ''
  })
}

function removeSeller(userId) {
  if (!confirm('この配信者を削除しますか？')) return
  router.delete(`/products/${props.product.id}/sellers/${userId}`, {
    preserveScroll: true
  })
}
</script>

<template>
  <div class="p-4 border rounded">
    <h2 class="text-lg font-bold mb-2">販売者（ライバー）管理</h2>

    <div class="mb-4">
      <label class="block text-sm mb-1">販売者を追加</label>
      <select v-model="selectedUserId" class="p-1 border rounded w-full">
        <option value="">選択してください</option>
        <option v-for="user in allUsers" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <button @click="addSeller" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded">追加</button>
    </div>

    <div>
      <h3 class="text-sm font-semibold mb-1">現在の販売者一覧:</h3>
      <ul>
        <li v-for="seller in product.sellers" :key="seller.id" class="flex justify-between items-center border-b py-1">
          <span>{{ seller.name }}</span>
          <button @click="removeSeller(seller.id)" class="text-red-500 text-sm">削除</button>
        </li>
      </ul>
    </div>
  </div>
</template>
