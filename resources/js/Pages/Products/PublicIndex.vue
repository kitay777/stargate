<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
const displayMode = ref('grid') // 初期状態はグリッド（2列）


const props = defineProps({
  products: Object,
  streamingRooms: Array
})

const rooms = ref([])
const products = ref(props.products); 

async function fetchRoomList() {
  try {
    const res = await fetch('https://moon.timesfun.net:8443/status')
    const json = await res.json()
    console.log('🌐 streamingRooms:', json) // ← ここで構造確認
    rooms.value = json
  } catch (e) {
    console.error('❌ Room一覧取得失敗:', e)
  }
}

onMounted(() => {
  fetchRoomList()
  fetchProducts()
  fetchStreamingRoomsInfo() 
  setInterval(() => {
    fetchRoomList();
    fetchProducts(); // ✅ 5秒ごとに再取得
    fetchStreamingRoomsInfo() 
  }, 5000);
})

const streamingRoomDetails = ref([]);

async function fetchStreamingRoomsInfo() {
  try {
    const res = await fetch('https://moon.timesfun.net:8443/status');
    const rawRooms = await res.json();
    rooms.value = rawRooms;

    // Laravel側に一括問い合わせ
    const roomIds = rawRooms.map(r => r.room); // ["30", "31", ...]
    const roomRes = await axios.post('/api/rooms/streaming-details', { ids: roomIds });
    streamingRoomDetails.value = roomRes.data;
  } catch (e) {
    console.error('❌ 配信ルーム情報取得失敗:', e);
  }
}
// ✅ 各商品の配信情報を判定
const getStreamingRoomByProduct = (product) => {
  if (!product.room) return null; // 予約されてない商品
  return rooms.value.find(room => String(room.room) === String(product.room.id) && room.streaming)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const d = new Date(dateString)
  return `${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}
async function fetchProducts() {
  try {
    const res = await fetch('/products-all-api'); // 🔥 ここはAPIエンドポイント
    const data = await res.json();
    products.value = data.products; // ✅ 最新データに置き換える！
  } catch (e) {
    console.error('❌ 商品一覧取得失敗:', e);
  }
}

const uniqueUsers = computed(() => {
  const usersMap = new Map()

  for (const categoryId in props.categoryRooms) {
    const rooms = props.categoryRooms[categoryId].data
    rooms.forEach(room => {
      const user = room.user
      if (user?.id) {
        // ✅ 既に登録済みなら、どちらかが配信中なら true
        if (usersMap.has(user.id)) {
          const existing = usersMap.get(user.id)
          existing.streaming ||= room.streaming === true
        } else {
          // ✅ user に streaming フラグを付けて登録
          usersMap.set(user.id, { ...user, streaming: room.streaming === true })
        }
      }
    })
  }
  return Array.from(usersMap.values())
})

</script>

<template>
  <AppLayout title="商品一覧">
    <Head title="商品一覧" />
      <div class="flex justify-end gap-4 mt-4 flex-wrap sm:flex-nowrap">
        <!-- 🎤 ライバー登録 -->
        <Link
          href="/register?role=streamer"
          class="inline-flex items-center h-8 pl-0 pr-4 bg-green-300 text-green-900 font-semibold rounded-full shadow hover:bg-green-400 transition overflow-hidden"
        >
          <!-- グラデーション丸 -->
          <span class="w-8 h-8 flex items-center justify-center bg-gradient-to-br from-white via-green-100 to-green-200 text-green-800 rounded-full shadow-inner">
            🎤
          </span>
          <span class="ml-2 whitespace-nowrap text-sm">ライバー登録</span>
        </Link>

        <!-- 🛍️ 販売者登録 -->
        <Link
          href="/register?role=seller"
          class="inline-flex items-center h-8 pl-0 pr-4 bg-emerald-400 text-white font-semibold rounded-full shadow hover:bg-emerald-500 transition overflow-hidden mr-3"
        >
          <span class="w-8 h-8 flex items-center justify-center bg-gradient-to-br from-white via-emerald-100 to-emerald-300 text-emerald-700 rounded-full shadow-inner">
            🛍️
          </span>
          <span class="ml-2 whitespace-nowrap text-sm">販売者登録</span>
        </Link>
      </div>
      <!-- 🔥 コンパクトなカード型ライブ配信（常に2列） -->
      <div class="max-w-6xl mx-auto p-4 pt-2">
        <h2 class="text-base font-bold text-black mb-2">
          📡 現在ライブ配信中
        </h2>

        <!-- ✅ 配信がある場合 -->
        <div v-if="streamingRoomDetails.length > 0" class="grid grid-cols-2 gap-2">
          <div
            v-for="room in streamingRoomDetails"
            :key="room.id"
            class="flex items-center gap-2 p-2 bg-white rounded shadow-sm hover:shadow-md transition"
          >
          {{ getStreamingRoomByProduct(room) ? 'LIVE' : 'OFFLINE' }}
            <img
              :src="room.image_path || '/images/no-image.png'"
              alt="room thumb"
              class="w-12 h-12 object-cover rounded border"
            />

            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold text-black truncate leading-snug">
                {{ room.name }}
              </div>
              <div class="text-xs text-gray-500 truncate leading-none">
                by {{ room.user?.name || '不明' }}
              </div>
            </div>

            <Link
              :href="`/viewer/${room.id}`"
              class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 whitespace-nowrap"
            >
              ▶︎
            </Link>
          </div>
        </div>

        <!-- ✅ 配信がない場合 -->
        <div v-else class="text-sm text-gray-500">
          現在ライブ配信はありません。
        </div>
      </div>

          <div
            v-for="user in uniqueUsers"
            :key="user.id"
            class="flex flex-col items-center text-xs text-white-700 min-w-fit"
          >
          </div>

    <div class="max-w-6xl mx-auto p-4">
      <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">🛍 商品一覧</h1>
      
      <div class="flex gap-2">
        <button
          class="px-3 py-1 rounded border font-semibold"
          :class="displayMode === 'grid' ? 'bg-white text-gray-800 border-gray-400' : 'bg-gray-800 text-white shadow-md'"
          @click="displayMode = 'grid'"
        >
          グリッド
        </button>
        <button
          class="px-3 py-1 rounded border font-semibold"
          :class="displayMode === 'list' ? 'bg-white text-gray-800 border-gray-400' : 'bg-gray-800 text-white shadow-md'"
          @click="displayMode = 'list'"
        >
          リスト
        </button>
      </div>
    </div>


      <!-- 商品グリッド -->
      <!-- ✅ モバイルから2列表示 -->
      <div
      :class="[
        displayMode === 'grid' ? 'grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4' : 'grid grid-cols-1',
        'gap-[1px]'
      ]"
    >


        <div
          v-for="product in products.data" :key="product.id"
          class="x bg-white text-black border rounded shadow hover:shadow-lg transition"
        >
          <Link
            :href="`/products/${product.id}`"
            class="block bg-white text-black border rounded shadow hover:shadow-lg transition"
          >
            <!-- 商品カード -->
            <div class="relative">
              <!-- ✅ 顔写真だけ絶対配置で、画像に半分かぶせる＆左に寄せる -->
              <div class="absolute left-[5px] -bottom-6 w-12 h-12 rounded-full border-2 border-black bg-white overflow-hidden">
                <img
                  :src="product.user?.profile_photo_path ? `/storage/${product.user.profile_photo_path}` : '/assets/imgs/okiniiri.png'"
                  alt="ユーザー画像"
                  class="w-full h-full object-cover z-29999"
                />
              </div>
              <img
                v-if="product.images?.length"
                :src="product.images[0].path"
                class="w-full h-48 object-cover rounded z-0"
              />


            </div>

            <!-- ✅ ユーザー名を商品画像の真下にぴったり表示 -->
            <div class="mt-0 pl-[60px] text-sm font-mediumpl-[5px]">
              <div class="w-12"></div> <!-- 顔写真と同じ幅のスペースを確保 -->
              <span class="text-sm font-medium text-black">{{ product.user.name }}</span>
            </div>

              <!-- ✅ 商品情報 -->
            <div class="mt-3">
              <div class="text-lg font-semibold">{{ product.name }}</div>
              <p class="text-sm text-gray-500 mt-1">
                <span v-if="product.auction_type === 'auction'">🏷️ オークション</span>
                <span v-else-if="product.auction_type === 'reverse'">💸 逆オークション</span>
                <span v-else>📦 通常販売</span>
              </p>
              <p class="text-right text-lg font-bold text-black mt-1">
                💰 {{ product.price.toLocaleString() }} 円
              </p>
              <p v-if="product.start_at && product.end_at" class="text-xs text-gray-400 mt-1">
                🕒 {{ product.start_at }} ～ {{ product.end_at }}
              </p>
            </div>
          </Link>


      

          <div class="mt-2">
            <template v-if="getStreamingRoomByProduct(product)">
              <div class="text-sm text-green-600 font-semibold">
                📡 ライブ配信中！
                <a
                  :href="`/viewer/${product.room.id}`"
                  class="ml-2 text-blue-600 underline"
                >
                  視聴する
                </a>
              </div>
            </template>
            <template v-else>
              <div v-if="product.room" class="text-sm text-gray-500">
                📅 配信予定: {{ formatDate(product.room.start) }}
              </div>
              <div v-else class="text-sm text-gray-500">
                📴 配信なし
              </div>
            </template>
          </div>
        </div>

    
      </div>

      <!-- ページネーション -->
      <div class="mt-6 flex justify-center gap-2">
        <Link
          v-for="link in products.links"
          :key="link.label"
          :href="link.url"
          v-html="link.label"
          class="px-3 py-1 border rounded"
          :class="{
            'bg-gray-200': link.active,
            'text-gray-500 pointer-events-none': !link.url,
          }"
        />
      </div>
    </div>
  </AppLayout>
</template>
