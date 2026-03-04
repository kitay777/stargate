<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import axios from 'axios'


const props = defineProps({
  product: Object,
  myRooms: Array,
  streamingRooms: Array
})

const selectedRoomId = ref('')


async function applyForSale() {
  if (!page.props.auth?.user) {
    alert('販売申請にはログインが必要です。');
    return;
  }
  try {
    await axios.post('/sales/apply', {
      product_id: props.product.id,
      room_id: selectedRoomId.value,
      price: props.product.price,
      shipping_price: props.product.shipping_fee ?? 0,
    })
    alert('販売申請を送信しました。')
  } catch (err) {
    console.error('❌ 販売申請エラー:', err)
    alert('申請に失敗しました')
  }
}


const remainingStock = ref((props.product.stock ?? 0) - (props.product.sales_count ?? 0))



async function fetchStock() {
  try {
    const res = await fetch(`/api/products/${props.product.id}/stock`);
    const data = await res.json();
    remainingStock.value = data.remaining_stock;
  } catch (error) {
    console.error('❌ 在庫取得失敗:', error);
  }
}



// 🛠 そのあとで、propsを使ったrefを作る
const price = ref(props.product.price)

// あとは普通に続く
const rooms = ref([])


async function fetchPrice() {
  try {
    const res = await fetch(`/api/products/${props.product.id}/price`);
    const data = await res.json();
    price.value = data.price; // ✅ 最新価格を代入
  } catch (e) {
    console.error('❌ 金額取得失敗:', e);
  }
}

let priceIntervalId = null;
let roomIntervalId = null;

onMounted(() => {
  fetchRoomList();
  fetchPrice();
  fetchStock(); // これも忘れず！

  roomIntervalId = setInterval(fetchRoomList, 5000);
  priceIntervalId = setInterval(fetchPrice, 5000);
  stockIntervalId = setInterval(fetchStock, 5000);
});

onBeforeUnmount(() => {
  clearInterval(roomIntervalId);
  clearInterval(priceIntervalId);
  clearInterval(stockIntervalId);
});





const page = usePage()
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';





// 🛠 サムネイル切り替え
const mainImage = ref(props.product.images?.[0]?.path || '')
const changeMainImage = (path) => {
  mainImage.value = path
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const d = new Date(dateString)
  return `${d.getMonth() + 1}月${d.getDate()}日 ${d.getHours()}時${String(d.getMinutes()).padStart(2, '0')}分`
}

const formatDateTime = (datetime) => {
  if (!datetime) return ''
  const d = new Date(datetime)
  const month = d.getMonth() + 1
  const day = d.getDate()
  const hours = d.getHours()
  const minutes = String(d.getMinutes()).padStart(2, '0')
  return `${month}/${day} ${hours}:${minutes}`
}


async function fetchRoomList() {
  try {
    const res = await fetch('https://moon.timesfun.net:8443/status')
    rooms.value = await res.json()
  } catch (e) {
    console.error('❌ Room一覧取得失敗:', e)
  }
}

// ✅ 配信中か判定
const getStreamingRoomByProduct = (product) => {
  if (!product.room) return null;
  return rooms.value.find(room => String(room.room) === String(product.room.id) && room.streaming)
}




async function handleAddToCart() {
  if (!page.props.auth?.user) {
    alert('カート機能はログインが必要です。');
    return;
  }
  await fetchStock(); // まず最新在庫チェック！

  if (remainingStock.value <= 0) {
    alert('申し訳ありません、売り切れました！');
    return;
  }

  // ✅ 在庫がある場合はフォームsubmit
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/cart';

  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrf;

  const productInput = document.createElement('input');
  productInput.type = 'hidden';
  productInput.name = 'product_id';
  productInput.value = props.product.id;

  form.appendChild(csrfInput);
  form.appendChild(productInput);

  document.body.appendChild(form);
  form.submit();
}

</script>



