<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workspace Invitation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-zinc-950 text-zinc-100 antialiased font-sans flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 text-white font-semibold text-xl tracking-tight">
            <span class="w-9 h-9 rounded-xl bg-sky-500 flex items-center justify-center text-base font-bold shadow-lg shadow-sky-500/30">T</span>
            Trello
        </div>
    </div>

    {{-- Invitation card --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-2xl">

        {{-- Accent bar using workspace color --}}
        <div class="h-1.5" style="background-color: {{ $invitation->workspace->color }}"></div>

        <div class="p-8">

            {{-- Inviter --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-lg font-bold shrink-0">
                    {{ substr($invitation->inviter->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">{{ $invitation->inviter->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $invitation->inviter->email }}</p>
                </div>
            </div>

            <h1 class="text-xl font-semibold mb-2 leading-snug">
                You've been invited to join a workspace
            </h1>

            {{-- Workspace info --}}
            <div class="flex items-center gap-3 p-4 bg-zinc-800 border border-zinc-700 rounded-xl mb-6">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-base font-bold shrink-0"
                     style="background-color: {{ $invitation->workspace->color }}33; color: {{ $invitation->workspace->color }}">
                    {{ substr($invitation->workspace->name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-white truncate">{{ $invitation->workspace->name }}</p>
                    @if($invitation->workspace->description)
                        <p class="text-xs text-zinc-400 mt-0.5 truncate">{{ $invitation->workspace->description }}</p>
                    @endif
                </div>
                <span class="shrink-0 text-xs px-2 py-1 rounded-full
                    {{ $invitation->role === 'admin'  ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' :
                       ($invitation->role === 'viewer' ? 'bg-zinc-700 text-zinc-400' :
                                                         'bg-violet-500/10 text-violet-400 border border-violet-500/20') }}">
                    {{ ucfirst($invitation->role) }}
                </span>
            </div>

            {{-- Role explanation --}}
            <div class="mb-6 space-y-1.5">
                <p class="text-xs text-zinc-500 font-medium uppercase tracking-wider">
                    As a {{ $invitation->role }}, you can:
                </p>
                <ul class="space-y-1">
                    @php
                        $check = '<svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                    @endphp

                    @if(in_array($invitation->role, ['admin', 'member']))
                        @foreach([
                            'View all boards and cards',
                            'Create and edit cards',
                            'Add comments and attachments',
                        ] as $perm)
                            <li class="flex items-center gap-2 text-sm text-zinc-400">
                                {!! $check !!} {{ $perm }}
                            </li>
                        @endforeach
                    @endif

                    @if($invitation->role === 'admin')
                        @foreach([
                            'Create and manage boards',
                            'Invite and remove workspace members',
                            'Change workspace settings',
                        ] as $perm)
                            <li class="flex items-center gap-2 text-sm text-zinc-400">
                                {!! $check !!} {{ $perm }}
                            </li>
                        @endforeach
                    @endif

                    @if($invitation->role === 'viewer')
                        <li class="flex items-center gap-2 text-sm text-zinc-400">
                            {!! $check !!} View all workspace content (read-only)
                        </li>
                    @endif
                </ul>
            </div>

            {{-- Expiry --}}
            <p class="text-xs text-zinc-600 mb-6">
                This invitation expires {{ $invitation->expires_at->diffForHumans() }}.
            </p>

            {{-- Actions --}}
            @auth
                @if(auth()->user()->email === $invitation->email)
                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit"
                                class="w-full bg-sky-500 hover:bg-sky-400 text-white py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-sky-500/20 mb-3">
                            Accept invitation
                        </button>
                    </form>
                    <a href="{{ route('workspaces.index') }}"
                       class="block text-center text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
                        Decline and go to my workspaces
                    </a>
                @else
                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl mb-4">
                        <p class="text-sm text-amber-400">
                            <strong>Wrong account.</strong> You're logged in as
                            <span class="font-mono">{{ auth()->user()->email }}</span>,
                            but this invitation was sent to
                            <span class="font-mono">{{ $invitation->email }}</span>.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 py-2.5 rounded-xl text-sm font-medium transition-colors">
                            Sign out and use a different account
                        </button>
                    </form>
                @endif
            @else
                <div class="space-y-3">
                    <a href="{{ route('login') }}?email={{ urlencode($invitation->email) }}&redirect={{ urlencode(request()->url()) }}"
                       class="flex items-center justify-center gap-2 w-full bg-sky-500 hover:bg-sky-400 text-white py-3 rounded-xl font-semibold transition-colors shadow-lg shadow-sky-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign in to accept
                    </a>

                    <div class="relative flex items-center">
                        <div class="flex-1 border-t border-zinc-800"></div>
                        <span class="px-3 text-xs text-zinc-600">or</span>
                        <div class="flex-1 border-t border-zinc-800"></div>
                    </div>

                    <a href="{{ route('register') }}?email={{ urlencode($invitation->email) }}&redirect={{ urlencode(request()->url()) }}"
                       class="flex items-center justify-center gap-2 w-full bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 py-2.5 rounded-xl text-sm font-medium transition-colors">
                        Create an account to join
                    </a>
                </div>

                <p class="text-xs text-zinc-600 text-center mt-4">
                    Invitation sent to <span class="text-zinc-500">{{ $invitation->email }}</span>
                </p>
            @endauth
        </div>
    </div>

    <p class="text-center text-xs text-zinc-700 mt-6">
        Trello — Project management for teams
    </p>
</div>

</body>
</html>
