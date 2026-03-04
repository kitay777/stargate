<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  video: null,
})

const handleUpload = () => {
  form.post(route('videos.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      alert('動画をアップロードしました')
    },
  })
}
</script>

<template>
  <AppLayout title="動画アップロード">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">動画アップロード</h2>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="handleUpload" class="space-y-6">
            <div>
              <input
                type="file"
                accept="video/*"
                @change="form.video = $event.target.files[0]"
                class="block w-full border-gray-300 shadow-sm rounded-md"
                required
              />
              <div v-if="form.errors.video" class="text-red-500 text-sm mt-1">
                {{ form.errors.video }}
              </div>
            </div>

            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              :disabled="form.processing"
            >
              アップロード
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
