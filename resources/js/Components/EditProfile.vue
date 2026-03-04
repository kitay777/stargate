<template>
  <div>
    <!-- 編集ボタン（一覧の中で使う） -->
    <button @click="openModal" class="text-blue-600 hover:underline text-sm">編集</button>

    <!-- モーダル本体 -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-auto">
      <div class="bg-white w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 rounded-xl shadow relative">
        <!-- 閉じるボタン -->
        <button @click="closeModal" class="absolute top-2 right-2 text-gray-600">&times;</button>

        <h2 class="text-xl font-bold mb-4">プロフィール編集</h2>

        <form @submit.prevent="submit" enctype="multipart/form-data">
          <!-- アバター画像（プレビュー付き） -->
          <div class="mb-4">
            <label class="block text-sm">プロフィール画像</label>
            <input type="file" accept="image/*" @change="onFileChange" />
            <img
              v-if="previewUrl"
              :src="previewUrl"
              class="w-32 h-32 mt-2 rounded-full object-cover border"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm">表示名（name）</label>
            <input v-model="form.name" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">姓（family name）</label>
            <input v-model="form.lastname" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">名（first name）</label>
            <input v-model="form.firstname" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">性別</label>
            <select v-model="form.sex" class="w-full border p-2 rounded">
              <option value="">選択してください</option>
              <option value="男性">男性</option>
              <option value="女性">女性</option>
              <option value="その他">その他</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm">都道府県（state）</label>
            <select v-model="form.state" class="w-full border p-2 rounded">
              <option value="">選択してください</option>
              <option v-for="pref in prefectures" :key="pref" :value="pref">{{ pref }}</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm">市区町村（city）</label>
            <input v-model="form.city" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">住所（address）</label>
            <input v-model="form.address" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">国（country）</label>
            <input v-model="form.country" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">郵便番号（zip）</label>
            <input v-model="form.zip" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">肩書き（title）</label>
            <input v-model="form.title" type="text" class="w-full border p-2 rounded" />
          </div>

          <div class="mb-4">
            <label class="block text-sm">自己紹介・メッセージ</label>
            <textarea v-model="form.message" class="w-full border p-2 rounded"></textarea>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">
              更新する
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  profile: Object,
})

const showModal = ref(false)
const previewUrl = ref(null)

const prefectures = [
  '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
  '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
  '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県',
  '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
  '鳥取県', '島根県', '岡山県', '広島県', '山口県',
  '徳島県', '香川県', '愛媛県', '高知県',
  '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
]

const form = useForm({
  name: props.user?.name || '',
  firstname: props.profile?.firstname || '',
  lastname: props.profile?.lastname || '',
  sex: props.profile?.sex || '',
  state: props.profile?.state || '',
  city: props.profile?.city || '',
  address: props.profile?.address || '',
  country: props.profile?.country || '',
  zip: props.profile?.zip || '',
  title: props.profile?.title || '',
  message: props.profile?.message || '',
  avatar: null,
})

const openModal = () => {
  showModal.value = true

  previewUrl.value = props.user.profile_photo_path
    ? `/storage/${props.user.profile_photo_path}`
    : null

  form.set({
    name: props.user?.name || '',
    firstname: props.profile?.firstname || '',
    lastname: props.profile?.lastname || '',
    sex: props.profile?.sex || '',
    state: props.profile?.state || '',
    city: props.profile?.city || '',
    address: props.profile?.address || '',
    country: props.profile?.country || '',
    zip: props.profile?.zip || '',
    title: props.profile?.title || '',
    message: props.profile?.message || '',
    avatar: null,
  })
}

const closeModal = () => {
  showModal.value = false
}

const onFileChange = (e) => {
  const file = e.target.files[0]
  form.avatar = file
  previewUrl.value = URL.createObjectURL(file)
}

const submit = () => {
  form.post(`/streamers/${props.user.id}/profile`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
    },
  })
}
</script>

<style scoped>
/* モーダル背景フェード */
</style>
