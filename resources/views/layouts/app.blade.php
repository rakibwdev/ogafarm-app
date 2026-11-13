<!DOCTYPE html>
<html lang="en" class="min-h-screen bg-neutral-50 text-neutral-900 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OgaFarm') — farmoga.com</title>
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
<header class="border-b border-neutral-200 bg-white">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('landing') }}" class="text-xl font-bold tracking-tight text-green-700">🌾 OgaFarm</a>
        <div class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-neutral-500 hover:text-neutral-900">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-neutral-600 hover:text-neutral-900">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">Register</a>
            @endauth
        </div>
    </nav>
</header>

<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>

<footer class="border-t border-neutral-200 bg-white py-6 text-center text-sm text-neutral-500">
    OgaFarm — Connect with farmers, buyers & suppliers across Nigeria • farmoga.com
</footer>
</body>
</html>
