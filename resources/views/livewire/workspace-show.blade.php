<div class="min-h-full bg-[#1d2125] flex flex-col">

    {{-- ── Workspace Header (Trello-style) ───────────────────────── --}}
    <div class="bg-[#282e33] border-b border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-6">
            <div class="flex items-start gap-5">

                {{-- Workspace icon --}}
                <div class="w-16 h-16 rounded-xl flex items-center justify-center text-2xl font-bold text-white shrink-0 shadow-lg select-none"
                     style="background-color: {{ $workspace->color }}">
                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                </div>

                {{-- Name + description --}}
                <div class="flex flex-1 min-w-0 pt-1">
                    @if($editingName)
                        <div class="space-y-2" x-data x-init="$nextTick(() => $refs.nameInput.focus())">
                            <input x-ref="nameInput"
                                   wire:model="editName"
                                   @keydown.enter="$wire.saveName()"
                                   @keydown.escape="$wire.set('editingName', false)"
                                   placeholder="Enter a Workspace title"
                                   class="bg-[#1d2125] border border-[#738496] rounded-lg px-3 py-2 text-xl font-bold text-white focus:outline-none focus:border-sky-400 w-full max-w-sm">
                            <textarea wire:model="editDesc"
                                      rows="2"
                                      placeholder="Add a description for your workspace..."
                                      class="bg-[#1d2125] border border-[#738496] rounded-lg px-3 py-2 text-sm text-[#9fadbc] placeholder-[#596773] focus:outline-none focus:border-sky-400 w-full max-w-sm resize-none"></textarea>
                            <div class="flex gap-2">
                                <button wire:click="saveName"
                                        class="bg-[#0c66e4] hover:bg-[#0055cc] text-white px-3 py-1.5 rounded text-sm font-medium transition-colors">
                                    Save
                                </button>
                                <button wire:click="$set('editingName', false)"
                                        class="px-3 py-1.5 hover:bg-white/10 rounded text-sm text-[#9fadbc] transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="cursor-pointer group/name" wire:click="$set('editingName', true)">
                            <h1 class="text-xl font-bold text-white group-hover/name:bg-white/10 inline-block px-2 py-1 rounded -ml-2 transition-colors">
                                {{ $workspace->name }}
                            </h1>
                            @if($workspace->description)
                                <p class="text-sm text-[#9fadbc] mt-1 px-2">{{ $workspace->description }}</p>
                            @else
                                <p class="text-sm text-[#596773] mt-1 px-2 italic group-hover/name:text-[#9fadbc] transition-colors">
                                    Add a description...
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Workspace color picker --}}
                <div x-data="{ open: false }" class="relative shrink-0">
                    <button @click="open = !open"
                            class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg px-3 py-2 text-sm text-[#9fadbc] transition-colors">
                        <span class="w-3 h-3 rounded-sm" style="background-color: {{ $workspace->color }}"></span>
                        Change color
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute right-0 top-10 bg-[#282e33] border border-[#384048] rounded-xl shadow-2xl p-3 z-10 w-48">
                        <p class="text-xs text-[#9fadbc] mb-2 font-medium">Workspace color</p>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['#0c66e4','#6544a3','#ca3521','#216e4e','#7f8b92','#0055cc','#943d73','#b15c00'] as $clr)
                                <button wire:click="updateColor('{{ $clr }}')" @click="open = false"
                                        class="w-9 h-7 rounded transition-all hover:scale-110 {{ $workspace->color === $clr ? 'ring-2 ring-white ring-offset-1 ring-offset-[#282e33]' : '' }}"
                                        style="background-color: {{ $clr }}"></button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Workspace nav tabs (Trello-style) --}}
            <div class="flex items-center gap-1 mt-5 -mb-px">
                <button class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white border-b-2 border-[#0c66e4] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Boards
                </button>
                <button class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-[#9fadbc] hover:text-white hover:bg-white/10 rounded-t-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Members
                </button>
                <a  href="{{ route('workspaces.settings' , ['workspace' => $workspace]) }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium
                text-[#9fadbc]
                hover:text-white
                hover:bg-white/10
                rounded-t-lg
                transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
            </div>
        </div>
    </div>

    {{-- ── Boards Grid ─────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-6xl mx-auto px-6 py-8">

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                {{-- Board cards --}}
                @foreach($this->boards as $board)
                    <div class="group relative rounded-xl overflow-hidden aspect-5/3 cursor-pointer"
                         style="background-color: {{ $board->color }}">

                        {{-- Gradient overlay for readability --}}
                        <div class="absolute inset-0 bg-linear-to-b from-black/10 via-transparent to-black/40"></div>

                        {{-- Board link --}}
                        <a href="{{ route('boards.show', $board) }}" wire:navigate class="absolute inset-0 z-10"></a>

                        {{-- Board name --}}
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <h3 class="text-white font-semibold text-sm leading-tight line-clamp-2 drop-shadow">
                                {{ $board->name }}
                            </h3>
                        </div>

                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        {{-- Star button (top-right) --}}
                        <button class="absolute top-2 right-2 z-20 w-6 h-6 flex items-center justify-center rounded opacity-0 group-hover:opacity-100 hover:bg-black/20 transition-all text-white/80 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </button>

                        {{-- Delete button (owner only) --}}
                        @if($board->isOwnedBy(auth()->user()))
                            <button wire:click="deleteBoard({{ $board->id }})"
                                    wire:confirm="Delete '{{ $board->name }}'? This cannot be undone."
                                    class="absolute bottom-2 right-2 z-20 w-6 h-6 flex items-center justify-center rounded opacity-0 group-hover:opacity-100 hover:bg-black/30 transition-all text-white/70 hover:text-white">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        @endif
                    </div>
                @endforeach

                {{-- Create board card --}}
                @if(!$showCreateBoard)
                    <button wire:click="$set('showCreateBoard', true)"
                            class="aspect-5/3 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors group/create">
                        <div class="text-center">
                            <svg class="w-5 h-5 text-[#9fadbc] group-hover/create:text-white transition-colors mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-sm text-[#9fadbc] group-hover/create:text-white transition-colors">Create board</span>
                        </div>
                    </button>
                @else
                    {{-- Inline create board form --}}
                    <div class="aspect-4.5/3 rounded-xl overflow-hidden relative"
                         style="background-color: {{ $boardColor }}"
                         x-data x-init="$nextTick(() => $refs.boardNameInput.focus())">

                        <div class="absolute inset-0 bg-linear-to-b from-black/20 to-black/50"></div>

                        <div class="absolute inset-0 p-3 flex flex-col gap-2">
                            <input x-ref="boardNameInput"
                                   wire:model="boardName"
                                   @keydown.enter="$wire.createBoard()"
                                   @keydown.escape="$wire.set('showCreateBoard', false)"
                                   placeholder="Board title..."
                                   class="bg-white/20 border border-white/30 rounded px-2 py-1.5 text-sm text-white placeholder-white/60 focus:outline-none focus:bg-white/30 w-full font-semibold">

                            {{-- Color swatches --}}
                            <div class="flex gap-1 flex-wrap">
                                @foreach(['#0c66e4','#6544a3','#ca3521','#216e4e','#0055cc','#943d73','#b15c00','#164b35'] as $clr)
                                    <button type="button" wire:click="$set('boardColor', '{{ $clr }}')"
                                            class="w-5 h-4 rounded-sm transition-all hover:scale-110 {{ $boardColor === $clr ? 'ring-2 ring-white' : '' }}"
                                            style="background-color: {{ $clr }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <div class="absolute bottom-0 left-0 right-0 p-2 flex gap-1.5 bg-linear-to-t from-black/40 to-transparent">
                            <button wire:click="createBoard"
                                    class="flex-1 bg-[#0c66e4] hover:bg-[#0055cc] text-white text-xs font-medium py-1.5 rounded transition-colors">
                                Create
                            </button>
                            <button wire:click="$set('showCreateBoard', false)"
                                    class="px-2 py-1.5 bg-black/30 hover:bg-black/50 text-white/80 text-xs rounded transition-colors">
                                ✕
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Empty state --}}
            @if($this->boards->isEmpty() && !$showCreateBoard)
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#596773]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <p class="text-[#9fadbc] text-sm mb-4">No boards in this workspace yet</p>
                    <button wire:click="$set('showCreateBoard', true)"
                            class="bg-[#0c66e4] hover:bg-[#0055cc] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Create a board
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
