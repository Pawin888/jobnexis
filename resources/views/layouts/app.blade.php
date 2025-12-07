{{-- filepath: e:\Project\jobnexis\resources\views\layouts\app.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'JobNexis')</title>

    {{-- DaisyUI + Tailwind --}}
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('image/web-image/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/web-image/logo.png') }}">

    {{-- ฟอนต์ Kanit --}}
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <script src="https://kit.fontawesome.com/2412bed399.js" crossorigin="anonymous"></script>

    {{-- AlpineJS --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Tom Select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>

@if (Auth::check() && Auth::user()->role === 'jobber' || !Auth::check())

<body class="h-full min-h-screen bg-[linear-gradient(to_bottom,_theme('colors.sky.100')_20%,_theme('colors.blue.300')_100%)] px-28">
    <div class="flex">
        {{-- Sidebar --}}
        @include('layouts.sidebar')
        {{-- Main Content --}}
        <div class="flex flex-col flex-1">
            {{-- Header --}}
            @include('layouts.header')
            <main class="px-6 pt-4 text-md">
                @yield('content')
            </main>
        </div>
    </div>
</body>
@else
<body class="h-full min-h-screen px-6 bg-gray-200">
    <div class="flex">
        {{-- Sidebar --}}
        @include('layouts.sidebar')
        {{-- Main Content --}}
        <div class="flex flex-col flex-1">
            {{-- Header --}}
            @include('layouts.header')
            <main class="px-6 pt-6 text-md">
                @yield('content')
            </main>
        </div>
    </div>
</body>
@endif
</html>
