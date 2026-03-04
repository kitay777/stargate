<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-bold">📋 現在のルーム一覧</h2>

    <ul v-if="rooms.length" class="space-y-2">
      <li v-for="room in rooms" :key="room.room" class="text-sm text-gray-700">
        <span class="font-mono">{{ room.room }}</span>
        <span v-if="room.streaming" class="text-green-600 font-bold ml-2">📡 配信中</span>
        <span v-else class="text-gray-400 ml-2">📴 配信なし</span>
        <span class="ml-2 text-sm text-gray-500">👥 {{ room.viewers }}人視聴中</span>
        <button v-if="room.streaming" @click="startViewer(room.room)" class="ml-4 underline text-blue-600">視聴</button>
      </li>
    </ul>
    <p v-else class="text-gray-400">ルームはありません</p>

    <video v-if="isViewing" id="video" autoplay playsinline muted class="w-full max-w-2xl border rounded"></video>
    <video v-show="isViewing" id="video" autoplay playsinline muted class="w-full max-w-2xl border rounded"></video>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { startViewer as viewerStart, stopViewer as viewerStop } from '@/webrtc/viewer-core';

const rooms = ref([]);
const isViewing = ref(false);
const selectedRoom = ref(null);
let isStarted = false;
let intervalId = null;

const fetchRoomList = async () => {
  try {
    const res = await fetch('https://moon.timesfun.jp:8443/status');
    rooms.value = await res.json();
  } catch (err) {
    console.error('❌ Room一覧取得失敗:', err);
  }
};

const startViewer = async (roomId) => {
  if (isStarted && selectedRoom.value === roomId) {
    console.log('✅ すでに視聴中');
    return;
  }

  const startViewer = (roomId) => {
  if (isStarted) return; // すでに開始済みなら何もしない
  isStarted = true;
  viewerStart(roomId);
  isViewing.value = true;
};
  stop(); // 既存セッションを止める

  selectedRoom.value = roomId;
  isStarted = true;
  isViewing.value = true;

  await nextTick(); // video要素がDOMにあることを保証

  const video = document.getElementById('video');
  if (!video) {
    console.error("❌ video element not found!");
    return;
  }

  viewerStart(roomId);
};

const stop = () => {
  viewerStop();
  isStarted = false;
  isViewing.value = false;
  selectedRoom.value = null;
};

onMounted(() => {
  fetchRoomList();
  intervalId = setInterval(fetchRoomList, 5000);
});

onBeforeUnmount(() => {
  stop();
  if (intervalId) clearInterval(intervalId);
});
</script>

<style scoped>
video {
  background-color: black;
}
</style>
