@extends('layouts.blade')

@section('content')
<!-- ✅ jQuery を最初に読み込む -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<video id="video" autoplay playsinline controls></video>

<div>
  <button id="presenter">Presenter</button>
  <button id="viewer">Viewer</button>
  <button id="stop">Stop</button>
</div>

<div id="vue-chat"
     data-friend='@json($friend)'
     data-current-user='@json(auth()->user())'>
</div>


{{-- ✅ Vueエリア --}}
<div id="app">
  <chat-box
    :friend="{{ json_encode($friend) }}"
    :current-user="{{ json_encode(auth()->user()) }}"
  ></chat-box>
</div>
@endsection

@push('scripts')
  {{-- ✅ jQuery はここにもいれて二重読み込みしてOK（保険） --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="{{ asset('js/kurento-utils.js') }}"></script>
  <script src="{{ asset('js/index.js') }}"></script>
  @vite('resources/js/chat-mount.js')
@endpush
