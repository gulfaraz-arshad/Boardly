@php use App\Models\Board;use App\Models\User;use App\Models\Workspace; @endphp
<div class="flex flex-col h-full overflow-hidden" x-data>
    {{-- Board Header --}}
    <div class="shrink-0 px-5 py-3 flex items-center gap-4 bg-black/20 border-b border-white/5">
        {{-- Board title --}}
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full ring-2 ring-white/20" style="background-color: {{ $board->color }}"></div>
            <h1 class="text-lg font-semibold tracking-tight">{{ $board->name }}</h1>
        </div>

        {{-- Member avatars --}}
        <div class="flex -space-x-2">
            @foreach($board->members->take(5) as $member)
                <div
                    class="w-7 h-7 rounded-full bg-linear-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-semibold border-2 border-zinc-950 ring-1 ring-zinc-800"
                    title="{{ $member->name }}">
                    {{ substr($member->name, 0, 1) }}
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 ml-auto">
            {{-- Filter controls --}}
            <div class="flex items-center gap-1 bg-zinc-900 border border-zinc-800 rounded-lg p-1">
                <input wire:model.live.debounce.300="searchQuery"
                       placeholder="Search cards..."
                       class="bg-transparent text-sm px-2 py-1 placeholder-zinc-600 focus:outline-none w-40">
                @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')" class="text-zinc-500 hover:text-zinc-300 px-1">✕</button>
                @endif
            </div>

            {{-- Label filter + manager --}}
            @if(auth()->user()->isPlatformAdmin())
                <x-label-manager
                    :filterLabel="$filterLabel"
                    :showLabelManager="$showLabelManager"
                    :this="$this"
                    :editingLabelId="$editingLabelId"
                    :labelColor="$labelColor"
                    :labelName="$labelName"
                    :labelPalette="$labelPalette"
                    :labels="$this->labels"
                />
            @endif

            {{-- Due date filter --}}
            <select wire:model.live="filterDue"
                    class="bg-zinc-900 border border-zinc-800 hover:border-zinc-600 rounded-lg px-3 py-2 text-sm transition-colors focus:outline-none {{ $filterDue ? 'border-sky-500 text-sky-400' : 'text-zinc-300' }}">
                <option value="">All due dates</option>
                <option value="today">Due today</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>
    </div>
    <div class="flex-1 overflow-x-auto overflow-y-hidden">
        <div class="flex h-full items-start gap-3 p-4"
             wire:sortable="updateListOrder"
             wire:sortable-group="updateCardOrder">

            @foreach($this->lists as $list)
                <div class="shrink-0 w-72 flex flex-col bg-zinc-900 border border-zinc-800 rounded-xl max-h-full"
                     wire:sortable.item="{{ $list->id }}"
                     wire:key="list-{{ $list->id }}">
                    {{-- List header --}}
                    <div class="flex items-center gap-2 px-3 py-2.5 border-b border-zinc-800"
                         wire:sortable.handle>
                        @if($list->color)
                            <div class="w-2 h-2 rounded-full" style="background-color: {{ $list->color }}"></div>
                        @endif

                        <span x-data="{ editing: false, name: '{{ addslashes($list->name) }}' }"
                              class="flex-1 min-w-0">
                        <span x-show="!editing"
                              @dblclick="editing = true"
                              class="text-sm font-semibold text-zinc-200 truncate block cursor-pointer hover:text-white">
                            {{ $list->name }}
                        </span>
                        <input x-show="editing"
                               x-model="name"
                               x-ref="nameInput"
                               @focus="$el.select()"
                               x-init="$watch('editing', v => v && $nextTick(() => $refs.nameInput.focus()))"
                               @keydown.enter="editing = false; $wire.updateListName({{ $list->id }}, name)"
                               @keydown.escape="editing = false; name = '{{ addslashes($list->name) }}'"
                               @blur="editing = false; $wire.updateListName({{ $list->id }}, name)"
                               class="w-full bg-zinc-800 border border-sky-500 rounded px-2 py-0.5 text-sm font-semibold focus:outline-none">
                    </span>

                        {{--                        <span class="text-xs text-zinc-500 shrink-0">{{ $list->cards->count() }}</span>--}}

                        <div x-data="{ open: false }" class="relative shrink-0">
                            <button @click="open = !open"
                                    class="w-6 h-6 flex items-center justify-center rounded hover:bg-zinc-700 transition-colors text-zinc-500 hover:text-zinc-300">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.5"/>
                                    <circle cx="12" cy="12" r="1.5"/>
                                    <circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 top-7 w-40 bg-zinc-800 border border-zinc-700 rounded-lg shadow-xl overflow-hidden z-20">
                                <button wire:click="deleteList({{ $list->id }})"
                                        wire:confirm="Archive '{{ $list->name }}'?"
                                        @click="open = false"
                                        class="w-full text-left px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-700 transition-colors">
                                    Archive list
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Cards container --}}
                    <div class="flex-1 overflow-y-auto overflow-x-hidden p-2 space-y-2"
                         wire:sortable-group.item-group="{{ $list->id }}">

                        @foreach($list->cards as $card)
                            <div class="group bg-zinc-800 p-3 rounded-lg border border-zinc-700/50 shadow-sm cursor-pointer"
                                 wire:sortable-group.item="{{ $card->id }}"
                                 wire:key="card-{{ $card->id }}"
                                 wire:click="openCard({{ $card->id }})">

                                {{-- Cover color bar --}}
                                @if($card->cover_color)
                                    <div class="h-1.5 rounded-sm -mt-0.5 mb-2" style="background-color: {{ $card->cover_color }}"></div>
                                @endif

                                {{-- Labels --}}
                                @if($card->labels->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mb-2">
                                        @foreach($card->labels as $label)
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                                  style="background-color: {{ $label->color }}22; color: {{ $label->color }}; border: 1px solid {{ $label->color }}44">
                                {{ $label->name }}
                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Title --}}
                                <p class="text-sm text-zinc-200 leading-snug font-medium group-hover:text-white transition-colors">
                                    {{ $card->title }}
                                </p>

                                {{-- Meta row --}}
                                <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                                    {{-- Due date --}}
                                    @if($card->due_date)
                                        <span class="inline-flex items-center gap-1 text-[11px] px-1.5 py-0.5 rounded
                                   {{ $card->isDueOverdue() ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
                                   ($card->isDueSoon() ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' :
                                   ($card->is_completed ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-700/50 text-zinc-400')) }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                                                 stroke-linejoin="round"
                                                                                                                 stroke-width="2"
                                                                                                                 d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $card->due_date->format('M j') }}
                            </span>
                                    @endif

                                    {{-- Attachments --}}
                                    @if($card->attachments->isNotEmpty())
                                        <span class="inline-flex items-center gap-1 text-[11px] text-zinc-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                                                 stroke-linejoin="round"
                                                                                                                 stroke-width="2"
                                                                                                                 d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                {{ $card->attachments->count() }}
                            </span>
                                    @endif

                                    {{-- Comments --}}
                                    @if($card->comments_count > 0)
                                        <span class="inline-flex items-center gap-1 text-[11px] text-zinc-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                                                 stroke-linejoin="round"
                                                                                                                 stroke-width="2"
                                                                                                                 d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $card->comments_count }}
                            </span>
                                    @endif

                                    {{-- Completed badge --}}
                                    @if($card->is_completed)
                                        <span class="ml-auto inline-flex items-center text-[11px] text-emerald-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round"
                                                                                                                     stroke-linejoin="round"
                                                                                                                     stroke-width="2"
                                                                                                                     d="M5 13l4 4L19 7"/></svg>
                            </span>
                                    @endif

                                    {{-- Member avatars --}}
                                    @if($card->members->isNotEmpty())
                                        <div class="flex -space-x-1 ml-auto">
                                            @foreach($card->members->take(3) as $m)
                                                <div
                                                    class="w-5 h-5 rounded-full bg-linear-to-br from-sky-400 to-violet-500 flex items-center justify-center text-[9px] font-semibold border border-zinc-800"
                                                    title="{{ $m->name }}">
                                                    {{ substr($m->name, 0, 1) }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Add card --}}
                    <div class="p-2 border-t border-zinc-800">
                        @if($addingCardToList === $list->id)
                            <div class="space-y-2">
                        <textarea wire:model="newCardTitle"
                                  wire:keydown.enter.prevent="addCard"
                                  rows="2"
                                  placeholder="Enter a title..."
                                  autofocus
                                  class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-3 py-2 text-sm placeholder-zinc-500 focus:outline-none resize-none"></textarea>
                                <div class="flex gap-2">
                                    <button wire:click="addCard"
                                            class="flex-1 bg-sky-500 hover:bg-sky-400 text-white rounded-lg py-1.5 text-xs font-medium transition-colors">
                                        Add card
                                    </button>
                                    <button wire:click="$set('addingCardToList', null)"
                                            class="px-3 py-1.5 hover:bg-zinc-700 rounded-lg text-zinc-400 text-xs transition-colors">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        @else
                            <button wire:click="$set('addingCardToList', {{ $list->id }})"
                                    class="w-full flex items-center gap-2 px-2 py-2 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add a card
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Add list column --}}
            <div class="shrink-0 w-72">
                @if($addingList)
                    <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-3 space-y-2">
                        <input wire:model="newListName"
                               wire:keydown.enter="addList"
                               placeholder="Enter list name..."
                               autofocus
                               class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-3 py-2 text-sm placeholder-zinc-500 focus:outline-none">
                        <div class="flex gap-2">
                            <button wire:click="addList"
                                    class="flex-1 bg-sky-500 hover:bg-sky-400 text-white rounded-lg py-2 text-sm font-medium transition-colors">
                                Add list
                            </button>
                            <button wire:click="$set('addingList', false)"
                                    class="px-3 py-2 hover:bg-zinc-700 rounded-lg text-zinc-400 text-sm transition-colors">
                                ✕
                            </button>
                        </div>
                    </div>
                @else
                    <button wire:click="$set('addingList', true)"
                            class="w-full flex items-center gap-2 px-4 py-3 bg-zinc-900/50 border border-dashed border-zinc-700 rounded-xl text-sm text-zinc-500 hover:text-zinc-300 hover:border-zinc-500 hover:bg-zinc-900 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add another list
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
