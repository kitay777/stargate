<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  setInterval(() => {
    window.location.reload()
  }, 5000)
})
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center text-center">

    <h1 class="text-2xl font-bold mb-6">
      通知を受け取るには友達追加が必要です
    </h1>

    <a
      href="https://line.me/R/ti/p/@082gctkp"
      target="_blank"
      class="bg-green-500 text-white px-6 py-3 rounded text-lg"
    >
      友達追加する
    </a>

    <p class="mt-6 text-gray-500">
      友達追加後、自動で画面が切り替わります。
    </p>

  </div>
</template>
