<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
defineProps({ presenters: Array });
</script>

<template>
  <AppLayout title="お気に入り配信者一覧">
    <div class="p-4 space-y-4">
      <h1 class="text-2xl font-bold text-white">⭐️ Likeした配信者</h1>

      <div v-if="presenters.length === 0" class="text-white-400">
        まだLikeした配信者はいません。
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="user in presenters" :key="user.id" class="bg-gray-800 rounded p-4 text-center">
          <img
            :src="user.profile_photo_url || '/images/default-profile.png'"
            class="w-16 h-16 rounded-full object-cover mx-auto mb-2"
          />
          <p class="text-white font-semibold">{{ user.name }}</p>
          <a :href="`/users/${user.id}`" class="text-blue-400 text-sm underline">プロフィールへ</a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
