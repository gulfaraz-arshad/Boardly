<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark:bg-zinc-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Brand panel --}}
    <div class="relative hidden overflow-hidden bg-zinc-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
        {{-- dot-grid texture --}}
        <div class="pointer-events-none absolute inset-0 [background-image:radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] [background-size:24px_24px]"></div>
        {{-- color blooms --}}
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-fuchsia-600/20 blur-3xl"></div>

        <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-2 text-white" wire:navigate>
            <x-app-logo-icon class="size-8 fill-current text-white" />
            <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
        </a>

        <div class="relative z-10 max-w-md">
            <p class="text-3xl font-semibold leading-tight text-white">
                {{ $headline ?? 'Everything your team ships, in one shared workspace.' }}
            </p>
            <p class="mt-4 text-sm text-zinc-400">
                {{ $subheadline ?? 'Plan, track, and launch work together — without the tab-switching.' }}
            </p>
        </div>

        <div class="relative z-10 text-xs text-zinc-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    {{-- Form panel --}}
    <div class="flex flex-col items-center justify-center px-6 py-12 sm:px-12">
        <div class="w-full max-w-sm">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2 lg:hidden" wire:navigate>
                <x-app-logo-icon class="size-8 fill-current text-zinc-900 dark:text-white" />
                <span class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
            </a>

            {{ $slot }}
        </div>
    </div>
</div>

@fluxScripts
</body>
</html>
