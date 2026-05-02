@props([
    'filterLabel',
    'showLabelManager',
    'editingLabelId',
    'labelColor',
    'labelName',
    'labelPalette',
    'labels',
])

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            class="flex items-center gap-1.5 bg-zinc-900 border border-zinc-800 hover:border-zinc-600 rounded-lg px-3 py-2 text-sm transition-colors
                   {{ $filterLabel ? 'border-sky-500 text-sky-400' : 'text-zinc-300' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
        </svg>
        Labels
        @if($filterLabel)
            <span class="w-2 h-2 rounded-full bg-sky-400 shrink-0"></span>
        @endif
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute top-full mt-2 right-0 w-72 bg-[#282e33] border border-[#374048] rounded-xl shadow-2xl z-30 overflow-hidden">

        @if(!$showLabelManager)
            {{-- ── List view ─────────────────────────────────────── --}}
            <div class="p-3">
                <p class="text-[11px] font-semibold text-[#8c9bab] uppercase tracking-wider text-center mb-3">Labels</p>

                {{-- All / clear filter --}}
                <button wire:click="$set('filterLabel', null)"
                        class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-[#ffffff14] transition-colors mb-2">
                    <span class="w-8 h-8 rounded bg-[#374048] flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 {{ !$filterLabel ? 'text-[#579dff]' : 'text-transparent' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="text-sm text-[#b6c2cf] font-medium">All labels</span>
                </button>

                {{-- Label rows --}}
                <div class="space-y-1 max-h-60 overflow-y-auto">
                    @forelse($labels as $label)
                        <div class="flex items-center gap-2 group">
                            <button wire:click="$set('filterLabel', {{ $label->id }})"
                                    class="flex-1 h-8 rounded-lg flex items-center px-2.5 gap-2 font-semibold text-sm text-white hover:brightness-110 transition-all"
                                    style="background-color: {{ $label->color }}">
                                @if($filterLabel == $label->id)
                                    <svg class="w-3.5 h-3.5 text-white/90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                                <span class="truncate">{{ $label->name }}</span>
                            </button>

                            <button wire:click="startEditLabel({{ $label->id }})"
                                    class="opacity-0 group-hover:opacity-100 w-7 h-7 flex items-center justify-center rounded-lg bg-[#374048] hover:bg-[#4a5568] transition-all shrink-0">
                                <svg class="w-3.5 h-3.5 text-[#8c9bab]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-[#596773] text-center py-2">No labels yet</p>
                    @endforelse
                </div>

                <div class="border-t border-[#374048] mt-3 pt-3">
                    <button wire:click="$set('showLabelManager', true)"
                            class="w-full py-1.5 rounded-lg bg-[#374048] hover:bg-[#4a5568] text-sm text-[#b6c2cf] font-medium transition-colors">
                        Create a new label
                    </button>
                </div>
            </div>

        @else
            {{-- ── Create / Edit view ───────────────────────────── --}}
            <div class="p-3">
                {{-- Header with back button --}}
                <div class="flex items-center mb-4">
                    <button wire:click="resetLabelForm"
                            class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-[#374048] transition-colors text-[#8c9bab] mr-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <p class="text-[11px] font-semibold text-[#8c9bab] uppercase tracking-wider flex-1 text-center pr-7">
                        {{ $editingLabelId ? 'Edit label' : 'Create label' }}
                    </p>
                </div>

                {{-- Live preview --}}
                <div class="mb-4 px-1">
                    <div class="h-8 rounded-lg w-full flex items-center px-3 font-semibold text-sm text-white"
                         style="background-color: {{ $labelColor ?: '#374048' }}">
                        {{ $labelName ?: ' ' }}
                    </div>
                </div>

                {{-- Title --}}
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-[#8c9bab] mb-1.5 uppercase tracking-wider">Title</label>
                    <input wire:model.live="labelName"
                           placeholder="Label title…"
                           class="w-full bg-[#22272b] border border-[#374048] focus:border-[#579dff] rounded-lg px-3 py-2 text-sm text-[#b6c2cf] placeholder-[#596773] focus:outline-none transition-colors">
                </div>

                {{-- Color palette --}}
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-[#8c9bab] mb-2 uppercase tracking-wider">Color</label>
                    <div class="grid grid-cols-5 gap-1.5 mb-2">
                        @foreach($labelPalette as $clr)
                            <button type="button"
                                    wire:click="$set('labelColor', '{{ $clr }}')"
                                    class="h-8 rounded-lg relative transition-all hover:brightness-110 {{ $labelColor === $clr ? 'ring-2 ring-white ring-offset-1 ring-offset-[#282e33]' : '' }}"
                                    style="background-color: {{ $clr }}">
                                @if($labelColor === $clr)
                                    <svg class="w-3.5 h-3.5 text-white absolute inset-0 m-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Custom hex --}}
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg shrink-0 border border-[#374048] transition-colors"
                             style="background-color: {{ $labelColor }}"></div>
                        <input wire:model.live="labelColor"
                               placeholder="#hex"
                               maxlength="7"
                               class="flex-1 bg-[#22272b] border border-[#374048] focus:border-[#579dff] rounded-lg px-3 py-1.5 text-sm text-[#b6c2cf] placeholder-[#596773] focus:outline-none font-mono transition-colors">
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-2 pt-1">
                    @if($editingLabelId)
                        <button wire:click="updateLabel"
                                class="flex-1 py-1.5 rounded-lg bg-[#579dff] hover:bg-[#388bff] text-white text-sm font-semibold transition-colors">
                            Save
                        </button>
                        <button wire:click="deleteLabel({{ $editingLabelId }})"
                                wire:confirm="Delete this label? It will be removed from all cards."
                                class="py-1.5 px-3 rounded-lg bg-[#f87168] hover:bg-[#e05c52] text-white text-sm font-semibold transition-colors">
                            Delete
                        </button>
                    @else
                        <button wire:click="createLabel"
                                class="flex-1 py-1.5 rounded-lg bg-[#579dff] hover:bg-[#388bff] text-white text-sm font-semibold transition-colors">
                            Create
                        </button>
                    @endif

                    <button wire:click="resetLabelForm"
                            class="py-1.5 px-3 rounded-lg bg-[#374048] hover:bg-[#4a5568] text-[#b6c2cf] text-sm font-semibold transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
