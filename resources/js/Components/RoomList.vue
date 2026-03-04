<template>
    <div id="room-list" class="p-4 space-y-2">
      <p class="font-bold">📋 現在のルーム一覧</p>
      <ul class="space-y-1 text-sm text-gray-700">
        <li v-if="rooms.length === 0" class="text-gray-400">ルームはありません</li>
        <li v-for="room in rooms" :key="room.room">
          <span class="font-mono">{{ room.room }}</span> -
          <span v-if="room.streaming" class="text-green-600 font-bold">📡 配信中</span>
          <a v-if="room.streaming" :href="`/viewer/${room.room}`" class="text-blue-600 underline ml-2">視聴する</a>
          <span v-else class="text-gray-400">📴 配信なし</span>
          <span class="ml-2 text-sm text-gray-500">👥 {{ room.viewers }}人視聴中</span>
        </li>
      </ul>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  
  const rooms = ref([]);
  
  const fetchRoomList = async () => {
    try {
      const res = await fetch('https://moon.timesfun.net:8443/status');
      rooms.value = await res.json();
    } catch (e) {
      console.error('❌ Room一覧取得失敗:', e);
    }
  };
  
  onMounted(() => {
    fetchRoomList();
    setInterval(fetchRoomList, 5000);
  });
  </script>
  