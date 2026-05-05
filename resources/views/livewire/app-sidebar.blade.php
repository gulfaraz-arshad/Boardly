@php use App\Models\Workspace; @endphp
<aside x-data="{ collapsed: $wire.entangle('collapsed') }"
       :class="collapsed ? 'w-14' : 'w-64'"
       class="relative shrink-0 h-full bg-zinc-900 border-r border-zinc-800 flex flex-col transition-all duration-300 ease-in-out overflow-hidden">

    {{-- ── Top: Logo + Toggle ─────────────────────────────────── --}}
    <div class="flex items-center h-14 px-3 border-b border-zinc-800 shrink-0">
        <a href="{{ route('workspaces.index') }}" wire:navigate
           class="flex items-center gap-2.5 min-w-0 flex-1 overflow-hidden">
            <span class="w-7 h-7 rounded-lg bg-sky-500 flex items-center justify-center text-sm font-bold shadow shrink-0">T</span>
            <span x-show="!collapsed" x-cloak x-transition:enter="transition-opacity duration-200 delay-100"
                  x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                  class="font-semibold text-white tracking-tight truncate">Trello</span>
        </a>

        <button wire:click="toggle"
                class="shrink-0 ml-1 w-7 h-7 rounded-lg hover:bg-zinc-800 flex items-center justify-center text-zinc-500 hover:text-zinc-300 transition-colors"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
            <svg x-show="!collapsed" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="collapsed" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- ── Scrollable nav area ─────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden py-3 space-y-1 min-h-0">

        {{-- Home link --}}
        <div class="px-2">
            <a href="{{ route('workspaces.index') }}" wire:navigate
               class="flex items-center gap-2.5 px-2 py-2 rounded-lg transition-colors
                      {{ request()->routeIs('workspaces.index') ? 'bg-sky-500/10 text-sky-400' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="!collapsed" x-cloak x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      class="text-sm font-medium truncate">Home</span>
            </a>
        </div>

        {{-- Divider --}}
        <div x-show="!collapsed" x-cloak class="mx-4 my-2 border-t border-zinc-800"></div>

        {{-- ── Workspaces heading ───────────────────────────── --}}
        <div x-show="!collapsed"
             x-cloak
             x-transition:enter="transition-opacity duration-150 delay-75"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="px-3">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-semibold text-zinc-600 uppercase tracking-widest">Workspaces</span>
                @if(auth()->user()->isPlatformAdmin())
                    <button wire:click="$set('showCreateWorkspace', true)"
                            class="w-5 h-5 rounded flex items-center justify-center text-zinc-600 hover:text-zinc-300 hover:bg-zinc-800 transition-colors"
                            title="New workspace">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Workspace list ───────────────────────────────── --}}
        <div class="px-2 space-y-0.5">
            @foreach($this->workspaces as $workspace)
                @php
                    $isOwner  = $workspace->isOwnedBy(auth()->user());
                    $canAdmin = $isOwner || auth()->user()->isPlatformAdmin();
                @endphp
                <div>
                    {{-- Workspace row --}}
                    <div class="group flex items-center gap-1 rounded-lg transition-colors hover:bg-zinc-800/60"
                         :class="collapsed ? 'px-1.5 py-2 justify-center' : 'px-2 py-1.5'">

                        <button wire:click="toggleWorkspace({{ $workspace->id }})"
                                class="flex items-center gap-2 flex-1 min-w-0 text-left"
                                :class="collapsed ? 'justify-center' : ''"
                                title="{{ $workspace->name }}">

                            <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-2 ring-white/10"
                                  style="background-color: {{ $workspace->color }}"></span>

                            <span x-show="!collapsed" x-cloak class="text-sm font-medium text-zinc-300 truncate flex-1">
                                @if($editingWorkspaceId === $workspace->id)
                                    <input wire:model="editWsName"
                                           wire:keydown.enter="saveWorkspaceName"
                                           wire:keydown.escape="$set('editingWorkspaceId', null)"
                                           wire:blur="saveWorkspaceName"
                                           @click.stop
                                           class="bg-zinc-700 border border-sky-500 rounded px-1.5 py-0.5 text-sm w-full focus:outline-none"
                                           autofocus>
                                @else
                                    <a href="{{ route('workspaces.show', $workspace) }}" wire:navigate
                                       @click.stop
                                       class="hover:text-white transition-colors">{{ $workspace->name }}</a>
                                @endif
                            </span>

                            <svg x-show="!collapsed"
                                 x-cloak
                                 class="w-3.5 h-3.5 text-zinc-600 shrink-0 transition-transform duration-200"
                                 :class="{{ json_encode((bool)($expanded[$workspace->id] ?? false)) }} ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        {{-- Actions — owner/admin only --}}
                        @if($canAdmin)
                            <div x-show="!collapsed" x-data="{ open: false }"
                                 x-cloak
                                 class="opacity-0 group-hover:opacity-100 transition-opacity shrink-0 relative">
                                <button @click.stop="open = !open"
                                        class="w-5 h-5 flex items-center justify-center rounded hover:bg-zinc-700 text-zinc-500 hover:text-zinc-300 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5" r="1.5"/>
                                        <circle cx="12" cy="12" r="1.5"/>
                                        <circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition
                                     x-cloak
                                     class="absolute right-0 top-6 w-44 bg-zinc-800 border border-zinc-700 rounded-xl shadow-2xl overflow-hidden z-50">

                                    {{-- Add board — owner/admin only --}}
                                    <button wire:click="$set('creatingBoardInWorkspace', {{ $workspace->id }})" @click="open = false"
                                            class="w-full text-left px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-700 transition-colors flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add board
                                    </button>

                                    {{-- Owner-only actions --}}
                                    @if($isOwner)
                                        <div class="border-t border-zinc-700"></div>

                                        <button wire:click="startEditWorkspace({{ $workspace->id }})" @click="open = false"
                                                class="w-full text-left px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-700 transition-colors flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Rename
                                        </button>

                                        <div class="border-t border-zinc-700 my-1"></div>

                                        <button wire:click="deleteWorkspace({{ $workspace->id }})"
                                                wire:confirm="Delete workspace '{{ $workspace->name }}'? All boards inside will also be deleted."
                                                @click="open = false"
                                                class="w-full text-left px-3 py-2 text-sm text-red-400 hover:bg-zinc-700 transition-colors flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete workspace
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Boards inside workspace (collapsible) --}}
                    @if(!$collapsed && ($expanded[$workspace->id] ?? false))
                        <div class="ml-4 pl-2 border-l border-zinc-800 mt-0.5 mb-1 space-y-0.5">
                            @forelse($workspace->boards as $board)
                                <a href="{{ route('boards.show', $board) }}" wire:navigate
                                   class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors group/board
                                          {{ $activeBoardId === $board->id
                                             ? 'bg-sky-500/10 text-sky-400 font-medium'
                                             : 'text-zinc-500 hover:text-zinc-200 hover:bg-zinc-800' }}">
                                    <span class="w-2 h-2 rounded-full shrink-0 opacity-70"
                                          style="background-color: {{ $board->color }}"></span>
                                    <span class="truncate flex-1">{{ $board->name }}</span>
                                    <span class="text-[10px] opacity-0 group-hover/board:opacity-100 transition-opacity text-zinc-600">
                                        {{ $board->cards_count }}
                                    </span>
                                </a>
                            @empty
                                <p class="px-2 py-1 text-xs text-zinc-600">No boards yet</p>
                            @endforelse

                            {{-- Quick add board — owner/admin only --}}
                            @if($canAdmin)
                                @if($creatingBoardInWorkspace === $workspace->id)
                                    <div class="py-1 space-y-1.5" x-data x-init="$nextTick(() => $el.querySelector('input').focus())">
                                        <input wire:model="quickBoardName"
                                               wire:keydown.enter="createQuickBoard"
                                               wire:keydown.escape="$set('creatingBoardInWorkspace', null)"
                                               placeholder="Board name..."
                                               class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-2.5 py-1.5 text-xs placeholder-zinc-600 focus:outline-none">
                                        <div class="flex gap-1.5">
                                            @foreach(['#0ea5e9','#8b5cf6','#ec4899','#ef4444','#10b981','#f97316'] as $clr)
                                                <button type="button" wire:click="$set('quickBoardColor', '{{ $clr }}')"
                                                        class="w-4 h-4 rounded-full border-2 transition-all {{ $quickBoardColor === $clr ? 'border-white' : 'border-transparent' }}"
                                                        style="background-color: {{ $clr }}"></button>
                                            @endforeach
                                        </div>
                                        <div class="flex gap-1.5">
                                            <button wire:click="createQuickBoard"
                                                    class="flex-1 bg-sky-500 hover:bg-sky-400 text-white rounded-lg py-1 text-xs font-medium transition-colors">
                                                Add
                                            </button>
                                            <button wire:click="$set('creatingBoardInWorkspace', null)"
                                                    class="px-2 py-1 text-zinc-500 hover:text-zinc-300 text-xs rounded-lg hover:bg-zinc-800 transition-colors">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <button wire:click="$set('creatingBoardInWorkspace', {{ $workspace->id }})"
                                            class="flex items-center gap-2 px-2 py-1 text-xs text-zinc-600 hover:text-zinc-400 transition-colors w-full rounded-lg hover:bg-zinc-800/60">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add board
                                    </button>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Empty state --}}
            @if($this->workspaces->isEmpty())
                <div x-show="!collapsed" x-cloak class="px-2 py-3 text-center">
                    <p class="text-xs text-zinc-600">No workspaces yet</p>
                    @if(auth()->user()->isPlatformAdmin())
                        <button wire:click="$set('showCreateWorkspace', true)"
                                class="text-xs text-sky-500 hover:text-sky-400 transition-colors mt-1">
                            Create one →
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ── Bottom: User info ───────────────────────────────────── --}}
    <div class="shrink-0 border-t border-zinc-800 p-2" x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center gap-2.5 w-full px-2 py-2 rounded-lg hover:bg-zinc-800 transition-colors"
                :class="collapsed ? 'justify-center' : ''">
            <div class="w-7 h-7 rounded-full bg-linear-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-bold shrink-0">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div x-show="!collapsed" class="flex-1 min-w-0 text-left" x-cloak>
                <p class="text-xs font-semibold text-zinc-200 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-zinc-600 truncate">{{ auth()->user()->email }}</p>
            </div>
            <svg x-show="!collapsed" x-cloak class="w-3.5 h-3.5 text-zinc-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>

        <div x-show="open" @click.outside="open = false" x-transition
             x-cloak
             :class="collapsed ? 'left-14 bottom-2' : 'left-2 bottom-14'"
             class="absolute w-52 bg-zinc-800 border border-zinc-700 rounded-xl shadow-2xl overflow-hidden z-50">
            <div class="p-3 border-b border-zinc-700">
                <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
            </div>
            <div class="p-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-400 rounded-lg hover:bg-zinc-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Create Workspace Modal ───────────────────────────────── --}}
    @if($showCreateWorkspace)
        <div class="fixed inset-0 z-60 flex items-center justify-center p-4"
             @keydown.escape.window="$wire.set('showCreateWorkspace', false)">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                 wire:click="$set('showCreateWorkspace', false)"></div>

            <div class="relative bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="p-5 border-b border-zinc-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">New Workspace</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Group related boards together</p>
                    </div>
                    <button wire:click="$set('showCreateWorkspace', false)"
                            class="text-zinc-600 hover:text-zinc-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="createWorkspace" class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">
                            Workspace name <span class="text-red-400">*</span>
                        </label>
                        <input wire:model="wsName"
                               placeholder="e.g. Engineering, Design, Marketing"
                               autofocus
                               class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-600 focus:outline-none transition-colors">
                        @error('wsName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-1.5">Description</label>
                        <textarea wire:model="wsDescription" rows="2"
                                  placeholder="What does this workspace contain?"
                                  class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-3 py-2 text-sm placeholder-zinc-600 focus:outline-none transition-colors resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-400 mb-2">Color</label>
                        <div class="flex items-center gap-2">
                            @foreach(['#0ea5e9','#8b5cf6','#ec4899','#ef4444','#f97316','#10b981','#f59e0b','#6366f1'] as $clr)
                                <button type="button" wire:click="$set('wsColor', '{{ $clr }}')"
                                        class="w-6 h-6 rounded-full border-2 transition-all {{ $wsColor === $clr ? 'border-white scale-110' : 'border-transparent' }}"
                                        style="background-color: {{ $clr }}"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" wire:click="$set('showCreateWorkspace', false)"
                                class="flex-1 px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-sky-500 hover:bg-sky-400 text-white rounded-lg text-sm font-medium transition-colors">
                            <span wire:loading.remove wire:target="createWorkspace">Create</span>
                            <span wire:loading wire:target="createWorkspace">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</aside>
