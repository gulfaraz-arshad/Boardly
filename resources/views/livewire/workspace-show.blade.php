<div class="min-h-full flex flex-col bg-[#1d2125]">

    {{-- ── Workspace Header ──────────────────────────────────── --}}
    <div class="bg-[#282e33] border-b border-white/10 shrink-0">
        <div class="max-w-6xl mx-auto px-6 py-5">
            <div class="flex items-center gap-4">

                {{-- Avatar --}}
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl font-bold text-white shrink-0 shadow-lg select-none"
                     style="background-color: {{ $workspace->color }}">
                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                </div>

                {{-- Name + description --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-white leading-tight">{{ $workspace->name }}</h1>
                    @if($workspace->description)
                        <p class="text-sm text-[#9fadbc] mt-0.5">{{ $workspace->description }}</p>
                    @endif
                </div>

                {{-- Role badge for current user --}}
                @php
                    $myRole = auth()->user()->workspaceRole($workspace);
                    $roleColors = [
                        'owner'  => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        'admin'  => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
                        'member' => 'bg-violet-500/20 text-violet-400 border-violet-500/30',
                        'viewer' => 'bg-zinc-600/40 text-zinc-400 border-zinc-600/40',
                    ];
                @endphp
                <span class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full border {{ $roleColors[$myRole] ?? 'bg-zinc-700 text-zinc-400' }}">
                    {{ ucfirst($myRole) }}
                </span>
            </div>

            {{-- Tabs --}}
            <nav class="flex items-center gap-1 mt-4 -mb-px">
                <button wire:click="$set('tab', 'boards')"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                               {{ $tab === 'boards' ? 'text-white border-[#0c66e4]' : 'text-[#9fadbc] border-transparent hover:text-white hover:bg-white/5 rounded-t-lg' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Boards
                    <span class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded-full">{{ $this->boards->count() }}</span>
                </button>

                <button wire:click="$set('tab', 'members')"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                               {{ $tab === 'members' ? 'text-white border-[#0c66e4]' : 'text-[#9fadbc] border-transparent hover:text-white hover:bg-white/5 rounded-t-lg' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Members
                    <span class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded-full">{{ $this->members->count() }}</span>
                </button>

                @can('update', $workspace)
                    <button wire:click="$set('tab', 'settings')"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                               {{ $tab === 'settings' ? 'text-white border-[#0c66e4]' : 'text-[#9fadbc] border-transparent hover:text-white hover:bg-white/5 rounded-t-lg' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </button>
                @endcan
            </nav>
        </div>
    </div>

    {{-- ── Tab Content ────────────────────────────────────────── --}}
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-6xl mx-auto px-6 py-8">

            {{-- ════════════ BOARDS TAB ════════════ --}}
            @if($tab === 'boards')
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                    @foreach($this->boards as $board)
                        <div class="group relative rounded-xl overflow-hidden aspect-[5/3] cursor-pointer shadow-md hover:shadow-xl transition-shadow"
                             style="background-color: {{ $board->color }}">
                            <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/50"></div>
                            <a href="{{ route('boards.show', $board) }}" wire:navigate class="absolute inset-0 z-10"></a>

                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <h3 class="text-white font-semibold text-sm leading-tight drop-shadow">{{ $board->name }}</h3>
                                <p class="text-white/60 text-[10px] mt-0.5">{{ $board->cards_count }} cards</p>
                            </div>

                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity z-0"></div>

                            {{-- Member avatars --}}
                            <div class="absolute top-2 left-2 flex -space-x-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-20">
                                @foreach($board->members->take(3) as $m)
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-[9px] font-bold text-white border border-black/30"
                                         title="{{ $m->name }}">{{ strtoupper(substr($m->name, 0, 1)) }}</div>
                                @endforeach
                            </div>

                            @can('delete', $board)
                                <button wire:click="deleteBoard({{ $board->id }})"
                                        wire:confirm="Delete '{{ $board->name }}'?"
                                        class="absolute top-2 right-2 z-20 w-6 h-6 flex items-center justify-center rounded opacity-0 group-hover:opacity-100 bg-black/30 hover:bg-red-600/80 transition-all text-white/80 hover:text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endcan
                        </div>
                    @endforeach

                    {{-- Create board --}}
                    @can('manageBoards', $workspace)
                        @if(!$showCreateBoard)
                            <button wire:click="$set('showCreateBoard', true)"
                                    class="aspect-[5/3] rounded-xl bg-white/8 hover:bg-white/15 flex flex-col items-center justify-center transition-colors group/c border border-white/10 hover:border-white/20">
                                <svg class="w-5 h-5 text-[#9fadbc] group-hover/c:text-white mb-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span class="text-sm text-[#9fadbc] group-hover/c:text-white transition-colors">Create board</span>
                            </button>
                        @else
                            <div class="aspect-[5/3] rounded-xl overflow-hidden relative"
                                 style="background-color: {{ $boardColor }}"
                                 x-data x-init="$nextTick(() => $refs.bnInput.focus())">
                                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/50"></div>
                                <div class="absolute inset-0 p-3 flex flex-col gap-2">
                                    <input x-ref="bnInput"
                                           wire:model="boardName"
                                           @keydown.enter="$wire.createBoard()"
                                           @keydown.escape="$wire.set('showCreateBoard', false)"
                                           placeholder="Board title..."
                                           class="bg-white/20 border border-white/30 rounded px-2 py-1.5 text-sm text-white placeholder-white/60 focus:outline-none focus:bg-white/30 w-full font-semibold">
                                    <div class="flex gap-1 flex-wrap">
                                        @foreach(['#0c66e4','#6544a3','#ca3521','#216e4e','#0055cc','#943d73','#b15c00','#164b35'] as $clr)
                                            <button type="button" wire:click="$set('boardColor', '{{ $clr }}')"
                                                    class="w-5 h-4 rounded-sm hover:scale-110 transition-transform {{ $boardColor === $clr ? 'ring-2 ring-white' : '' }}"
                                                    style="background-color: {{ $clr }}"></button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 p-2 flex gap-1.5 bg-gradient-to-t from-black/40 to-transparent">
                                    <button wire:click="createBoard"
                                            class="flex-1 bg-[#0c66e4] hover:bg-[#0055cc] text-white text-xs font-medium py-1.5 rounded transition-colors">
                                        Create
                                    </button>
                                    <button wire:click="$set('showCreateBoard', false)"
                                            class="px-2 py-1.5 bg-black/30 hover:bg-black/50 text-white/80 text-xs rounded transition-colors">✕</button>
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>

                @if($this->boards->isEmpty() && !$showCreateBoard)
                    <div class="text-center py-20 mt-4">
                        <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-[#596773]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </div>
                        <p class="text-[#9fadbc] text-sm">No boards yet in this workspace.</p>
                        @can('manageBoards', $workspace)
                            <button wire:click="$set('showCreateBoard', true)"
                                    class="mt-4 bg-[#0c66e4] hover:bg-[#0055cc] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Create your first board
                            </button>
                        @endcan
                    </div>
                @endif


                {{-- ════════════ MEMBERS TAB ════════════ --}}
            @elseif($tab === 'members')
                <div class="max-w-2xl space-y-6">

                    {{-- Invite form (owner only) --}}
                    @can('manageMembers', $workspace)
                        <div class="bg-[#282e33] border border-white/10 rounded-xl p-5">
                            <h2 class="text-base font-semibold text-white mb-1">Invite to workspace</h2>
                            <p class="text-xs text-[#9fadbc] mb-4">
                                Members can access all boards in this workspace based on their role.
                            </p>

                            @if($inviteSuccess)
                                <div class="mb-3 flex items-center gap-2 bg-[#1f845a]/20 border border-[#1f845a]/40 rounded-lg px-3 py-2.5 text-sm text-[#4bce97]">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Member added successfully!
                                </div>
                            @endif

                            @if($inviteError)
                                <div class="mb-3 flex items-center gap-2 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2.5 text-sm text-red-400">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $inviteError }}
                                </div>
                            @endif

                            <div class="flex gap-3">
                                <div class="flex-1 min-w-0">
                                    <input wire:model="inviteEmail"
                                           type="email"
                                           placeholder="Email address"
                                           @keydown.enter="$wire.inviteMember()"
                                           class="w-full bg-[#1d2125] border border-[#454f59] focus:border-[#0c66e4] rounded-lg px-3 py-2.5 text-sm text-[#b6c2cf] placeholder-[#596773] focus:outline-none transition-colors">
                                    @error('inviteEmail') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <select wire:model="inviteRole"
                                        class="bg-[#1d2125] border border-[#454f59] focus:border-[#0c66e4] rounded-lg px-3 py-2.5 text-sm text-[#b6c2cf] focus:outline-none transition-colors">
                                    <option value="admin">Admin</option>
                                    <option value="member">Member</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <button wire:click="inviteMember"
                                        class="shrink-0 bg-[#0c66e4] hover:bg-[#0055cc] text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                    Add
                                </button>
                            </div>

                            {{-- Role explanation --}}
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                @foreach([
                                    ['role' => 'admin',  'color' => 'sky',    'desc' => 'Manage boards, lists & members'],
                                    ['role' => 'member', 'color' => 'violet', 'desc' => 'Create and edit cards'],
                                    ['role' => 'viewer', 'color' => 'zinc',   'desc' => 'Read-only access'],
                                ] as $r)
                                    <div class="bg-[#1d2125] border border-white/5 rounded-lg p-3">
                                        <span class="text-xs font-semibold text-{{ $r['color'] }}-400">{{ ucfirst($r['role']) }}</span>
                                        <p class="text-[10px] text-[#596773] mt-0.5 leading-snug">{{ $r['desc'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endcan

                    {{-- Members list --}}
                    <div class="bg-[#282e33] border border-white/10 rounded-xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/10">
                            <h2 class="text-sm font-semibold text-white">
                                {{ $this->members->count() }} {{ Str::plural('member', $this->members->count()) }}
                            </h2>
                        </div>

                        <div class="divide-y divide-white/5">
                            @foreach($this->members as $member)
                                @php
                                    $role = $member->pivot_role;
                                    $isOwner = $role === 'owner';
                                    $badgeClasses = match($role) {
                                        'owner'  => 'bg-amber-500/15 text-amber-400 border border-amber-500/30',
                                        'admin'  => 'bg-sky-500/15 text-sky-400 border border-sky-500/30',
                                        'member' => 'bg-violet-500/15 text-violet-400 border border-violet-500/30',
                                        'viewer' => 'bg-zinc-600/30 text-zinc-400 border border-zinc-600/40',
                                        default  => 'bg-zinc-700 text-zinc-400',
                                    };
                                    $typeClasses = match($member->type ?? 'member') {
                                        'super_admin' => 'bg-red-500/15 text-red-400',
                                        'admin'       => 'bg-orange-500/15 text-orange-400',
                                        default       => '',
                                    };
                                @endphp
                                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/3 transition-colors">

                                    {{-- Avatar --}}
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-sm font-bold text-white shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-[#b6c2cf] truncate">{{ $member->name }}</span>
                                            {{-- Platform type badge --}}
                                            @if(!empty($typeClasses))
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $typeClasses }}">
                                        {{ str_replace('_', ' ', ucfirst($member->type)) }}
                                    </span>
                                            @endif
                                            {{-- "You" indicator --}}
                                            @if($member->id === auth()->id())
                                                <span class="text-[10px] text-[#596773]">(you)</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-[#596773] truncate">{{ $member->email }}</p>
                                    </div>

                                    {{-- Workspace role --}}
                                    @if($isOwner)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $badgeClasses }} shrink-0">Owner</span>
                                    @elseif(auth()->user()->isWorkspaceOwner($workspace))
                                        {{-- Owner can change roles inline --}}
                                        <div x-data="{ open: false }" class="relative shrink-0">
                                            <button @click="open = !open"
                                                    class="flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $badgeClasses }} hover:opacity-80 transition-opacity">
                                                {{ ucfirst($role) }}
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition
                                                 class="absolute right-0 top-8 w-36 bg-[#1d2125] border border-[#454f59] rounded-xl shadow-2xl overflow-hidden z-20">
                                                @foreach(['admin', 'member', 'viewer'] as $r)
                                                    <button wire:click="changeMemberRole({{ $member->id }}, '{{ $r }}')"
                                                            @click="open = false"
                                                            class="w-full text-left px-3 py-2 text-sm transition-colors flex items-center justify-between
                                                       {{ $role === $r ? 'text-[#0c66e4] bg-[#0c66e4]/10' : 'text-[#b6c2cf] hover:bg-[#454f59]' }}">
                                                        {{ ucfirst($r) }}
                                                        @if($role === $r)
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        @endif
                                                    </button>
                                                @endforeach
                                                <div class="border-t border-[#454f59] my-1"></div>
                                                <button wire:click="removeMember({{ $member->id }})"
                                                        wire:confirm="Remove {{ $member->name }} from this workspace?"
                                                        @click="open = false"
                                                        class="w-full text-left px-3 py-2 text-sm text-red-400 hover:bg-[#454f59] transition-colors">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $badgeClasses }} shrink-0">{{ ucfirst($role) }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                {{-- ════════════ SETTINGS TAB ════════════ --}}
            @elseif($tab === 'settings')
                @can('update', $workspace)
                    <div class="max-w-lg space-y-6">

                        @if($settingsSaved)
                            <div class="flex items-center gap-2 bg-[#1f845a]/20 border border-[#1f845a]/40 rounded-lg px-4 py-3 text-sm text-[#4bce97]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Settings saved.
                            </div>
                        @endif

                        {{-- Name & description --}}
                        <div class="bg-[#282e33] border border-white/10 rounded-xl p-5 space-y-4">
                            <h2 class="text-sm font-semibold text-white">Workspace details</h2>

                            <div>
                                <label class="block text-xs font-medium text-[#9fadbc] mb-1.5">Name</label>
                                <input wire:model="editName"
                                       class="w-full bg-[#1d2125] border border-[#454f59] focus:border-[#0c66e4] rounded-lg px-3 py-2.5 text-sm text-[#b6c2cf] focus:outline-none transition-colors">
                                @error('editName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#9fadbc] mb-1.5">Description</label>
                                <textarea wire:model="editDesc" rows="3"
                                          placeholder="What does this workspace contain?"
                                          class="w-full bg-[#1d2125] border border-[#454f59] focus:border-[#0c66e4] rounded-lg px-3 py-2.5 text-sm text-[#b6c2cf] placeholder-[#596773] focus:outline-none transition-colors resize-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#9fadbc] mb-2">Color</label>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @foreach(['#0c66e4','#6544a3','#ca3521','#216e4e','#0055cc','#943d73','#b15c00','#164b35','#206b74','#4c6b1f'] as $clr)
                                        <button type="button" wire:click="$set('editColor', '{{ $clr }}')"
                                                class="w-7 h-7 rounded-full border-2 transition-all hover:scale-110 {{ $editColor === $clr ? 'border-white scale-110' : 'border-transparent' }}"
                                                style="background-color: {{ $clr }}"></button>
                                    @endforeach
                                </div>
                            </div>

                            <button wire:click="saveSettings"
                                    class="bg-[#0c66e4] hover:bg-[#0055cc] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <span wire:loading.remove wire:target="saveSettings">Save changes</span>
                                <span wire:loading wire:target="saveSettings">Saving...</span>
                            </button>
                        </div>

                        {{-- Danger zone --}}
                        @can('delete', $workspace)
                            <div class="bg-[#282e33] border border-red-500/20 rounded-xl p-5">
                                <h2 class="text-sm font-semibold text-red-400 mb-1">Danger zone</h2>
                                <p class="text-xs text-[#9fadbc] mb-4">
                                    Deleting this workspace will remove it permanently. Boards inside will
                                    not be deleted — they'll become uncategorized.
                                </p>
                                <button wire:click="deleteWorkspace"
                                        wire:confirm="Are you sure? This will permanently delete the workspace '{{ $workspace->name }}'."
                                        class="bg-transparent hover:bg-red-600 border border-red-500/50 hover:border-red-600 text-red-400 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Delete this workspace
                                </button>
                            </div>
                        @endcan
                    </div>
                @else
                    <div class="text-center py-20">
                        <p class="text-[#596773] text-sm">You don't have permission to manage settings.</p>
                    </div>
                @endcan
            @endif

        </div>
    </div>
</div>
