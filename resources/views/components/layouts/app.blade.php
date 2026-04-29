<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>— Trello</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|instrument-serif:400i&display=swap" rel="stylesheet" />
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}



    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-screen bg-zinc-950 text-zinc-100 antialiased font-sans overflow-hidden flex flex-col">

{{-- Navigation --}}
<nav class="shrink-0 h-14 bg-zinc-900/80 backdrop-blur border-b border-zinc-800 flex items-center px-4 gap-4 z-30">
    <a href="{{ route('boards.index') }}" wire:navigate
       class="flex items-center gap-2 text-white font-semibold text-lg tracking-tight hover:opacity-80 transition-opacity">
        <span class="w-7 h-7 rounded-lg bg-sky-500 flex items-center justify-center text-sm font-bold shadow">T</span>
        Trello
    </a>

    <div class="flex-1"></div>
    {{-- User menu --}}
    @auth
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-sm font-semibold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" @click.outside="open = false" x-transition
                 class="absolute right-0 top-full mt-2 w-48 bg-zinc-800 border border-zinc-700 rounded-xl shadow-2xl overflow-hidden">
                <div class="p-3 border-b border-zinc-700">
                    <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <div class="p-1">
                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-700 transition-colors">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-zinc-700 transition-colors text-red-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</nav>

{{-- Main content --}}
<main class="flex-1 min-h-0 overflow-hidden">
    {{ $slot }}
</main>

{{-- Card modal (global) --}}
@livewire('card-modal')

{{-- Invite member modal (global) --}}
@livewire('invite-member-modal')

@livewireScripts
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>
<script>
    // Keyboard shortcut for search
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-search'));
        }
    });
</script>
</body>
</html>
