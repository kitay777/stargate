@extends('webrtc.app')

{{-- head などで --}}
<script src="{{ asset('js/viewer-core.js') }}"></script>

@push('scripts')

<script>
  window.startRoom = function(roomId) {
    startViewerDynamic(roomId, 'video-container');
  };

  async function fetchStreamingRooms() {
    try {
      const res = await fetch('https://moon.timesfun.net:8443/status');
      const data = await res.json();

      const roomList = document.getElementById('room-list');
      roomList.innerHTML = '';

      const onlineRooms = data.filter(room => room.streaming);
      if (onlineRooms.length === 0) {
        roomList.innerHTML = '<p class="text-gray-400">📴 現在オンラインの配信はありません</p>';
        return;
      }

      for (const room of onlineRooms) {
        const div = document.createElement('div');
        div.innerHTML = `
          <span class="font-mono text-sm">${room.room}</span>
          <span class="ml-2 text-green-600 font-bold">📡 配信中</span>
          <button class="ml-4 px-3 py-1 bg-blue-600 text-white rounded text-sm" onclick="startRoom('${room.room}')">視聴する</button>
        `;
        roomList.appendChild(div);
      }
    } catch (e) {
      console.error("❌ ルーム一覧取得失敗", e);
    }
  }

  fetchStreamingRooms();
  setInterval(fetchStreamingRooms, 10000); // 10秒おきに更新
</script>
@endpush

@section('content')
<div class="p-4">
  <h2 class="text-lg font-bold mb-4">🎥 オンラインの配信一覧</h2>

  <div id="room-list" class="space-y-2 mb-6"></div>

  <div id="video-container" class="grid grid-cols-2 gap-4"></div>
</div>
@endsection


