<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Neoria</title>
  @vite('resources/js/app.js') {{-- Vite読み込み（Vue用） --}}
  @vite('resources/css/app.css')
</head>
<body>
  <main>
    @yield('content')
  </main>

  {{-- ✅ ここが正解！ --}}
  @stack('scripts')
</body>
</html>

