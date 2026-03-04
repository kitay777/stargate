<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>NeoriaFun</title>
  <script src="/js/kurento-utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/selfie_segmentation.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  @vite(['resources/js/app.js'])  ←この順番重要！

</head>
<body style="width:100vw; height:100vh;margin:0; padding:0;border;5px solid #f00">
  @yield('content')
  @stack('scripts')
</body>
</html>
