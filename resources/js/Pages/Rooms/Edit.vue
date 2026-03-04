<template>
  <AppLayout title="ルーム作成">
    <template #default>
      <h1 class="text-2xl font-bold mb-4">ルーム作成</h1>

      <form @submit.prevent="submit" class="space-y-4 max-w-2xl">
        <!-- 部屋名 -->
        <div>
          <label class="block font-medium text-sm text-white-700">ルーム名</label>
          <input v-model="form.name" name="name" type="text" class="input text-black" />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- 説明 -->
        <div>
          <label class="block font-medium text-sm text-white-700">説明</label>
          <textarea v-model="form.description" name="description" class="input text-black" maxlength="1024" rows="4"></textarea>
          <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
        </div>

        <!-- スタート時間 -->
        <div>
          <label class="block font-medium text-sm text-white-700">開始時刻</label>
          <input v-model="form.start" name="start" type="datetime-local" class="input text-black" />
          <div v-if="form.errors.start" class="text-red-500 text-sm mt-1">{{ form.errors.start }}</div>
        </div>

        <!-- 終了予定時間 -->
        <div>
          <label class="block font-medium text-sm text-white-700">終了予定時刻</label>
          <input v-model="form.end" name="end" type="datetime-local" class="input text-black" />
          <div v-if="form.errors.end" class="text-red-500 text-sm mt-1">{{ form.errors.end }}</div>
        </div>

        <!-- カテゴリー選択 -->
        <div>
          <label class="block font-medium text-sm text-white-700">カテゴリー</label>
          <select v-model="form.category_id" class="input text-black" name="category_id">
            <option value="" disabled>選択してください</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
        </div>
        <!-- 画像アップロード -->
        <div class="mb-4">
          <label class="block text-white mb-1">ルーム画像 (1240x744 推奨)</label>
          <input type="file" @change="handleImageUpload" name="image" accept="image/*" />
        </div>
        <!-- 作成ボタン -->
        <div>
          <button type="submit" class="btn-primary">作成する</button>
        </div>
      </form>
    </template>
  </AppLayout>
</template>


// Pages/Rooms/Edit.vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3' 
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const imageFile = ref(null)

const props = defineProps({
  room: Object,
  categories: Array,
});

// 初期値に room の値をセット
/*
const form = useForm({
  name: props.room.name,
  description: props.room.description,
  start: props.room.start,
  end: props.room.end,
  category_id: props.room.category_id,
  image: null,
});
*/

const form = useForm({
  name: props.room.name,
  description: props.room.description,
  start: props.room.start?.slice(0, 16), // ← "2025-04-09T21:35"
  end: props.room.end?.slice(0, 16),
  category_id: props.room.category_id,
  image: null,
})


// PUTで更新
const submit = () => {
  const data = new FormData()

  data.append('name', form.name)
  data.append('description', form.description)
  data.append('start', form.start)
  data.append('end', form.end)
  data.append('category_id', form.category_id)
  if (imageFile.value) {
    data.append('image', imageFile.value)
  }


  // 👇 Laravel用の "_method" で PUT を指定！
  data.append('_method', 'PUT')

  router.post(route('rooms.update', props.room.id), data, {
    onSuccess: () => {
      router.visit('/dashboard')
    },
    onError: (errors) => {
      console.warn('バリデーションエラー:', errors)
    }
  })
}

function handleImageUpload(event) {
  imageFile.value = event.target.files[0]
  console.log('📷 アップロード画像:', imageFile.value)
}




</script>

<style scoped>
.input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
}
</style>
