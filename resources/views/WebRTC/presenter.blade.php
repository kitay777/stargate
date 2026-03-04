@extends('webrtc.layout')

@section('buttons')
  <button id="start">📡 配信開始</button>
  <button id="stop">⛔ 配信停止</button>
  {{-- ✅ Vueエリア --}}
  <div id="vue-chat"
    data-room='{{ json_encode($room) }}'
    data-current-user='@json(auth()->user())'>
  </div>
<div id="app">
  <chat-box
    :room="{{ json_encode($room) }}"
    :current-user="{{ json_encode(auth()->user()) }}"
  ></chat-box>
</div>
@endsection

@push('webrtc-script')
  <script>
    const ROOM = "{{ $room->id }}";
  </script>
  <script src="{{ asset('js/presenter.js') }}"></script>
@endpush