<template>
  <AppLayout :title="product.name">
    <Head :title="product.name" />

    <div class="max-w-4xl mx-auto p-4 text-black">

      <!-- 🔹 戻るボタン -->
      <div class="mb-6">
        <Link
          href="/products/all"
          class="inline-block bg-gradient-to-r from-purple-500 to-pink-500 text-black font-bold py-2 px-6 rounded-full shadow-lg hover:scale-105 transform transition"
        >
          ← 戻る
        </Link>
      </div>

      <!-- 🔹 メイン画像 -->
      <div class="w-full h-64 bg-gray-800 rounded mb-4 flex items-center justify-center overflow-hidden">
        <img
          v-if="mainImage"
          :src="mainImage"
          alt="Main Product Image"
          class="object-contain h-full"
        />
      </div>

      <!-- 🔹 サムネイル -->
      <div v-if="product.images?.length > 1" class="flex gap-2 overflow-x-auto mb-6">
        <img
          v-for="(image, index) in product.images"
          :key="index"
          :src="image.path"
          class="w-16 h-16 object-cover rounded border-2 cursor-pointer"
          :class="{'border-blue-400': mainImage === image.path, 'border-transparent': mainImage !== image.path}"
          @click="changeMainImage(image.path)"
        />
      </div>

      <!-- 🔹 タイトル -->
      <h1 class="text-2xl font-bold mb-4">{{ product.name }}</h1>

      <!-- 🔹 出品者情報 -->
      <div class="flex items-center mb-4">
        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white mr-4">
          <img
            :src="product.user?.profile_photo_path ? `/storage/${product.user.profile_photo_path}` : '/images/default-profile.png'"
            alt="出品者"
            class="w-full h-full object-cover"
          />
        </div>
        <div class="text-lg font-semibold">
          {{ product.user?.name }}
        </div>
      </div>

      <!-- 🔹 商品説明 -->
      <div class="mt-2 whitespace-pre-line text-sm text-black mb-6">
        {{ product.description }}
      </div>

      <!-- 🔹 金額 -->
      <div class="text-right text-2xl font-bold text-black mb-6">
        💰 {{ price.toLocaleString() }} 円
      </div>
      <!-- 🔹 残個数 -->
      <div class="text-right text-sm text-black-300 mb-6">
        📦 {{ remainingStock }} / {{ product.stock }} 個
      </div>


      <!-- 🔹 🔥 販売期間 -->
      <div v-if="product.start_at && product.end_at" class="text-sm text-black-300 mb-2">
        📦 販売期間: {{ formatDateTime(product.start_at) }} ～ {{ formatDateTime(product.end_at) }}
      </div>

      <!-- 🔹 種別 -->
      <div class="text-sm mb-2">
        <span v-if="product.auction_type === 'auction'">🏷️ オークション</span>
        <span v-else-if="product.auction_type === 'reverse'">💸 逆オークション</span>
        <span v-else>📦 通常販売</span>
      </div>


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
          <div v-if="product.room" class="text-sm text-black-500">
            📅 配信予定: {{ formatDate(product.room.start) }}
          </div>
          <div v-else class="text-sm text-black-500">
            📴 配信なし
          </div>
        </template>
      </div>
      <!-- 🛒 カート追加（ログインユーザーのみ） -->
      <form v-if="page.props.auth?.user" method="POST" action="/cart" class="mt-6">
        <input type="hidden" name="_token" :value="csrf" />
        <input type="hidden" name="product_id" :value="product.id" />

        <button
          type="button"
          :disabled="remainingStock <= 0"
          @click="handleAddToCart"
          class="font-bold py-2 px-6 rounded-full shadow transition"
          :class="remainingStock > 0 ? 'bg-green-500 text-black hover:bg-green-600' : 'bg-gray-400 text-black cursor-not-allowed'"
        >
          {{ remainingStock > 0 ? '🛒 カートに追加' : '売り切れ' }}
        </button>
      </form>

      <!-- 🔐 未ログイン時：ログイン誘導 -->
      <div v-else class="mt-6 text-black-300 text-sm">
        カートに追加するには
        <Link href="/login" class="underline hover:text-blue-400">ログイン</Link>
        が必要です。
      </div>

      <!-- 💡 配信販売申請（ログイン時のみ） -->
      <form v-if="page.props.auth?.user" @submit.prevent="applyForSale" class="mt-6">
        <div class="mb-2">
          <label for="room" class="block text-sm mb-1">配信ルームを選択</label>
          <select v-model="selectedRoomId" class="w-full rounded border px-2 py-1 text-black">
            <option disabled value="">選択してください</option>
            <option v-for="room in myRooms || []" :key="room.id" :value="room.id">
              {{ room.name }}（{{ formatDate(room.start) }}）
            </option>
          </select>
        </div>

        <button
          type="submit"
          class="bg-blue-500 text-black px-4 py-2 rounded hover:bg-blue-600"
          :disabled="!selectedRoomId"
        >
          💡 この配信で販売申請する
        </button>
      </form>

      <!-- 🔐 未ログイン時：ログイン誘導 -->
      <div v-else class="mt-6 text-black-300 text-sm">
        販売申請をするには
        <Link href="/login" class="underline hover:text-blue-400">ログイン</Link>
        が必要です。
      </div>








    </div>
  </AppLayout>
</template>
