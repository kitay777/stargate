<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import { usePage, router } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount } from 'vue'


let intervalId = null;

onMounted(() => {
  intervalId = setInterval(() => {
    router.reload({ preserveScroll: true });
  }, 5000); // 5秒ごとにページをサクッとリロードするだけ
});

onBeforeUnmount(() => {
  clearInterval(intervalId);
});




const page = usePage()

const currentUserId = page.props.auth?.user?.id ?? null

const showForm = ref(false)

const props = defineProps({
  products: Array,
  categories: Array, 
  rooms: Array,
})

const form = useForm({
  name: '',
  description: '',
  price: '',
  stock: 0,
  auction_type: 'none',
  min_price: null,
  start_at: '',  // ✅ 追加
  end_at: '',    // ✅ 追加
  category_id: '',  // ✅ カテゴリー追加
  shipping_type: 'included', // ✅ 送料タイプ（デフォルト: 送料込み）
  shipping_fee: null, 
  room_id: null,
})

const editingId = ref(null)
const editingForm = useForm({
  id: null,
  name: '',
  description: '',
  price: '',
  stock: 0,
  auction_type: 'none',
  min_price: null,
  start_at: '',
  end_at: '',
  category_id: '',
  shipping_type: 'included',
  shipping_fee: null,
  room_id: null,
})


const startEdit = (product) => {
  editingId.value = product.id
  editingForm.id = product.id 
  editingForm.name = product.name
  editingForm.description = product.description
  editingForm.price = product.price
  editingForm.stock = product.stock
  editingForm.auction_type = product.auction_type || 'none'
  editingForm.min_price = product.min_price || null
  editingForm.start_at = product.start_at || ''
  editingForm.end_at = product.end_at || ''
  editingForm.category_id = product.category_id || ''
  editingForm.shipping_type = product.shipping_type || 'included'
  editingForm.shipping_fee = product.shipping_fee || 0
}


// 新規登録 submit
const handleSubmit = () => {
  if (!validateAuction(form)) return
  form.post('/products', {
    onSuccess: () => {
      form.reset()     // 入力内容リセット
      showForm.value = false // ✅ フォームを閉じる
    }
  })
}


// 編集保存 submit
const handleEditSubmit = (productId) => {
  if (!validateAuction(editingForm)) return
  editingForm.put(`/products/${productId}`, {
    onSuccess: () => {
      editingForm.reset()
      editingId.value = null
    }
  })
}


// 共通バリデーション関数
const validateAuction = (targetForm) => {
  if (targetForm.auction_type === 'auction' && targetForm.min_price < targetForm.price) {
    alert('最低金額は開始価格以上である必要があります。')
    return false
  }
  if (targetForm.auction_type === 'reverse' && targetForm.min_price > targetForm.price) {
    alert('最低金額は上限価格以下である必要があります。')
    return false
  }
  return true
}

const uploadImages = (e, productId) => {
  const files = e.target.files
  if (!files.length) return

  const formData = new FormData()
  for (const file of files) {
    formData.append('images[]', file)
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

  fetch(`/products/${productId}/images`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
    },
    body: formData,
    credentials: 'include',
  })
    .then(async (res) => {
      const content = await res.text()
      console.log('📡 ステータス:', res.status)
      console.log('📦 内容:', content)
      if (res.ok) {
        location.reload()
      } else {
        alert('アップロード失敗: ' + content)
      }
    })
    .catch((err) => {
      console.error('❌ fetchエラー:', err)
      alert('アップロード通信エラー')
    })
}

