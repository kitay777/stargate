<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  products: Array,
  allUsers: Array,
})

const selectedUser = ref(null)

const form = useForm({
  product_id: null,
  user_id: null,
})

const addSeller = (productId) => {
  if (!selectedUser.value) return alert('ユーザーを選択してください')
  form.product_id = productId
  form.user_id = selectedUser.value
  form.post(`/products/${productId}/sellers`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedUser.value = null
    },
  })
}

const removeSeller = (productId, userId) => {
  if (!confirm('このユーザーを販売者から削除しますか？')) return
  form.delete(`/products/${productId}/sellers/${userId}`, {
    preserveScroll: true,
  })
}
const approveSale = (saleId) => {
  form.post(`/sales/${saleId}/approve`, { preserveScroll: true })
}
const rejectSale = (saleId) => {
  form.post(`/sales/${saleId}/reject`, { preserveScroll: true })
}
const formatDateTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  const month = d.getMonth() + 1
  const day = d.getDate()
  const hour = d.getHours()
  const min = String(d.getMinutes()).padStart(2, '0')
  return `${month}/${day} ${hour}:${min}`
}

</script>

<template>
  <AppLayout title="販売者管理">
    <Head title="販売者管理" />
    <div class="max-w-4xl mx-auto p-6 text-white">
      <h1 class="text-2xl font-bold mb-6">販売者管理</h1>
      <div v-for="product in props.products" :key="product.id" class="mb-6 p-4 bg-gray-800 rounded">
        <h2 class="text-lg font-bold mb-2">🛒 {{ product.name }}</h2>

        <ul>
          <li
            v-for="sale in product.sales || []"
            :key="sale.id"
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-4 py-2 border-b border-gray-700"
          >
            <!-- 左側：販売者 + 放送期間 -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 text-sm">
              <span class="whitespace-nowrap">
                {{ sale.room?.user?.name || '販売者未設定' }}
                （{{ sale.room?.name || '未設定ルーム' }}）
                <span v-if="sale.status === 'pending'" class="text-yellow-400 ml-1">[申請中]</span>
                <span v-else-if="sale.status === 'approved'" class="text-green-400 ml-1">[承認済]</span>
                <span v-else-if="sale.status === 'rejected'" class="text-red-400 ml-1">[却下]</span>
              </span>

              <span v-if="sale.room?.start" class="flex items-center text-gray-300 whitespace-nowrap">
                🖥️ 期間: {{ formatDateTime(sale.room.start) }} ～ {{ formatDateTime(sale.room.end) }}
              </span>
            </div>

            <!-- 右側：操作ボタン -->
            <div class="flex gap-2 justify-end shrink-0">
              <button
                v-if="sale.status === 'pending'"
                @click="approveSale(sale.id)"
                class="bg-green-500 text-white px-2 py-1 rounded"
              >✔ 承認</button>

              <button
                v-if="sale.status === 'pending'"
                @click="rejectSale(sale.id)"
                class="bg-red-500 text-white px-2 py-1 rounded"
              >✖ 却下</button>

              <button
                @click="removeSale(sale.id)"
                class="bg-gray-500 text-white px-2 py-1 rounded"
              >🗑 削除</button>
            </div>
          </li>
          <li v-if="!product.sales || product.sales.length === 0" class="text-gray-400 text-sm">
            申請はありません
          </li>
        </ul>
      </div>


    </div>
  </AppLayout>
</template>

<style scoped>
select {
  min-width: 200px;
}
</style>
