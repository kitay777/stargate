<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { startViewer, stopViewer } from '@/webrtc/viewer-core.js'


const isStreaming = ref(false)

const handleStart = () => {
    console.log('handleStart')
  startViewer(props.roomId)
}

const props = defineProps({
  roomId: String
})

// ✅ STOP ハンドラー追加
const handleStop = () => {
    console.log('handleStop')
  stopViewer()
}

onMounted(() => {
  checkStreamStatus()
  setInterval(checkStreamStatus, 5000)
})

function checkStreamStatus() {
  axios.get(`https://moon.timesfun.net:8443/status/${props.roomId}`)
    .then(res => {
      isStreaming.value = res.data.streaming
      console.warn('配信状態:', res.data.streaming)

    })
    .catch(err => {
      console.warn('配信状態取得失敗:', err)
    })
}
</script>

<template>
  <div class="p-4">
    <div v-if="isStreaming" class="text-green-600 font-bold">📡 配信中</div>
    <div v-else class="text-gray-500 font-semibold">📴 配信していません</div>
<!--
    <div v-if="isStreaming" class="mt-4 space-x-2">
      <button @click="handleStart" class="bg-blue-500 text-white px-4 py-2 rounded">📥 視聴開始</button>
      <button @click="handleStop" class="bg-red-500 text-white px-4 py-2 rounded">⛔ 停止</button>
    </div>
  -->
  </div>
</template>
