<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const props = defineProps({
  products: Object,            // ページネーション済み
  statusUrl: String,           // 例: https://moon.timesfun.net:8443/status
})

const items = ref(props.products) // 再取得で差し替えたい場合に備えて
const rooms = ref([])             // status API の結果（配信中判定用）
const nowTs = ref(Date.now())
let tick = null

const getStreamingRoomByProduct = (product) => {
  if (!product?.room?.id) return null
  return rooms.value.find(r => String(r.room) === String(product.room.id) && r.streaming)
}

const remainText = (p) => {
  if (!p?.end_at) return '—'
  const ms = new Date(p.end_at).getTime() - nowTs.value
  if (ms <= 0) return '終了'
  const s = Math.floor(ms / 1000)
  const d = Math.floor(s / 86400)
  const h = Math.floor((s % 86400) / 3600)
  const m = Math.floor((s % 3600) / 60)
  const sec = s % 60
  return d > 0 ? `${d}日${h}時間` : (h > 0 ? `${h}時間${m}分` : `${m}分${sec}秒`)
}

async function fetchStatus() {
  try {
    const res = await fetch(props.statusUrl)
    rooms.value = await res.json()
  } catch (e) {
    console.error('status取得失敗', e)
  }
}

// 任意：ページ自体を一定間隔でリフレッシュしたい場合はここでInertia再訪問でもOK
// import { router } from '@inertiajs/vue3'
// function refreshPage() { router.visit(route('auctions.open'), { replace: true, preserveScroll: true }) }

onMounted(() => {
  fetchStatus()
  tick = setInterval(() => { nowTs.value = Date.now(); fetchStatus() }, 5000)
})
onBeforeUnmount(() => { if (tick) clearInterval(tick) })
</script>

<template>
  <AppLayout title="開催中のオークション">
    <Head title="開催中のオークション" />

    <div class="max-w-6xl mx-auto p-4">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">🎯 開催中のオークション</h1>
        <Link href="/" class="text-sm underline">トップへ戻る</Link>
      </div>

      <div v-if="items.data?.length" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        <div
          v-for="p in items.data"
          :key="p.id"
          class="bg-white text-black border rounded shadow hover:shadow-lg transition overflow-hidden"
        >
          <Link :href="`/products/${p.id}`" class="block">
            <div class="relative">
              <span
                v-if="getStreamingRoomByProduct(p)"
                class="absolute left-2 top-2 text-[10px] font-bold px-2 py-1 rounded-full bg-red-600 text-white shadow"
              >
                LIVE
              </span>
              <img
                :src="p.images?.[0]?.path || '/images/no-image.png'"
                alt=""
                class="w-full h-40 object-cover"
              />
            </div>

            <div class="p-3">
              <div class="text-sm text-gray-500">
                <span v-if="p.auction_type === 'auction'">🏷️ オークション</span>
                <span v-else-if="p.auction_type === 'reverse'">💸 逆オークション</span>
              </div>
              <div class="mt-1 text-base font-semibold truncate">{{ p.name }}</div>
              <div class="text-xs text-gray-500 truncate">by {{ p.user?.name ?? '不明' }}</div>

              <div class="mt-2 flex items-end justify-between">
                <div class="text-lg font-bold">
                  💰 {{ (p.current_price ?? p.price)?.toLocaleString?.() ?? p.price }} 円
                </div>
                <div class="text-xs text-gray-600 text-right">
                  ⏳ {{ remainText(p) }}
                </div>
              </div>

              <div v-if="p.start_at && p.end_at" class="mt-1 text-[11px] text-gray-400">
                🕒 {{ p.start_at }} ～ {{ p.end_at }}
              </div>
            </div>
          </Link>

          <div class="px-3 pb-3">
            <div class="flex items-center justify-between">
              <Link
                :href="`/products/${p.id}`"
                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded"
              >
                入札する
              </Link>

              <a
                v-if="getStreamingRoomByProduct(p)"
                :href="`/viewer/${p.room.id}`"
                class="text-xs underline text-green-700"
              >
                📺 視聴へ
              </a>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-sm text-gray-500">現在、開催中のオークションはありません。</div>

      <!-- ページネーション -->
      <div class="mt-6 flex justify-center gap-2" v-if="items.links?.length">
        <Link
          v-for="link in items.links"
          :key="link.label"
          :href="link.url"
          v-html="link.label"
          class="px-3 py-1 border rounded"
          :class="{
            'bg-gray-200': link.active,
            'text-gray-400 pointer-events-none': !link.url,
          }"
        />
      </div>
    </div>
  </AppLayout>
</template>
