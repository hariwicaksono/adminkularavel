<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $settings['site_name'] ?? 'My App' }}</title>
  <link rel="icon" href="{{ $settings['site_logo'] }}" type="image/x-icon">
  @vite('resources/js/app.js')
</head>

<body>
  <div id="app"></div>
</body>

</html>