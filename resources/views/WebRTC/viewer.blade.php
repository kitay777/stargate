@extends('webrtc.layout')

@section('buttons')

<div id="room-list" class="p-4 space-y-2">
  <p class="font-bold">📋 現在のルーム一覧</p>
  <ul id="room-list-items" class="space-y-1 text-sm text-gray-700">
    <li>読み込み中...</li>
  </ul>
</div>

<div id="viewer-status" data-room-id="{{ $room->id }}"></div>

<div id="vue-chat"
  data-room='{{ json_encode($room) }}'
  data-current-user='@json(auth()->user())'>
</div>
{{-- ✅ Vueエリア --}}
<div id="app">
<chat-box
  :room="{{ json_encode($room) }}"
  :current-user="{{ json_encode(auth()->user()) }}"
></chat-box>
</div>
@endsection
@vite('resources/js/viewer-mount.js')

@push('webrtc-script')
  <script>
    
  </script>
@endpush
<script>
  async function fetchRoomList() {
    try {
      const res = await fetch('https://moon.timesfun.net:8443/status');
      const rooms = await res.json();

      const list = document.getElementById('room-list-items');
      list.innerHTML = ''; // 一旦空にする

      if (rooms.length === 0) {
        list.innerHTML = '<li class="text-gray-400">ルームはありません</li>';
        return;
      }

      for (const room of rooms) {
        const li = document.createElement('li');
        const res = await fetch(`/room-name/${room.room}`);
        const data = await res.json();
        const roomname = data.name ?? '不明なルーム';
        console.log('🚀 ルーム名:', roomname);
        li.innerHTML = `
          <span class="font-mono">${roomname}</span> -
          ${room.streaming
            ? `<span class="text-green-600 font-bold">📡 配信中</span>
              <a href="/viewer/${room.room}" class="text-blue-600 underline ml-2">視聴する</a>`
            : '<span class="text-gray-400">📴 配信なし</span>'
          }
          <span class="ml-2 text-sm text-gray-500">👥 ${room.viewers}人視聴中</span>
        `;
        list.appendChild(li);
      }
    } catch (e) {
      console.error('❌ Room一覧取得失敗:', e);
    }
  }

  fetchRoomList();
  setInterval(fetchRoomList, 5000); // 5秒ごとに更新
</script>

@push('webrtc-script')
  <script>
  </script>
@endpush

@push('scripts')
<script type="module">
    const ROOM = '{{ $room->id }}';
    document.addEventListener('DOMContentLoaded', () => {
      console.log('🚀 DOM Ready');

      // 確認ログ
      console.log('✅ ROOM:', typeof ROOM !== 'undefined' ? ROOM : 'undefined');
      console.log('✅ startViewer is', typeof startViewer);

      if (typeof startViewer === 'function' && typeof ROOM !== 'undefined') {
        const video = document.getElementById('video');
        if (video) {
          startViewer(ROOM);
          console.log('🎥 Viewer started for ROOM:', ROOM);
        } else {
          console.warn('❌ video element not found');
        }
      }
    });

    window.addEventListener('beforeunload', () => {
      if (typeof stopViewer === 'function') {
        stopViewer();
        console.log('🛑 Viewer stopped');
      }
    });
  </script>
@endpush







