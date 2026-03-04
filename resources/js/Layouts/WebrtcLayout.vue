<template>
  <div>
    <!-- ナビゲーションマウントポイント（旧: vue-navi）-->
    <div id="vue-navi" :data-user="JSON.stringify(auth.user)"></div>

    <!-- Kurento映像 -->
    <video id="video" autoplay playsinline controls muted></video>

    <!-- プレゼンターボタンエリア -->
    <div>
      <slot name="buttons" />
    </div>

    <!-- チャットエリア -->
    <chat-box :room="room" :current-user="auth.user" />

    <!-- スクリプト読み込み -->
    <!-- kurento-utils.js を外部読み込み（onMountedで） -->
  </div>
</template>

<script setup>
import ChatBox from '@/Components/ChatBox.vue'
import { onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const auth = page.props.auth
const room = page.props.room

onMounted(() => {
  const script = document.createElement('script')
  script.src = '/js/kurento-utils.js'
  script.async = true
  document.body.appendChild(script)

  // 旧bladeのように navi-mount, chat-mount も呼び出す
  import('@/navi-mount.js')
  import('@/chat-mount.js')
})
</script>
