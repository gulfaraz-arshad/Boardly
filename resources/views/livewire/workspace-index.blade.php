<div class="min-h-screen bg-[#1d2125] text-[#b6c2cf] antialiased">

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <header class="px-6 pt-12 pb-8 max-w-7xl mx-auto">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-400 to-violet-600 flex items-center justify-center text-xl font-bold text-white shadow-lg shrink-0">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-[#deebff] tracking-tight">
                    @php
                        $superAdmin = auth()->user()->isSuperAdmin();
                    @endphp
                    {{ $superAdmin ? auth()->user()->name : 'All Workspaces' }}
                </h1>
                <p class="text-sm text-[#8c9bab] font-medium">Welcome back to your workflow 👋</p>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 pb-20 flex flex-col lg:flex-row gap-10">

        {{-- ── Left nav ────────────────────────────────────────── --}}
        <aside class="w-64 shrink-0 hidden lg:block">
            <nav>
                <ul class="space-y-1 text-[14px]">
                    @php
                        $navItems = [
                            ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Boards', 'active' => true],
                        ];
                    @endphp
                    @foreach($navItems as $item)
                        <li>
                            <button class="flex items-center gap-3 w-full px-3 py-2 rounded-md transition-all {{ $item['active'] ? 'bg-[#a6c5e229] text-[#579dff] font-semibold' : 'text-[#9fadbc] hover:bg-[#a6c5e214] hover:text-[#b6c2cf]' }}">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                                </svg>
                                {{ $item['label'] }}
                            </button>
                        </li>
                    @endforeach

                    <li class="pt-6 pb-2 px-3 text-xs font-bold text-[#8c9bab] uppercase tracking-widest">
                        Workspaces
                    </li>

                    @foreach($this->workspaces as $workspace)
                        <li class="group">
                            <details open class="outline-none">
                                <summary class="flex items-center justify-between px-3 py-2 cursor-pointer hover:bg-[#a6c5e214] rounded-md transition-colors list-none">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded shadow-sm flex items-center justify-center text-[11px] font-bold text-white shrink-0"
                                              style="background-color: {{ $workspace->color }}">
                                            {{ substr($workspace->name, 0, 1) }}
                                        </span>
                                        <span class="font-medium text-[#9fadbc] group-hover:text-[#b6c2cf] truncate">{{ $workspace->name }}</span>
                                    </div>
                                </summary>
                                <div class="ml-9 mt-1 space-y-1">
                                    <a href="{{ route('workspaces.show', $workspace) }}" wire:navigate
                                       class="flex items-center gap-2 py-1.5 text-sm text-[#8c9bab] hover:text-[#b6c2cf] transition-colors">
                                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                                        Boards
                                    </a>
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        {{-- ── Main content ─────────────────────────────────────── --}}
        <main class="flex-1 min-w-0">
            @if($this->workspaces->isNotEmpty())
                @foreach($this->workspaces as $workspace)
                    <section class="mb-12">
                        {{-- Workspace Header --}}
                        <div class="flex items-center gap-3 mb-5 border-b border-[#323940] pb-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0 shadow-inner"
                                 style="background-color: {{ $workspace->color }}">
                                {{ substr($workspace->name, 0, 1) }}
                            </div>
                            <h2 class="font-bold text-[#b6c2cf] text-lg tracking-tight">{{ $workspace->name }}</h2>

                            <a href="{{ route('workspaces.show', $workspace) }}" wire:navigate
                               class="ml-auto px-4 py-1.5 rounded bg-[#282e33] text-xs font-semibold text-[#b6c2cf] hover:bg-[#323940] transition-all">
                                View Workspace
                            </a>
                        </div>

                        {{-- Board Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($workspace->boards as $board)
                                <a href="{{ route('boards.show', $board) }}" wire:navigate
                                   class="group relative h-28 rounded-xl overflow-hidden flex flex-col justify-between p-4 transition-all hover:-translate-y-1 hover:shadow-2xl"
                                   style="background-color: {{ $board->color }};">

                                    {{-- Glass Effect Overlay --}}
                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                                    <div class="absolute inset-0 opacity-10 group-hover:opacity-20 transition-opacity"
                                         style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 12px 12px;"></div>

                                    <div class="relative flex justify-between items-start">
                                        <span class="font-bold text-white text-[15px] leading-snug drop-shadow-md">
                                            {{ $board->name }}
                                        </span>
                                        <button @click.prevent class="opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-white/20">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </button>
                                    </div>

                                    <span class="relative text-[11px] font-bold text-white/80 bg-black/20 self-start px-2 py-0.5 rounded-full backdrop-blur-sm">
                                        {{ $board->cards_count }} {{ Str::plural('card', $board->cards_count) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                {{-- Empty state --}}
                <div class="flex flex-col items-center justify-center py-32 text-center border-2 border-dashed border-[#323940] rounded-3xl">
                    <div class="w-20 h-20 rounded-full bg-[#282e33] flex items-center justify-center mb-6 shadow-xl">
                        <svg class="w-10 h-10 text-[#738496]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-[#b6c2cf] mb-2">No workspaces found</h2>
                    <p class="text-[#8c9bab] max-w-xs">Create your first workspace to start organizing your boards and tasks.</p>
                </div>
            @endif
        </main>
    </div>
</div>
