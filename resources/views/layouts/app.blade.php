@extends('layouts.blade')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<video id="video" autoplay playsinline controls></video>

<div>
  @yield('buttons')
</div>

<div id="vue-chat"
     data-room='{{$room}}'
     data-current-user='@json(auth()->user())'>
</div>

<div id="app">
  <chat-box
    :room="{{ $room }}"
    :current-user="{{ json_encode(auth()->user()) }}"
  ></chat-box>
</div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="{{ asset('js/kurento-utils.js') }}"></script>
  @stack('webrtc-script')
  @vite('resources/js/chat-mount.js')
@endpush
