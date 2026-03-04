@extends('layouts.blade')

@section('content')
<div id="vue-navi" data-user='@json(auth()->user())'></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<video id="video" autoplay playsinline controls muted></video>

<div>
  @yield('buttons')
</div>

{{-- Vueチャット --}}
<div id="app">
  <chat-box
  {{--
    :friend="{{ json_encode($friend) }}"
      --}}
    :room="{{ json_encode($room) }}"
    :current-user="{{ json_encode(auth()->user()) }}"
  ></chat-box>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('js/kurento-utils.js') }}"></script>
  @stack('webrtc-script')
  @vite('resources/js/navi-mount.js')
  @vite('resources/js/chat-mount.js')
@endpush
