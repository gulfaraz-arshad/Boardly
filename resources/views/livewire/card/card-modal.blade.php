<div>
    @if($isOpen && $card)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 overflow-y-auto"
             @keydown.escape.window="$wire.close()">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="close"></div>

            {{-- Modal --}}
            <div class="relative bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-2xl mb-8"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Cover color --}}
                @if($card->cover_color)
                    <div class="h-10 rounded-t-2xl" style="background-color: {{ $card->cover_color }}"></div>
                @endif

                {{-- Header --}}
                <div class="p-5 pb-0">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-zinc-500 mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <div class="flex-1 min-w-0">
                            {{-- Editable title --}}
                            <div x-data="{ editing: false }">
                                <h2 x-show="!editing"
                                    @click="editing = true"
                                    class="text-lg font-semibold text-white leading-tight cursor-pointer hover:text-sky-300 transition-colors">
                                    {{ $card->title }}
                                    @if($card->is_completed)
                                        <span class="ml-2 text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded-full font-normal">Done</span>
                                    @endif
                                </h2>
                                <input x-show="editing"
                                       wire:model="title"
                                       @keydown.enter="editing = false; $wire.saveTitle()"
                                       @keydown.escape="editing = false"
                                       @blur="editing = false; $wire.saveTitle()"
                                       x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                       class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-3 py-1.5 text-lg font-semibold focus:outline-none">
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">
                                in <span class="text-zinc-400 font-medium">{{ $card->list->name }}</span>
                                · created {{ $card->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <button wire:click="close"
                                class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-zinc-800 text-zinc-500 hover:text-zinc-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-5 grid grid-cols-3 gap-5">

                    {{-- Main content (2 cols) --}}
                    <div class="col-span-2 space-y-5">

                        {{-- Labels --}}
                        @if($card->labels->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Labels</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($card->labels as $label)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                              style="background-color: {{ $label->color }}22; color: {{ $label->color }}; border: 1px solid {{ $label->color }}44">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $label->color }}"></span>
                            {{ $label->name }}
                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Description --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Description</p>
                            <div x-data="{ editing: false }">
                                <div x-show="!editing"
                                     @click="editing = true"
                                     class="min-h-[80px] p-3 bg-zinc-800 border border-zinc-700 hover:border-zinc-600 rounded-lg text-sm text-zinc-300 cursor-pointer transition-colors">
                                    @if($card->description)
                                        <p class="whitespace-pre-wrap">{{ $card->description }}</p>
                                    @else
                                        <p class="text-zinc-600 italic">Add a description...</p>
                                    @endif
                                </div>
                                <div x-show="editing" class="space-y-2">
                            <textarea wire:model="description"
                                      rows="4"
                                      placeholder="Add a more detailed description..."
                                      x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                      class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-600 focus:outline-none resize-none"></textarea>
                                    <div class="flex gap-2">
                                        <button wire:click="saveDescription" @click="editing = false"
                                                class="bg-sky-500 hover:bg-sky-400 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                            Save
                                        </button>
                                        <button @click="editing = false"
                                                class="px-3 py-1.5 hover:bg-zinc-700 rounded-lg text-zinc-400 text-sm transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Checklists --}}
                        @if($card->checklists->isNotEmpty())
                            <div class="space-y-5">
                                @foreach($card->checklists as $checklist)
                                    @php
                                        $total   = $checklist->items->count();
                                        $checked = $checklist->items->where('is_checked', true)->count();
                                        $percent = $total > 0 ? round($checked / $total * 100) : 0;
                                    @endphp
                                    <div>
                                        {{-- Checklist header --}}
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                <span class="text-sm font-semibold text-zinc-200">{{ $checklist->title }}</span>
                                                <span class="text-xs text-zinc-500">{{ $checked }}/{{ $total }}</span>
                                            </div>
                                            <button wire:click="deleteChecklist({{ $checklist->id }})"
                                                    class="text-xs text-zinc-600 hover:text-red-400 transition-colors px-2 py-1 rounded hover:bg-zinc-800">
                                                Delete
                                            </button>
                                        </div>

                                        {{-- Progress bar --}}
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="text-xs text-zinc-500 w-7 text-right shrink-0">{{ $percent }}%</span>
                                            <div class="flex-1 h-1.5 bg-zinc-700 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500
                                    {{ $percent === 100 ? 'bg-emerald-500' : 'bg-sky-500' }}"
                                                     style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>

                                        {{-- Items --}}
                                        <div class="space-y-1">
                                            @foreach($checklist->items as $item)
                                                <div class="flex items-start gap-2.5 group/item px-1 py-1 rounded-lg hover:bg-zinc-800/60 transition-colors"
                                                     x-data="{ editing: false, content: @js($item->content) }">

                                                    {{-- Checkbox --}}
                                                    <button wire:click="toggleChecklistItem({{ $item->id }})"
                                                            class="mt-0.5 shrink-0 w-4 h-4 rounded border-2 flex items-center justify-center transition-all
                                            {{ $item->is_checked
                                                ? 'bg-emerald-500 border-emerald-500'
                                                : 'border-zinc-600 hover:border-sky-400' }}">
                                                        @if($item->is_checked)
                                                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        @endif
                                                    </button>

                                                    {{-- Content --}}
                                                    <div class="flex-1 min-w-0">
                                    <span x-show="!editing"
                                          @click="editing = true"
                                          class="text-sm cursor-pointer leading-snug block
                                              {{ $item->is_checked ? 'line-through text-zinc-500' : 'text-zinc-300 hover:text-white' }}">
                                        {{ $item->content }}
                                    </span>
                                                        <div x-show="editing" class="flex items-center gap-2">
                                                            <input x-model="content"
                                                                   x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                                   @keydown.enter="editing = false; $wire.updateChecklistItemContent({{ $item->id }}, content)"
                                                                   @keydown.escape="editing = false; content = @js($item->content)"
                                                                   @blur="editing = false; $wire.updateChecklistItemContent({{ $item->id }}, content)"
                                                                   class="flex-1 bg-zinc-800 border border-sky-500 rounded px-2 py-0.5 text-sm focus:outline-none">
                                                        </div>
                                                    </div>

                                                    {{-- Delete item --}}
                                                    <button wire:click="deleteChecklistItem({{ $item->id }})"
                                                            class="opacity-0 group-hover/item:opacity-100 transition-opacity shrink-0 text-zinc-600 hover:text-red-400 mt-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach

                                            {{-- Add item --}}
                                            <div x-data="{ adding: false }"
                                                 class="pl-1">
                                                <div x-show="!adding">
                                                    <button @click="adding = true"
                                                            class="flex items-center gap-1.5 text-xs text-zinc-500 hover:text-zinc-300 px-1 py-1 rounded transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                        Add an item
                                                    </button>
                                                </div>
                                                <div x-show="adding" x-cloak class="flex items-center gap-2 mt-1">
                                                    <input wire:model="newItemContent.{{ $checklist->id }}"
                                                           x-init="$watch('adding', v => v && $nextTick(() => $el.focus()))"
                                                           @keydown.enter="$wire.addChecklistItem({{ $checklist->id }})"
                                                           @keydown.escape="adding = false"
                                                           placeholder="Add an item..."
                                                           class="flex-1 bg-zinc-800 border border-sky-500 rounded-lg px-2.5 py-1.5 text-sm placeholder-zinc-600 focus:outline-none">
                                                    <button wire:click="addChecklistItem({{ $checklist->id }})" @click="adding = false"
                                                            class="bg-sky-500 hover:bg-sky-400 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                                        Add
                                                    </button>
                                                    <button @click="adding = false"
                                                            class="text-zinc-500 hover:text-zinc-300 px-1 py-1.5 text-xs transition-colors">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Attachments --}}
                        @if($card->attachments->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Attachments</p>
                                <div class="space-y-2">
                                    @foreach($card->attachments as $attachment)
                                        <div class="flex items-center gap-3 p-2.5 bg-zinc-800 border border-zinc-700 rounded-lg">
                                            {{-- Thumbnail or icon --}}
                                            @if($attachment->isImage())
                                                <img src="{{ $attachment->url }}" alt="" class="w-12 h-10 object-cover rounded">
                                            @else
                                                <div class="w-12 h-10 bg-zinc-700 rounded flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-zinc-200 truncate">{{ $attachment->original_name }}</p>
                                                <p class="text-xs text-zinc-500">{{ $attachment->formatted_size }} · {{ $attachment->created_at->diffForHumans() }}</p>
                                            </div>
                                            <a href="{{ $attachment->url }}" target="_blank"
                                               class="text-zinc-500 hover:text-sky-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                            <button wire:click="deleteAttachment({{ $attachment->id }})"
                                                    class="text-zinc-500 hover:text-red-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Comments --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Comments</p>

                            <div class="flex gap-3 mb-4">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-semibold shrink-0">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="flex-1 space-y-2">
                            <textarea wire:model="newComment"
                                      rows="2"
                                      placeholder="Write a comment..."
                                      class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-3 py-2 text-sm placeholder-zinc-600 focus:outline-none transition-colors resize-none"></textarea>
                                    <button wire:click="addComment"
                                            class="bg-sky-500 hover:bg-sky-400 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                        Comment
                                    </button>
                                </div>
                            </div>

                            @foreach($card->comments as $comment)
                                <div class="flex gap-3 mb-4">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-semibold shrink-0">
                                        {{ substr($comment->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-sm font-semibold">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-300 mt-1 whitespace-pre-wrap">{{ $comment->body }}</p>
                                        @if($comment->user_id === auth()->id())
                                            <button wire:click="deleteComment({{ $comment->id }})"
                                                    class="text-xs text-zinc-600 hover:text-red-400 transition-colors mt-1">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Activity --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Activity</p>
                            <div class="space-y-3">
                                @foreach($card->activities->take(10) as $activity)
                                    <div class="flex gap-3">
                                        <div class="w-6 h-6 rounded-full bg-zinc-700 flex items-center justify-center text-[10px] font-semibold shrink-0">
                                            {{ substr($activity->user->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-zinc-400">
                                                <span class="font-medium text-zinc-300">{{ $activity->user->name }}</span>
                                                {{ $activity->content }}
                                            </p>
                                            <p class="text-[11px] text-zinc-600 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar (1 col) --}}
                    <div class="space-y-4">

                        {{-- Due date --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Due date</p>
                            <div class="space-y-1.5">
                                <input type="datetime-local"
                                       wire:model="dueDate"
                                       wire:change="saveDueDate"
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-sky-500 transition-colors">
                                <button wire:click="toggleComplete"
                                        class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors
                                    {{ $isCompleted ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-zinc-800 border border-zinc-700 text-zinc-300 hover:border-zinc-500' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ $isCompleted ? 'Completed' : 'Mark complete' }}
                                </button>
                            </div>
                        </div>

                        {{-- Labels --}}
                        <div x-data="{ open: false }">
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Labels</p>
                            <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 hover:border-zinc-600 rounded-lg text-xs text-zinc-300 transition-colors">
                                <span>Edit labels</span>
                                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="mt-1 bg-zinc-800 border border-zinc-700 rounded-xl p-2 space-y-1">
                                @foreach($card->list->board->labels as $label)
                                    @php $hasLabel = $card->labels->contains('id', $label->id); @endphp
                                    <button wire:click="toggleLabel({{ $label->id }})"
                                            class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors hover:bg-zinc-700">
                                        <span class="w-3 h-3 rounded-sm" style="background-color: {{ $label->color }}"></span>
                                        <span class="text-xs text-zinc-300 flex-1 text-left">{{ $label->name }}</span>
                                        @if($hasLabel)
                                            <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Checklist --}}
                        <div x-data="{ open: false }">
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Checklist</p>
                            <button @click="open = !open"
                                    class="w-full flex items-center gap-2 px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 hover:border-zinc-600 rounded-lg text-xs text-zinc-300 transition-colors">
                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                Add checklist
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="mt-2 space-y-2">
                                <input wire:model="newChecklistTitle"
                                       @keydown.enter="$wire.addChecklist(); open = false"
                                       @keydown.escape="open = false"
                                       x-init="$watch('open', v => v && $nextTick(() => $el.focus()))"
                                       placeholder="Checklist title..."
                                       class="w-full bg-zinc-800 border border-sky-500 rounded-lg px-2.5 py-1.5 text-xs placeholder-zinc-600 focus:outline-none">
                                <div class="flex gap-2">
                                    <button wire:click="addChecklist" @click="open = false"
                                            class="flex-1 bg-sky-500 hover:bg-sky-400 text-white py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Add
                                    </button>
                                    <button @click="open = false; $wire.set('newChecklistTitle', '')"
                                            class="px-2 py-1.5 hover:bg-zinc-700 rounded-lg text-zinc-500 text-xs transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Cover color --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Cover</p>
                            <div class="grid grid-cols-4 gap-1.5">
                                @foreach(['','#0ea5e9','#8b5cf6','#ec4899','#ef4444','#f97316','#10b981','#f59e0b','#64748b'] as $clr)
                                    <button wire:click="setCoverColor('{{$clr}}')"
                                            class="h-7 rounded {{ !$clr ? 'bg-zinc-700 border-2 border-dashed border-zinc-600' : '' }} {{ $coverColor === $clr ? 'ring-2 ring-white ring-offset-1 ring-offset-zinc-900' : '' }}"
                                            style="{{ $clr ? 'background-color:' . $clr : '' }}"
                                            title="{{ $clr ?: 'No cover' }}">
                                        @if(!$clr)<span class="text-zinc-500 text-xs">✕</span>@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <div>
                            <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Attachments</p>
                            <div x-data="{ dragging: false }"
                                 @dragover.prevent="dragging = true"
                                 @dragleave="dragging = false"
                                 @drop.prevent="dragging = false; $wire.upload('attachment', $event.dataTransfer.files[0])"
                                 :class="dragging ? 'border-sky-500 bg-sky-500/5' : 'border-zinc-700'"
                                 class="border-2 border-dashed rounded-lg p-3 transition-colors text-center cursor-pointer"
                                 @click="$refs.fileInput.click()">
                                <svg class="w-5 h-5 text-zinc-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <p class="text-xs text-zinc-500">Drop files or click</p>
                                <input x-ref="fileInput" type="file" wire:model="attachment" class="hidden"
                                       @change="$wire.uploadAttachment()">
                            </div>
                            @error('attachment') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Archive --}}
                        <div class="pt-2 border-t border-zinc-800">
                            <button wire:click="archiveCard"
                                    wire:confirm="Archive this card?"
                                    class="w-full flex items-center gap-2 px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 hover:border-red-500/50 hover:text-red-400 rounded-lg text-xs text-zinc-400 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                Archive card
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
