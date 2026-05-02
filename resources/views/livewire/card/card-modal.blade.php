<div>
    @if($isOpen && $card)
        <div>
            <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-12"
                 @keydown.escape.window="$wire.close()">

                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/50" wire:click="close"></div>

                {{-- Modal --}}
                <div class="relative bg-zinc-900 rounded-xl shadow-2xl  max-w-3xl max-h-[90vh]  overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    {{-- Cover color --}}
                    @if($card->cover_color)
                        <div class="h-16 w-full" style="background-color: {{ $card->cover_color }}"></div>
                    @else
                        <div class="h-16 w-full bg-gradient-to-r from-sky-500/20 to-violet-500/20"></div>
                    @endif

                    {{-- Header --}}
                    <div class="px-6 pt-6 pb-4 border-b border-zinc-800">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                {{-- Editable title --}}
                                <div x-data="{ editing: false }">
                                    <h2 x-show="!editing"
                                        @click="editing = true"
                                        class="text-2xl font-bold text-white leading-tight cursor-pointer hover:text-sky-300 transition-colors">
                                        {{ $card->title }}
                                        @if($card->is_completed)
                                            <span
                                                class="ml-2 text-sm bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-full font-medium inline-block">✓ Done</span>
                                        @endif
                                    </h2>
                                    <input x-show="editing"
                                           wire:model="title"
                                           @keydown.enter="editing = false; $wire.saveTitle()"
                                           @keydown.escape="editing = false"
                                           @blur="editing = false; $wire.saveTitle()"
                                           x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                           class="w-full bg-zinc-800 border-2 border-sky-500 rounded-lg px-4 py-2 text-lg font-semibold focus:outline-none text-white">
                                </div>
                                <p class="text-sm text-zinc-400 mt-2">
                                    in list <span class="text-zinc-300 font-medium">{{ $card->list->name }}</span>
                                    <span class="mx-1">·</span>
                                    <span class="text-zinc-500">{{ $card->created_at->diffForHumans() }}</span>
                                </p>
                            </div>

                            <button wire:click="close"
                                    class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg hover:bg-zinc-800 text-zinc-400 hover:text-zinc-200 transition-all hover:shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 grid grid-cols-3 gap-6">

                        {{-- Main content (2 cols) --}}
                        <div class="col-span-2 space-y-6">

                            {{-- Labels --}}
                            @if($card->labels->isNotEmpty())
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Labels</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($card->labels as $label)
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium transition-all hover:opacity-80"
                                                style="background-color: {{ $label->color }}20; color: {{ $label->color }}; border: 1px solid {{ $label->color }}40">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $label->color }}"></span>
                            {{ $label->name }}
                        </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Description --}}
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Description</p>
                                </div>
                                <div x-data="{ editing: false }">
                                    <div x-show="!editing"
                                         @click="editing = true"
                                         class="min-h-24 p-4 bg-zinc-800/50 border border-zinc-700 hover:border-zinc-600 rounded-lg text-sm text-zinc-300 cursor-pointer transition-all hover:bg-zinc-800/70">
                                        @if($card->description)
                                            <p class="whitespace-pre-wrap">{{ $card->description }}</p>
                                        @else
                                            <p class="text-zinc-500 italic">Add a description...</p>
                                        @endif
                                    </div>
                                    <div x-show="editing" class="space-y-2">
                            <textarea wire:model="description"
                                      rows="5"
                                      placeholder="Add a more detailed description..."
                                      x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                      class="w-full bg-zinc-800 border-2 border-sky-500 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-600 focus:outline-none resize-none text-white"></textarea>
                                        <div class="flex gap-2">
                                            <button wire:click="saveDescription" @click="editing = false"
                                                    class="bg-sky-600 hover:bg-sky-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow-md">
                                                Save
                                            </button>
                                            <button @click="editing = false"
                                                    class="px-4 py-2 hover:bg-zinc-700 rounded-lg text-zinc-400 text-sm transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Attachments</p>

                            {{-- Attachments --}}
                            @foreach($card->attachments as $attachment)
                                <div class="group flex items-center gap-3 p-3 bg-zinc-800/50 border border-zinc-700 hover:border-sky-500/50 hover:bg-zinc-800 rounded-lg transition-all">

                                    <!-- File Info Link (Clicking this opens the file) -->
                                    <a href="{{ $attachment->url }}" target="_blank" class="flex flex-1 items-center gap-3 min-w-0">
                                        @if($attachment->isImage())
                                            <img src="{{ $attachment->url }}" alt="" class="w-10 h-10 object-cover rounded">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-zinc-700 to-zinc-800 rounded flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-zinc-200 truncate group-hover:underline">{{ $attachment->original_name }}</p>
                                            <p class="text-xs text-zinc-500">{{ $attachment->formatted_size }} · {{ $attachment->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>

                                    <!-- Action Buttons Container -->
                                    <div class="flex items-center gap-1 flex-shrink-0">

                                        <!-- Download Button -->
                                        <a href="{{ $attachment->url }}"
                                           download="{{ $attachment->original_name }}"
                                           class="p-2 hover:bg-sky-500/20 rounded text-zinc-400 hover:text-sky-400 transition-colors"
                                           title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>

                                        <!-- Delete Button -->
                                        <button type="button"
                                                wire:click="deleteAttachment({{ $attachment->id }})"
                                                class="p-2 hover:bg-red-500/20 rounded text-zinc-400 hover:text-red-500 transition-colors"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>

                                    </div>
                                </div>
                            @endforeach

                            {{-- Checklists --}}
                            @if($card->checklists->isNotEmpty())
                                <div class="space-y-6">
                                    @foreach($card->checklists as $checklist)
                                        @php
                                            $total   = $checklist->items->count();
                                            $checked = $checklist->items->where('is_checked', true)->count();
                                            $percent = $total > 0 ? round($checked / $total * 100) : 0;
                                        @endphp
                                        <div class="border border-zinc-700 rounded-lg p-4 bg-zinc-800/30">
                                            {{-- Checklist header --}}
                                            <div class="flex items-center justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                    </svg>
                                                    <div>
                                                        <span class="text-sm font-semibold text-zinc-200">{{ $checklist->title }}</span>
                                                        <span class="ml-2 text-xs text-zinc-500 font-medium">{{ $checked }}/{{ $total }}</span>
                                                    </div>
                                                </div>
                                                <button wire:click="deleteChecklist({{ $checklist->id }})"
                                                        class="text-xs text-zinc-600 hover:text-red-400 transition-colors px-3 py-1.5 rounded-lg hover:bg-red-950/20 font-medium">
                                                    Delete
                                                </button>
                                            </div>

                                            {{-- Progress bar --}}
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="flex-1 h-2 bg-zinc-700 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500
                                    {{ $percent === 100 ? 'bg-emerald-500' : 'bg-sky-500' }}"
                                                         style="width: {{ $percent }}%"></div>
                                                </div>
                                                <span class="text-xs text-zinc-400 font-medium min-w-fit">{{ $percent }}%</span>
                                            </div>

                                            {{-- Items --}}
                                            <div class="space-y-2">
                                                @foreach($checklist->items as $item)
                                                    <div
                                                        class="flex items-start gap-3 group/item px-2 py-2 rounded-lg hover:bg-zinc-700/30 transition-colors"
                                                        x-data="{ editing: false, content: @js($item->content) }">

                                                        {{-- Checkbox --}}
                                                        <button wire:click="toggleChecklistItem({{ $item->id }})"
                                                                class="mt-0.5 shrink-0 w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all
                                            {{ $item->is_checked
                                                ? 'bg-emerald-600 border-emerald-600'
                                                : 'border-zinc-600 hover:border-sky-400 hover:bg-zinc-700/30' }}">
                                                            @if($item->is_checked)
                                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                                          d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            @endif
                                                        </button>

                                                        {{-- Content --}}
                                                        <div class="flex-1 min-w-0">
                                                        <span x-show="!editing"
                                                              @click="editing = true"
                                                              class="text-sm cursor-pointer leading-snug block
                                                                   {{ $item->is_checked ? 'line-through text-zinc-500' : 'text-zinc-300 hover:text-white' }}
                                                                   transition-colors">
                                                            {{ $item->content }}
                                                        </span>
                                                            <div x-show="editing" class="flex items-center gap-2">
                                                                <input x-model="content"
                                                                       x-init="$watch('editing', v => v && $nextTick(() => $el.focus()))"
                                                                       @keydown.enter="editing = false; $wire.updateChecklistItemContent({{ $item->id }}, content)"
                                                                       @keydown.escape="editing = false; content = @js($item->content)"
                                                                       @blur="editing = false; $wire.updateChecklistItemContent({{ $item->id }}, content)"
                                                                       class="flex-1 bg-zinc-800 border-2 border-sky-500 rounded-lg px-2.5 py-1 text-sm focus:outline-none text-white">
                                                            </div>
                                                        </div>

                                                        {{-- Delete item --}}
                                                        <button wire:click="deleteChecklistItem({{ $item->id }})"
                                                                class="opacity-0 group-hover/item:opacity-100 transition-opacity shrink-0 text-zinc-600 hover:text-red-400 mt-0.5">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach

                                                {{-- Add item --}}
                                                <div x-data="{ adding: false }"
                                                     class="pt-1">
                                                    <div x-show="!adding">
                                                        <button @click="adding = true"
                                                                class="flex items-center gap-2 text-xs text-zinc-500 hover:text-zinc-300 px-2 py-1.5 rounded-lg transition-colors hover:bg-zinc-700/20">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            Add an item
                                                        </button>
                                                    </div>
                                                    <div x-show="adding" x-cloak class="flex items-center gap-2 mt-2">
                                                        <input wire:model="newItemContent.{{ $checklist->id }}"
                                                               x-init="$watch('adding', v => v && $nextTick(() => $el.focus()))"
                                                               @keydown.enter="$wire.addChecklistItem({{ $checklist->id }})"
                                                               @keydown.escape="adding = false"
                                                               placeholder="Add an item..."
                                                               class="flex-1 bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-2.5 py-1.5 text-sm placeholder-zinc-600 focus:outline-none text-white transition-colors">
                                                        <button wire:click="addChecklistItem({{ $checklist->id }})" @click="adding = false"
                                                                class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all">
                                                            ✓
                                                        </button>
                                                        <button @click="adding = false"
                                                                class="text-zinc-500 hover:text-zinc-300 px-1 py-1.5 text-xs transition-colors">
                                                            ✕
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif


                            {{-- Comments --}}
                            <div>
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Activity</p>
                                </div>

                                <div class="flex gap-3 mb-5 p-4 bg-zinc-800/50 border border-zinc-700 rounded-lg">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 space-y-2">
                            <textarea wire:model="newComment"
                                      rows="3"
                                      placeholder="Write a comment..."
                                      class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-4 py-2.5 text-sm placeholder-zinc-600 focus:outline-none transition-colors resize-none text-white"></textarea>
                                        <button wire:click="addComment"
                                                class="bg-sky-600 hover:bg-sky-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-sm hover:shadow-md">
                                            Comment
                                        </button>
                                    </div>
                                </div>

                                @foreach($card->comments as $comment)
                                    <div class="flex gap-3 mb-4">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ substr($comment->user->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-baseline gap-2 mb-1">
                                                <span class="text-sm font-semibold text-white">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg p-3">
                                                <p class="text-sm text-zinc-300 whitespace-pre-wrap">{{ $comment->body }}</p>
                                            </div>
                                            @if($comment->user_id === auth()->id())
                                                <button wire:click="deleteComment({{ $comment->id }})"
                                                        class="text-xs text-zinc-600 hover:text-red-400 transition-colors mt-2 font-medium">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Activity Log --}}
                            <div class="pt-6 border-t border-zinc-700">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">History</p>
                                </div>
                                <div class="space-y-3">
                                    @foreach($card->activities->take(8) as $activity)
                                        <div class="flex gap-3 text-xs">
                                            <div
                                                class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center text-[10px] font-semibold flex-shrink-0">
                                                {{ substr($activity->user->name, 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-zinc-400">
                                                    <span class="font-semibold text-zinc-300">{{ $activity->user->name }}</span>
                                                    <span class="text-zinc-500">{{ $activity->content }}</span>
                                                </p>
                                                <p class="text-zinc-600 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
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
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
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
                                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
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
                                                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Members --}}
                            <div x-data="{ open: false, search: '' }">
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Members</p>

                                {{-- Assigned avatars --}}
                                @if($card->members->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                        @foreach($card->members as $member)
                                            <div class="relative group/avatar">
                                                <div
                                                    class="w-7 h-7 rounded-full bg-linear-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-bold cursor-pointer border-2 border-zinc-900 ring-1 ring-zinc-700 hover:ring-red-500/60 transition-all"
                                                    title="{{ $member->name }} — click to remove"
                                                    wire:click="toggleMember({{ $member->id }})">
                                                    {{ substr($member->name, 0, 1) }}
                                                </div>
                                                <div
                                                    class="absolute -top-8 left-1/2 -translate-x-1/2 bg-zinc-800 border border-zinc-700 text-[10px] text-zinc-300 px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover/avatar:opacity-100 transition-opacity pointer-events-none z-20 shadow-xl">
                                                    {{ $member->name }} · remove
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Assign button --}}
                                <button @click="open = !open; $nextTick(() => open && $refs.memberSearch.focus())"
                                        class="w-full flex items-center gap-2 px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 hover:border-zinc-600 rounded-lg text-xs text-zinc-300 transition-colors {{ $card->members->isEmpty() ? '' : 'mt-1' }}">
                                    <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ $card->members->isEmpty() ? 'Assign members' : 'Edit members' }}</span>
                                </button>

                                {{-- Dropdown picker --}}
                                <div x-show="open"
                                     @click.outside="open = false; search = ''"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="mt-1.5 bg-zinc-800 border border-zinc-700 rounded-xl overflow-hidden shadow-2xl z-20">

                                    {{-- Search box --}}
                                    <div class="p-2 border-b border-zinc-700/60">
                                        <div class="flex items-center gap-2 bg-zinc-900 border border-zinc-700 rounded-lg px-2.5 py-1.5">
                                            <svg class="w-3.5 h-3.5 text-zinc-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <input x-ref="memberSearch"
                                                   x-model="search"
                                                   placeholder="Search board members..."
                                                   class="bg-transparent text-xs placeholder-zinc-600 focus:outline-none flex-1 min-w-0">
                                            <button x-show="search !== ''" @click="search = ''"
                                                    class="text-zinc-600 hover:text-zinc-400 text-xs">✕
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Member rows --}}
                                    <div class="max-h-52 overflow-y-auto">
                                        @forelse($this->boardMembers as $member)
                                            @php $isAssigned = $card->members->contains('id', $member->id); @endphp
                                            <button wire:click="toggleMember({{ $member->id }})"
                                                    x-show="search === '' || '{{ strtolower($member->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($member->email) }}'.includes(search.toLowerCase())"
                                                    class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-zinc-700/70 transition-colors text-left group/row">

                                                {{-- Avatar with ring when assigned --}}
                                                <div class="relative shrink-0">
                                                    <div class="w-7 h-7 rounded-full bg-linear-to-br from-sky-400 to-violet-500 flex items-center justify-center text-xs font-bold
                                                {{ $isAssigned ? 'ring-2 ring-emerald-500 ring-offset-1 ring-offset-zinc-800' : '' }}">
                                                        {{ substr($member->name, 0, 1) }}
                                                    </div>
                                                    @if($isAssigned)
                                                        <div
                                                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full flex items-center justify-center">
                                                            <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5"
                                                                      d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Name + email --}}
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium truncate {{ $isAssigned ? 'text-white' : 'text-zinc-300' }}">
                                                        {{ $member->name }}
                                                    </p>
                                                    <p class="text-[10px] text-zinc-500 truncate">{{ $member->email }}</p>
                                                </div>

                                                {{-- Action label --}}
                                                <span class="text-[10px] opacity-0 group-hover/row:opacity-100 transition-opacity shrink-0
                                            {{ $isAssigned ? 'text-red-400' : 'text-sky-400' }}">
                                            {{ $isAssigned ? 'Remove' : 'Assign' }}
                                        </span>
                                            </button>
                                        @empty
                                            <div class="px-3 py-6 text-center">
                                                <p class="text-xs text-zinc-600">No members on this board yet</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Checklist --}}
                            <div x-data="{ open: false }">
                                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Checklist</p>
                                <button @click="open = !open"
                                        class="w-full flex items-center gap-2 px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 hover:border-zinc-600 rounded-lg text-xs text-zinc-300 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
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
                                        <button wire:click="setCoverColor(`{{ $clr }}`)"
                                                class="h-7 rounded {{ !$clr ? 'bg-zinc-700 border-2 border-dashed border-zinc-600' : '' }} {{ $coverColor === $clr ? 'ring-2 ring-white ring-offset-1 ring-offset-zinc-900' : '' }}"
                                                style="{{ $clr ? 'background-color:' . $clr : '' }}"
                                                title="{{ $clr ?: 'No cover' }}">
                                            @if(!$clr)
                                                <span class="text-zinc-500 text-xs">✕</span>
                                            @endif
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
                                    <svg class="w-5 h-5 text-zinc-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
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
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                    Archive card
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