const deleteImage = (imageId) => {
  if (!confirm('本当に削除しますか？')) return
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

  fetch(`/products/images/${imageId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': csrfToken,
    },
    credentials: 'include',
  })
    .then(() => location.reload())
    .catch((err) => console.error('削除失敗:', err))
}
const handleRoomSelection = () => {
  const selectedRoom = props.rooms.find(r => r.id === form.room_id)
  if (selectedRoom) {
    form.start_at = selectedRoom.start
  }
}
const handleRoomSelectionEdit = () => {
  const selectedRoom = props.rooms.find(r => r.id === editingForm.room_id)
  if (selectedRoom) {
    editingForm.start_at = selectedRoom.start
  }
}


// 金額変更用関数
function updatePrice(productId, delta) {
  router.post(`/products/${productId}/update-price`, {
    delta: delta,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ 金額変更成功！');
      router.reload({ preserveScroll: true }); // 🎯 Inertia軽量リロード！
    },
    onError: () => {
      console.error('❌ 金額変更失敗');
    }
  })
}


</script>

<template>

  <AppLayout title="Dashboard">
    <Head title="商品管理" />
    <div class="max-w-4xl mx-auto p-4">
      <h1 class="text-2xl mb-4 font-bold">商品管理...</h1>

      <div class="mb-4">
        <button
          @click="showForm = !showForm"
          class="bg-blue-500 text-white px-4 py-2 rounded"
        >
          {{ showForm ? '▲ 閉じる' : '➕ 新規商品を登録' }}
        </button>
      </div>
      
      <!-- 新規商品登録 -->
      <Transition name="fade">
        <form 
          v-if="showForm"
          @submit.prevent="handleSubmit" class="mb-8 space-y-2 text-white">

          <input v-model="form.name" type="text" placeholder="商品名" class="w-full p-2 rounded bg-gray-100" />
          <textarea v-model="form.description" rows="3" class="w-full p-2 rounded bg-gray-100" placeholder="説明"></textarea>
          <input v-model="form.price" type="number" placeholder="開始価格 / 上限価格" class="w-full p-2 rounded bg-gray-100" />
          <input v-model="form.stock" type="number" placeholder="在庫" class="w-full p-2 rounded bg-gray-100" />

          <select v-model="form.auction_type" class="w-full p-2 rounded bg-gray-100">
            <option value="none">通常販売</option>
            <option value="auction">オークション</option>
            <option value="reverse">逆オークション</option>
          </select>

          <div v-if="form.auction_type !== 'none'">
            <label class="text-sm text-gray-700 mt-1">
              最低金額（落札条件）
            </label>
            <input v-model.number="form.min_price" type="number" class="w-full p-2 rounded bg-gray-100" />
          </div>
            <label class="block text-sm text-gray-700 mt-1">販売開始日時</label>
            <input v-model="form.start_at" type="datetime-local" class="w-full p-2 rounded bg-gray-100" />

            <label class="block text-sm text-gray-700 mt-1">販売終了日時</label>
            <input v-model="form.end_at" type="datetime-local" class="w-full p-2 rounded bg-gray-100" />
            <!-- 🔽 カテゴリー選択 -->
            <label class="block text-sm text-gray-700 mt-1">カテゴリー</label>
            <select v-model="form.category_id" class="w-full p-2 rounded bg-gray-100">
              <option value="">選択してください</option>
              <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>

            <!-- 🚚 送料の扱い -->
            <label class="block text-sm text-gray-700 mt-1">送料</label>
            <select v-model="form.shipping_type" class="w-full p-2 rounded bg-gray-100">
              <option value="included">送料込み</option>
              <option value="cod">着払い</option>
            </select>
            <!-- 💰 送料金額（着払い時のみ表示） -->
            <div v-if="form.shipping_type === 'cod'">
              <label class="block text-sm text-gray-700 mt-1">送料金額（円）</label>
              <input
                v-model.number="form.shipping_fee"
                type="number"
                min="0"
                class="w-full p-2 rounded bg-gray-100"
                placeholder="例: 500"
              />
            </div>
            <!-- 📺 配信ルーム選択 -->
            <label class="block text-sm text-gray-700 mt-1">ライブ配信ルーム</label>
            <select
              v-model="form.room_id"
              @change="handleRoomSelection"
              class="w-full p-2 rounded bg-gray-100"
            >
              <option value="">選択してください</option>
              <option v-for="room in props.rooms" :key="room.id" :value="room.id">
                {{ room.name }}（{{ room.start }}）
              </option>
            </select>

          <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">➕ 登録</button>
        </form>
      </Transition>
      <!-- 商品一覧 -->
      <div v-for="product in props.products" :key="product.id" class="mb-6 p-4 bg-white text-white shadow rounded">
        <div v-if="editingId === product.id">
          <input v-model="editingForm.name" class="w-full p-1 border rounded mb-1 text-white" />
          <textarea v-model="editingForm.description" rows="3" class="w-full p-1 border rounded mb-1 text-white"></textarea>
          <input v-model="editingForm.price" type="number" class="w-full p-1 border rounded mb-1 text-white" placeholder="開始価格 / 上限価格" />
          <input v-model="editingForm.stock" type="number" class="w-full p-1 border rounded mb-1 text-white" />

          <select v-model="editingForm.auction_type" class="w-full p-1 border rounded mb-1 text-white">
            <option value="none">通常販売</option>
            <option value="auction">オークション</option>
            <option value="reverse">逆オークション</option>
          </select>

          <div v-if="editingForm.auction_type !== 'none'">
            <label class="text-sm text-gray-700 mt-1 text-white">
              最低金額（落札条件）
            </label>
            <input v-model.number="editingForm.min_price" type="number" class="w-full p-1 border rounded mb-1" />
          </div>

          <label class="block text-sm text-gray-700 mt-1 text-white">販売開始日時</label>
          <input v-model="editingForm.start_at" type="datetime-local" class="w-full p-1 border rounded mb-1" />

          <label class="block text-sm text-gray-700 mt-1 text-white">販売終了日時</label>
          <input v-model="editingForm.end_at" type="datetime-local" class="w-full p-1 border rounded mb-1" />

          <!-- カテゴリー選択 -->
          <label class="text-sm text-gray-700 mt-1">カテゴリー</label>
          <select v-model="editingForm.category_id" class="w-full p-1 border rounded mb-1 text-white">
            <option value="">選択してください</option>
            <option v-for="cat in props.categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>

          <!-- 送料の扱い -->
          <label class="text-sm text-gray-700 mt-1">送料</label>
          <select v-model="editingForm.shipping_type" class="w-full p-1 border rounded mb-1 text-white">
            <option value="included">送料込み</option>
            <option value="cod">着払い</option>
          </select>

          <!-- 送料金額（着払いのときだけ） -->
          <div v-if="editingForm.shipping_type === 'cod'">
            <label class="text-sm text-gray-700 mt-1">送料金額</label>
            <input
              v-model.number="editingForm.shipping_fee"
              type="number"
              class="w-full p-1 border rounded mb-1 text-white"
            />
          </div>
            <!-- 📺 配信ルーム選択 -->
            <label class="block text-sm text-gray-700 mt-1">ライブ配信ルーム</label>
            <select
              v-model="editingForm.room_id"
              @change="handleRoomSelectionEdit"
              class="w-full p-1 border rounded mb-1 text-white"
            >
              <option value="">選択してください</option>
              <option v-for="room in props.rooms" :key="room.id" :value="room.id">
                {{ room.name }}（{{ room.start }}）
              </option>
            </select>



          <div class="flex gap-2 mt-2">
            <button @click="handleEditSubmit(product.id)" class="bg-blue-500 text-white px-3 py-1 rounded">保存</button>
            <button @click="editingId = null" class="bg-gray-300 px-3 py-1 rounded">キャンセル</button>
          </div>
        </div>

        <div v-else>
          <div class="text-lg font-semibold">🛒 {{ product.name }}</div>
          <p class="text-sm whitespace-pre-line text-white mt-1">{{ product.description }}</p>
          <p class="text-sm mt-1">💰 {{ product.price }} 円 / 在庫: {{ product.stock }}</p>
                      <!-- ✅ 値付けボタンエリア（販売担当者だけ） -->
              <div v-if="product.sellers?.some(seller => seller.id === currentUserId)" class="mt-4 flex flex-wrap gap-2">

                
              <!-- オークション種別によってボタン内容を変える -->
              <template v-if="product.auction_type === 'auction'">
                <!-- オークション（値上げのみ） -->
                <button
                  class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click.prevent="updatePrice(product.id, 100)"
                >+100円</button>
                <button
                  class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click.prevent="updatePrice(product.id, 500)"
                >+500円</button>
                <button
                  class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click.prevent="updatePrice(product.id, 1000)"
                >+1000円</button>
              </template>

              <template v-else-if="product.auction_type === 'reverse'">
                <!-- 逆オークション（値下げのみ） -->
                <button
                  class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click.prevent="updatePrice(product.id, -100)"
                >-100円</button>
                <button
                  class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click.prevent="updatePrice(product.id, -500)"
                >-500円</button>
                <button
                  class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click.prevent="updatePrice(product.id, -1000)"
                >-1000円</button>
              </template>
            </div>

          <!-- オークション・逆オークション表示 -->
          <div v-if="product.auction_type === 'auction'" class="text-sm text-blue-700 mt-1">
            🏷️ オークション<br>
            ▶️ スタート価格: {{ product.price }} 円<br>
            📉 最低落札価格: {{ product.min_price }} 円以上
          </div>
          <div v-else-if="product.auction_type === 'reverse'" class="text-sm text-green-700 mt-1">
            💸 逆オークション<br>
            ⬆️ 上限価格: {{ product.price }} 円<br>
            ⬇️ 最低価格: {{ product.min_price }} 円まで下がる可能性あり
          </div>

          <p class="text-sm mt-1 text-gray-600" v-if="product.start_at && product.end_at">
            🕒 販売期間：{{ product.start_at }} 〜 {{ product.end_at }}
          </p>
          <!-- カテゴリー表示 -->
          <p v-if="product.category" class="text-sm mt-1 text-gray-700">
            📂 カテゴリー: {{ product.category.name }}
          </p>

          <!-- 送料表示 -->
          <p class="text-sm mt-1 text-gray-700">
            🚚 送料: 
            <span v-if="product.shipping_type === 'included'">送料込み</span>
            <span v-else>着払い（{{ product.shipping_fee }} 円）</span>
          </p>
          <p v-if="product.room" class="text-sm mt-1 text-gray-700">
            📺 配信ルーム: {{ product.room.name }}（{{ product.room.start }}）
          </p>

          <!-- 画像一覧 -->
          <div v-if="product.images?.length" class="flex gap-2 mt-2 flex-wrap">
            <div v-for="img in product.images" :key="img.id" class="relative">
              <img :src="img.path" class="w-24 h-24 object-cover border rounded" />
              <button @click="deleteImage(img.id)" class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 rounded">✕</button>
            </div>
          </div>

          <!-- 画像アップロード -->
<!-- 画像アップロード -->
<div class="flex flex-col items-start">
  <!-- 非表示のinput -->
  <input
    :id="`cameraInput-${product.id}`"
    type="file"
    accept="image/*"
    capture="environment"
    multiple
    class="hidden"
    @change="e => uploadImages(e, product.id)"
  />

  <!-- 見た目のボタン -->
  <label
    :for="`cameraInput-${product.id}`"
    class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded cursor-pointer"
  >
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
      xmlns="http://www.w3.org/2000/svg">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M3 7h2l2-3h10l2 3h2a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z">
      </path>
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a3 3 0 100 6 3 3 0 000-6z"></path>
    </svg>
    📷 写真を撮る
  </label>
</div>



          <!-- 操作 -->
          <div class="mt-2 flex gap-2">
            <button @click="startEdit(product)" class="bg-yellow-400 px-3 py-1 rounded">編集</button>
            <button @click="() => $inertia.delete(`/products/${product.id}`)" class="bg-red-500 text-white px-3 py-1 rounded">削除</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
input, textarea {
  border: 1px solid #ccc;
}
</style>
<style scoped>
input, textarea {
  border: 1px solid #ccc;
}

/* アニメーション用クラス */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
