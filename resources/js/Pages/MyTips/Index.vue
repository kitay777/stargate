<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const tips = computed(() => page.props.tips)
const total = computed(() => page.props.total)

function formatDate(date) {
  const d = new Date(date)
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}
</script>

<template>
  <AppLayout title="💰 受け取ったTIP一覧">
    <div class="p-4 space-y-6">
      <!-- 💰 合計 -->
      <div class="text-green-400 text-lg font-bold">
        総受取TIP：¥{{ total.toLocaleString() }}
      </div>

      <!-- 📊 TIP履歴一覧 -->
      <div>
        <p class="font-bold text-lg">📋 TIP履歴</p>
        <ul v-if="tips.length" class="space-y-2 text-sm text-white-700">
          <li v-for="tip in tips" :key="tip.id" class="border-b pb-2">
            <p class="font-semibold text-green-600">¥{{ tip.amount }}</p>
            <p class="text-white-500">🧑 送信者：{{ tip.user?.name ?? '匿名' }}</p>
            <p class="text-white-600">📺 ルーム：{{ tip.room?.name ?? '不明なルーム' }}</p>
            <p class="text-white-400">🕒 {{ formatDate(tip.created_at) }}</p>
          </li>
        </ul>
        <p v-else class="text-white-400">TIP履歴はまだありません</p>
      </div>
    </div>
  </AppLayout>
</template>
