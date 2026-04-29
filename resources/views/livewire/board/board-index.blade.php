<div class="min-h-full bg-zinc-950">
    {{-- Header --}}
    <div class="border-b border-zinc-800 bg-zinc-900/50 px-6 py-5">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">My Boards</h1>
                <p class="text-sm text-zinc-400 mt-0.5">Manage your projects and workspaces</p>
            </div>

            <div class="flex items-center gap-3">
                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300="search"
                           placeholder="Search boards..."
                           class="pl-9 pr-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 w-52 transition-colors">
                </div>

                <button wire:click="$set('showCreateModal', true)"
                        class="flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-sky-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Board
                </button>
            </div>
        </div>
    </div>

    {{-- Boards grid --}}
    <div class="max-w-6xl mx-auto px-6 py-8">
        @if($this->boards->isEmpty())
            <div class="text-center py-24">
                <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                </div>
                <h2 class="text-lg font-medium text-zinc-300 mb-2">No boards yet</h2>
                <p class="text-zinc-500 text-sm mb-6">Create your first board to get started</p>
                <button wire:click="$set('showCreateModal', true)"
                        class="bg-sky-500 hover:bg-sky-400 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    Create a board
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($this->boards as $board)
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
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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

                        {{-- Actions --}}
                        {{-- Actions --}}
                        @if($board->isOwnedBy(auth()->user()))
                            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-20"
                                 x-data="{ open: false }">
                                <button @click.prevent.stop="open = !open"
                                class="w-6 h-6 bg-zinc-800 border border-zinc-700 rounded flex items-center justify-center hover:bg-zinc-700 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
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

                {{-- Create new board card --}}
                <button wire:click="$set('showCreateModal', true)"
                        class="flex flex-col items-center justify-center gap-2 bg-zinc-900/50 border border-dashed border-zinc-700 rounded-xl p-6 hover:border-zinc-500 hover:bg-zinc-900 transition-all duration-200 text-zinc-500 hover:text-zinc-300 min-h-[120px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm font-medium">New Board</span>
                </button>
            </div>
        @endif
    </div>

    {{-- Create Board Modal --}}
    @if($showCreateModal)
    <div x-data x-show="$wire.showCreateModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="$wire.set('showCreateModal', false)">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>

        <div class="relative bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="p-6 border-b border-zinc-800">
                <h2 class="text-lg font-semibold">Create new board</h2>
                <p class="text-sm text-zinc-400 mt-0.5">Set up a workspace for your project</p>
            </div>

            <form wire:submit="createBoard" class="p-6 space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium mb-1.5">Board name <span class="text-red-400">*</span></label>
                    <input wire:model="name" placeholder="e.g. Marketing Campaign"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 transition-colors">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium mb-1.5">Description</label>
                    <textarea wire:model="description" rows="2" placeholder="What is this board for?"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 transition-colors resize-none"></textarea>
                </div>

                {{-- Color --}}
                <div>
                    <label class="block text-sm font-medium mb-2">Board color</label>
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach(['#0ea5e9','#8b5cf6','#ec4899','#ef4444','#f97316','#10b981','#f59e0b','#6366f1'] as $clr)
                            <button type="button" wire:click="$set('color', '{{ $clr }}')"
                                    class="w-7 h-7 rounded-full border-2 transition-all {{ $color === $clr ? 'border-white scale-110' : 'border-transparent' }}"
                                    style="background-color: {{ $clr }}"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Public toggle --}}
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative" x-data>
                        <input type="checkbox" wire:model="is_public" class="sr-only peer">
                        <div class="w-10 h-6 bg-zinc-700 rounded-full peer peer-checked:bg-sky-500 transition-colors"></div>
                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <div>
                        <span class="text-sm font-medium">Public board</span>
                        <p class="text-xs text-zinc-500">Anyone can view this board</p>
                    </div>
                </label>

                <div class="flex gap-3 pt-1">
                    <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex-1 px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-sky-500 hover:bg-sky-400 text-white rounded-lg text-sm font-medium transition-colors shadow-lg shadow-sky-500/20">
                        <span wire:loading.remove wire:target="createBoard">Create board</span>
                        <span wire:loading wire:target="createBoard">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
