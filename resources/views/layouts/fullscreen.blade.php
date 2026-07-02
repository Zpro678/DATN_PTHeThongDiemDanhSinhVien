<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'Attendia Tech') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=' . time()) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-slate-50 font-sans antialiased text-slate-900 flex flex-col">

    <!-- Header -->
    <header class="sticky top-0 z-50 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl ?? '#' }}" wire:navigate class="flex h-10 w-10 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition">
                <x-user.icon name="x" :size="24" />
            </a>
            <div class="ml-2 flex items-center gap-3 border-l border-slate-200 pl-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <x-user.icon name="clipboard-check" :size="20" />
                </div>
                <div>
                    <h1 class="text-base font-extrabold leading-tight tracking-tight text-slate-900 truncate max-w-[200px] sm:max-w-md">{{ $title ?? 'Chi tiết' }}</h1>
                    @if(isset($subtitle))
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{ $headerActions ?? '' }}
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full flex flex-col min-h-0">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
