<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const props = defineProps({
  sales: Array,
})
const flash = page.props.flash
</script>

<template>
    <AppLayout title="カート一覧">
        <Head title="カート一覧" />
        <div v-if="flash?.success" class="mb-4 p-4 bg-green-500 text-white rounded shadow">
        {{ flash.success }}
        </div>


      <div class="max-w-4xl mx-auto p-6 text-white">
        <h1 class="text-2xl font-bold mb-6">🛒 カートに入っている商品</h1>
  
        <div v-if="sales.length === 0" class="text-center text-gray-400">
          カートに商品はありません。
        </div>
  
        <div v-else class="space-y-4">
          <div
            v-for="sale in sales"
            :key="sale.id"
            class="p-4 bg-gray-800 rounded shadow"
          >
            <h2 class="text-xl font-bold">{{ sale.product?.name ?? '商品なし' }}</h2>
            <p class="mt-2">金額: 💰 {{ sale.price.toLocaleString() }} 円</p>
            <p class="mt-1">数量: 📦 {{ sale.quantity }}</p>
            <p class="mt-1">送料: 🚚 {{ sale.shipping_price }} 円</p>
          </div>
        </div>
  
        <!-- ✅ 全体の外に購入確定ボタン -->
        <div class="mt-8 text-center" v-if="sales.length > 0">
          <form method="POST" action="/cart/checkout">
            <input type="hidden" name="_token" :value="csrf" />
            <button
              type="submit"
              class="bg-blue-500 text-white font-bold py-2 px-6 rounded-full shadow hover:bg-blue-600 transition"
            >
              💳 購入確定
            </button>
          </form>
        </div>
  
      </div>
    </AppLayout>
  </template>
  
