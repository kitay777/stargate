<template>
  <nav class="bg-white border-b border-gray-100 shadow-sm px-4 py-3 flex justify-between items-center">
    <div class="flex items-center gap-8">
      <a href="/dashboard">
        <img src="/logo.svg" alt="Logo" class="h-9 w-auto" />
      </a>
      <div class="hidden sm:flex gap-6">
        <a href="/dashboard" class="text-sm font-semibold text-gray-800 hover:text-blue-600">ダッシュボード</a>
        <a href="/user/profile" class="text-sm font-semibold text-gray-800 hover:text-blue-600">プロファイル</a>
        <a href="/presenter/1" class="text-sm font-semibold text-gray-800 hover:text-blue-600">Presenter</a>
      </div>
    </div>

    <!-- ▼ メニュー -->
    <div class="relative">
      <button @click="open = !open" class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div
        v-if="open"
        class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-10"
        @click.outside="open = false"
      >
        <button
          @click="logout"
          class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
        >
          Log Out
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'

// Props
const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

// ログアウト処理
const logout = () => {
  router.post('/logout')
}

// メニュー開閉用
const open = ref(false)

// 外側クリック検出（v-click-outsideがない場合用）
onMounted(() => {
  document.addEventListener('click', (e) => {
    const menu = document.querySelector('.relative')
    if (menu && !menu.contains(e.target)) {
      open.value = false
    }
  })
})
</script>
