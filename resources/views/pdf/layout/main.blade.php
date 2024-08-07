<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  @vite('resources/css/filament/admin/theme.css')
  <link href="https://fonts.bunny.net" rel="preconnect">
  <link href="https://fonts.bunny.net/css?family=abel:400|poppins:300,400" rel="stylesheet" />
  {{-- @filamentScripts --}}
  @yield('head-import')
  <title>{{ $title }}</title>
  {{-- @filamentStyles --}}
  <style>
    .font-poppins {
      font-family: 'Abel', sans-serif;
      font-family: 'Poppins', sans-serif;
    }
  </style>
  @yield('style')
</head>

<body class="font-poppins flex min-h-screen items-center justify-center bg-gray-100">
  <div class="w-full max-w-3xl min-h-max">
    @yield('content')
  </div>
  @stack('script')
</body>

</html>
