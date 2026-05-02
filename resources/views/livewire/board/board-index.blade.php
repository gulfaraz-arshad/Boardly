<div class="min-h-full bg-zinc-950">

    {{-- Header --}}
    <div class="border-b border-zinc-800 bg-zinc-900/50 px-6 py-5">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">My Workspaces</h1>
                <p class="text-sm text-zinc-400 mt-0.5">All your boards, organised by workspace</p>
            </div>

            <div class="flex items-center gap-3">
                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300="search"
                           placeholder="Search boards..."
                           class="pl-9 pr-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 w-52 transition-colors">
                </div>

                <button wire:click="openCreateModal()"
                        class="flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-sky-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Board
                </button>
            </div>
        </div>
    </div>

    {{-- Workspace sections --}}
    <div class="max-w-7xl mx-auto px-6 py-8 space-y-10">

        @forelse($this->workspaces as $workspace)
            <section>
                {{-- Workspace header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        {{-- Workspace avatar --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0"
                             style="background-color: {{ $workspace->color ?? '#0ea5e9' }}">
                            {{ strtoupper(substr($workspace->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 max-w-2xl">
                            <h2 class="text-base font-semibold text-white leading-none">{{ $workspace->name }}</h2>
                            @if($workspace->description)
                                <p class="text-xs text-zinc-500 mt-0.5 leading-relaxed">{{ $workspace->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Workspace settings link --}}
                        @if($workspace->isOwnedBy(auth()->user()))
                            <a href="{{ route('workspaces.settings' , ['workspace' => $workspace]) }}" wire:navigate
                               class="flex items-center gap-1.5 text-xs text-zinc-500 hover:text-zinc-300 transition-colors px-2.5 py-1.5 rounded-lg hover:bg-zinc-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Settings
                            </a>
                        @endif

                        <button wire:click="openCreateModal({{ $workspace->id }})"
                                class="flex items-center gap-1.5 text-xs text-zinc-400 hover:text-white transition-colors px-2.5 py-1.5 rounded-lg hover:bg-zinc-800 border border-zinc-700 hover:border-zinc-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add board
                        </button>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-zinc-800 mb-4"></div>

                {{-- Boards grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                    @foreach($workspace->boards as $board)
                        <div class="group relative bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden hover:border-zinc-600 transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
                            {{-- Color bar --}}
                            <div class="h-1.5 w-full" style="background-color: {{ $board->color }}"></div>

                            <a href="{{ route('boards.show', $board) }}" wire:navigate class="block p-4">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <h3 class="font-semibold text-white leading-tight group-hover:text-sky-300 transition-colors">
                                        {{ $board->name }}
                                    </h3>
                                    @if($board->is_public)
                                        <span class="shrink-0 text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded-full">Public</span>
                                    @endif
                                </div>

                                @if($board->description)
                                    <p class="text-xs text-zinc-400 line-clamp-2 mb-3">{{ $board->description }}</p>
                                @endif

                                <div class="flex items-center gap-3 text-xs text-zinc-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        {{ $board->cards_count }} cards
                                    </span>
                                    {{-- Member avatars --}}
                                    <div class="flex -space-x-1.5 ml-auto">
                                        @foreach($board->members->take(4) as $member)
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-[10px] font-semibold border border-zinc-900 ring-1 ring-zinc-800"
                                                 title="{{ $member->name }}">
                                                {{ substr($member->name, 0, 1) }}
                                            </div>
                                        @endforeach
                                        @if($board->members->count() > 4)
                                            <div class="w-5 h-5 rounded-full bg-zinc-700 flex items-center justify-center text-[9px] font-semibold border border-zinc-900">
                                                +{{ $board->members->count() - 4 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>

                            {{-- Board actions --}}
                            @if($board->isOwnedBy(auth()->user()))
                                <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-20"
                                     x-data="{ open: false }">
                                    <button @click.prevent.stop="open = !open"
                                            class="w-6 h-6 bg-zinc-800 border border-zinc-700 rounded flex items-center justify-center hover:bg-zinc-700 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                    <div x-show="open"
                                         @click.outside="open = false"
                                         @click.stop
                                         x-transition
                                         class="absolute right-0 top-8 w-36 bg-zinc-800 border border-zinc-700 rounded-lg shadow-xl overflow-hidden">
                                        <button wire:click="deleteBoard({{ $board->id }})"
                                                wire:confirm="Delete '{{ $board->name }}'? This cannot be undone."
                                                @click="open = false"
                                                class="w-full text-center px-3 py-2 text-sm text-red-400 hover:bg-zinc-700 transition-colors flex items-center justify-center">
                                            Delete board
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Add board card (inline) --}}
                    <button wire:click="openCreateModal({{ $workspace->id }})"
                            class="flex flex-col items-center justify-center gap-2 bg-zinc-900/50 border border-dashed border-zinc-700 rounded-xl p-6 hover:border-zinc-500 hover:bg-zinc-900 transition-all duration-200 text-zinc-500 hover:text-zinc-300 min-h-[120px]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-sm font-medium">Add a board</span>
                    </button>
                </div>
            </section>
        @empty
            {{-- Empty state --}}
            <div class="text-center py-24">
                <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <h2 class="text-lg font-medium text-zinc-300 mb-2">No workspaces yet</h2>
                <p class="text-zinc-500 text-sm mb-6">Create a workspace to organise your boards</p>
{{--                <a href="{{ route('workspaces.create') }}" wire:navigate--}}
{{--                   class="bg-sky-500 hover:bg-sky-400 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors inline-block">--}}
{{--                    Create a workspace--}}
{{--                </a>--}}
            </div>
        @endforelse
    </div>

    {{-- Create Board Modal --}}
    @include('board-create-modal')

</div>
