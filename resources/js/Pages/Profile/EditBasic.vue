<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'

const page = usePage()

const props = defineProps({
  user: Object,
  profile: Object
})

const form = useForm({
  name: props.user?.name ?? '',
  firstname: props.profile?.firstname ?? '',
  lastname: props.profile?.lastname ?? '',
  sex: props.profile?.sex ?? '',
  birthday: props.profile?.birthday ?? '',
  profile_photo: null,
})

function submit() {
  form.post(route('profile.basic.update'), {
    preserveScroll: true,
    onSuccess: () => {
      page.reload({ only: ['user', 'profile'] })
    }
  })
}

function handleFile(e) {
  form.profile_photo = e.target.files[0]
}

/* リアルタイム表示 */
const fullName = computed(() => {
  return `${form.lastname ?? ''} ${form.firstname ?? ''}`.trim()
})

const previewImage = computed(() => {
  if (form.profile_photo) {
    return URL.createObjectURL(form.profile_photo)
  }
  if (props.user?.profile_photo_path) {
    return `/storage/${props.user.profile_photo_path}`
  }
  return null
})
</script>

<template>
  <AppLayout title="基本情報編集">
    <div class="max-w-2xl mx-auto p-6">

      <h2 class="text-xl font-bold mb-6">基本情報</h2>

      <!-- 成功メッセージ -->
      <div v-if="page.props.flash.success"
           class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ page.props.flash.success }}
      </div>

      <!-- 現在表示（リアルタイム） -->
      <div class="mb-6 p-4 bg-gray-100 rounded">
        <h3 class="font-bold mb-2">現在の表示</h3>

        <div v-if="previewImage" class="mb-3">
          <img :src="previewImage"
               class="w-24 h-24 rounded-full object-cover border" />
        </div>

        <p><strong>表示名:</strong> {{ form.name }}</p>
        <p><strong>フルネーム:</strong> {{ fullName || '未設定' }}</p>
        <p><strong>性別:</strong> {{ form.sex || '未設定' }}</p>
        <p><strong>誕生日:</strong> {{ form.birthday || '未設定' }}</p>
      </div>

      <!-- フォーム -->
      <form @submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">

        <div>
          <label class="block mb-1 font-semibold">表示名 *</label>
          <input v-model="form.name"
                 type="text"
                 class="w-full border rounded px-3 py-2"
                 required />
          <div v-if="form.errors.name" class="text-red-500 text-sm">
            {{ form.errors.name }}
          </div>
        </div>

        <div>
          <label class="block mb-1 font-semibold">姓</label>
          <input v-model="form.firstname"
                 type="text"
                 class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block mb-1 font-semibold">名</label>
          <input v-model="form.lastname"
                 type="text"
                 class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block mb-1 font-semibold">性別</label>
          <select v-model="form.sex"
                  class="w-full border rounded px-3 py-2">
            <option value="">未設定</option>
            <option value="男性">男性</option>
            <option value="女性">女性</option>
            <option value="その他">その他</option>
          </select>
        </div>

        <div>
          <label class="block mb-1 font-semibold">生年月日</label>
          <input v-model="form.birthday"
                 type="date"
                 class="w-full border rounded px-3 py-2" />
        </div>

        <div>
          <label class="block mb-1 font-semibold">プロフィール画像</label>
          <input type="file"
                 @change="handleFile"
                 class="w-full" />
        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded shadow">
          保存
        </button>

      </form>
    </div>
  </AppLayout>
</template>