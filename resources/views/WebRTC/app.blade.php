@extends('layouts.blade')

@section('content')
<div id="vue-navi" data-user='@json(auth()->user())'></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<div>
  @yield('buttons')
</div>
@endsection

@push('scripts')
  <script src="{{ asset('js/kurento-utils.js') }}"></script>
  @stack('webrtc-script')
  @vite('resources/js/navi-mount.js')
  @vite('resources/js/chat-mount.js')
@endpush
