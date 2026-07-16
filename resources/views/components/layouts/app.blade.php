<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|instrument-serif:400i&display=swap" rel="stylesheet"/>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-zinc-950 text-white">

<div class="flex h-full">

    {{-- Sidebar --}}
    @auth
        @livewire('app-sidebar', ['activeBoardId' => $activeBoardId ?? null])
    @endauth

    {{-- Main column --}}
    <div class="flex-1 flex flex-col min-w-0 h-full">

        {{-- Top bar --}}
        <header class="shrink-0 h-12 bg-zinc-900/70 backdrop-blur border-b border-zinc-800 flex items-center px-4 gap-3 z-20">
            {{-- Breadcrumb / title --}}
            <div class="flex items-center gap-2 text-sm min-w-0 flex-1">
                @isset($breadcrumb)
                    {{ $breadcrumb }}
                @endisset
            </div>

            {{-- Slot for page-level action buttons --}}
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>

        {{-- Page content --}}
        <main class="flex-1 min-h-0 overflow-auto">
            {{ $slot }}
        </main>
    </div>
</div>

@auth
    @livewire('card-modal')
    @livewire('invite-member-modal')
@endauth

@livewireScripts
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>
</body>
</html>
