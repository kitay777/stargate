<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  purchases: Object,
})

</script>

<template>
  <AppLayout title="購入履歴">
    <Head title="購入履歴" />

    <div class="max-w-4xl mx-auto p-6 text-white">
      <h1 class="text-2xl font-bold mb-6">🛍️ 購入済み商品一覧</h1>

      <div v-if="purchases.length === 0" class="text-center text-gray-400">
        購入履歴はありません。
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="purchase in purchases.data"
          :key="purchase.id"
          class="p-4 bg-gray-800 rounded shadow"
        >
          <h2 class="text-xl font-bold">{{ purchase.product?.name ?? '商品なし' }}</h2>
          <p class="mt-2">金額: 💰 {{ purchase.price ? purchase.price.toLocaleString() : '0' }} 円</p>
          <p class="mt-1">数量: 📦 {{ purchase.quantity }}</p>
          <p class="mt-1">送料: 🚚 {{ purchase.shipping_price }} 円</p>
          <p class="text-sm text-gray-400 mt-2">購入日: 📅 {{ new Date(purchase.created_at).toLocaleString() }}</p>
        </div>
      </div>

    </div>
    <!-- ページネーション -->
    <div class="flex justify-center mt-6 space-x-2">
      <template v-for="link in purchases.links" :key="link.label">
        <a
          v-if="link.url"
          :href="link.url"
          v-html="link.label"
          class="px-3 py-1 border rounded text-white hover:bg-gray-600"
          :class="{ 'bg-gray-400': link.active }"
        ></a>
        <span
          v-else
          v-html="link.label"
          class="px-3 py-1 border rounded text-gray-400"
        ></span>
      </template>
    </div>

  </AppLayout>
</template>
