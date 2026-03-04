import './bootstrap'; // ← これが Echo を定義してる
import '../css/app.css';

import { createApp, h } from 'vue'
import RoomChat from './Components/RoomChat.vue'

const el = document.getElementById('vue-chat')

if (el) {
  const room = JSON.parse(el.dataset.room);
  const currentUser = JSON.parse(el.dataset.currentUser);
  const roomId = room.id;

  createApp({
    render: () =>
      h(RoomChat, {
        room,
        currentUser
      }),
  }).mount('#vue-chat');
}
