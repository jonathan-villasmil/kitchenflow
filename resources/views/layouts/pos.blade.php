<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KitchenFlow POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; overflow: hidden; }
    </style>
</head>
<body class="h-full bg-gray-950 text-white">
    {{ $slot }}
    @livewireScripts
</body>
</html>
