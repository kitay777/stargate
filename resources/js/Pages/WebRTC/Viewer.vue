<template>
  <AppLayout title="Viewer">
    <!-- 背景ビデオ -->
    <video
      id="video"
      ref="videoRef"
      autoplay
      playsinline
      muted
      class="fixed top-0 left-0 w-full h-full object-cover z-0"
    />
    <div v-if="!streamStarted" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white text-xl">
      🎬 配信者がまだ入室していません。しばらくお待ちください...
    </div>
    <div v-else>
      <button
      class="absolute right-4 z-30 px-4 py-2 bg-black bg-opacity-50 text-white rounded"
      @click="forceRedraw"
      style="top: 110px;"
      >
      🔄 再描画
    </button>
    </div>
    <!-- オーバーレイ：通常コンテンツ -->
    <div class="absolute top-15 left-0 z-20 p-4 text-white text-lg font-bold">
      🎥 配信視聴中: {{ room.name }}
      <div class="text-sm text-gray-200 mt-1">👤 配信者: {{ room.user.name }}</div>
    </div>
    <!-- 音声ON/OFFボタン -->
    <button
      class="absolute top-19 right-4 z-30 px-4 py-2 bg-black bg-opacity-50 text-white rounded"
      @click="toggleMute"
    >
      {{ isMuted ? '🔇 ミュート解除' : '🔊 ミュート' }}
    </button>


    <!-- 🎯 チャットを画面下から120px上に固定 -->
    <div class="absolute bottom-[50px] left-0 w-full p-4 overflow-y-auto h-1/2 bg-transparent z-10">
      <RoomChat :room="room" :currentUser="user" />
    </div>
  </AppLayout>
</template>




<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import RoomChat from '@/Components/RoomChat.vue'
import { watch } from 'vue'


// Props from Inertia
const page = usePage()
const room = page.props.room
const user = page.props.authUser

const videoRef = ref(null)
const containerHeight = ref('100vh') // 初期値
const isMuted = ref(true) // 初期状態はミュート（スマホ自動再生のため）
const streamStarted = ref(false)
const redrawKey = ref(0)


function forceRedraw() {
  if (videoRef.value) {
    console.log('🔁 手動で映像再描画')

    // transform ハックで再描画
    videoRef.value.style.transform = 'scale(1.0001)'

    window.location.reload()
  }
}

function toggleMute() {
  isMuted.value = !isMuted.value
  if (videoRef.value) {
    videoRef.value.muted = isMuted.value
  }
}

function updateContainerHeight() {
  const viewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight
  const headerFooterOffset = 120 // AppLayout の header + footer 合計
  containerHeight.value = `${viewportHeight - headerFooterOffset}px`
}

/*
  onMounted(() => {
    console.log('🚀 Viewer mounted. ROOM:', room.id)

    import('@/webrtc/viewer-core').then(({ startViewerDynamic, setStreamEndedCallback }) => {
    if (typeof startViewerDynamic === 'function') {
      startViewerDynamic(room.id, 'video-container-id') // ← IDは正しく指定
    }

    if (typeof setStreamEndedCallback === 'function') {
      setStreamEndedCallback(room.id, () => {
        alert('📡 配信が終了しました')
        router.visit('/dashboard')
      })
    }
  })

  // 🎧 最初のミュート状態を反映
  if (videoRef.value) {
    videoRef.value.muted = isMuted.value
  }

  // WebRTC Viewer start
  import('@/webrtc/viewer-core').then(({ startViewer }) => {
    if (typeof startViewer === 'function') {
      startViewer(room.id, videoRef.value)
    }
  })

  updateContainerHeight()
  window.addEventListener('resize', updateContainerHeight)
})
*/


watch(streamStarted, (started) => {
  if (started && !hasReloaded) {
    hasReloaded = true
    console.log('🔄 streamStarted → 自動リロード')

    setTimeout(() => {
      window.location.reload()
    }, 100) // 少しだけ遅延を入れるとより自然
  }
})

onMounted(() => {
  console.log('🚀 Viewer mounted. ROOM:', room.id)
  startViewer(room.id, videoRef.value, (stream) => {
    console.log('🔁 明示的に video.srcObject を再セット')
    videoRef.value.srcObject = stream
    videoRef.value.play().catch(() => {})
    streamStarted.value = true
  })

  fetch(`/rooms/${room.id}/streaming-status`)
      .then(res => res.json())
      .then(data => {
        streamStarted.value = data.streaming // ← ✅ ここで切り替え
      })

  setInterval(() => {
    fetch(`/rooms/${room.id}/streaming-status`)
      .then(res => res.json())
      .then(data => {
        streamStarted.value = data.streaming // ← ✅ ここで切り替え
      })
  }, 5000)
if (videoRef.value) {
  videoRef.value.muted = isMuted.value

  videoRef.value.addEventListener('loadeddata', () => {
    console.log('🎬 loadeddata fired — calling play()')
    videoRef.value.play().catch(err => {
      console.warn('⚠️ video play() failed:', err)
    })
  })
}


  import('@/webrtc/viewer-core').then(({ 
    startViewerDynamic, 
    startViewer, 
    setStreamStartedCallback, 
    setStreamEndedCallback 
  }) => {
    if (typeof startViewerDynamic === 'function') {
      startViewerDynamic(room.id, 'video-container-id')
    }

    if (typeof startViewer === 'function' && videoRef.value) {
      startViewer(room.id, videoRef.value)
    }

    if (typeof setStreamStartedCallback === 'function') {
      setStreamStartedCallback(room.id, () => {
        console.log('🎥 Stream started for room', room.id)
        streamStarted.value = true
      })
    }

    if (typeof setStreamEndedCallback === 'function') {
      setStreamEndedCallback(room.id, () => {
        alert('📡 配信が終了しました')
        router.visit('/dashboard')
      })
    }
  })

  if (videoRef.value) {
    videoRef.value.muted = isMuted.value
  }

  updateContainerHeight()
  window.addEventListener('resize', updateContainerHeight)
}) // ← ✅ このカッコが必要！これがないとエラーになる

onBeforeUnmount(() => {
  import('@/webrtc/viewer-core').then(({ stopViewer }) => {
    if (typeof stopViewer === 'function') {
      stopViewer()
      console.log('🛑 Viewer stopped')
    }
  })

  window.removeEventListener('resize', updateContainerHeight)
})
</script>
