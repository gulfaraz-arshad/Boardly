<div class="min-h-full bg-zinc-950">

    {{-- Header --}}
    <div class="border-b border-zinc-800 bg-zinc-900/50 px-6 py-5">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('workspaces.index') }}" wire:navigate
                   class="text-zinc-500 hover:text-zinc-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-zinc-600">/</span>
                <span class="text-sm text-zinc-400">{{ $workspace->name }}</span>
                <span class="text-zinc-600">/</span>
                <span class="text-sm text-zinc-300">Settings</span>
            </div>

            <div class="flex items-center gap-3 mt-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base font-bold text-white shrink-0"
                     style="background-color: {{ $workspace->color ?? '#0ea5e9' }}">
                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">{{ $workspace->name }}</h1>
                    <p class="text-sm text-zinc-500">
                        {{ $boardCount }} {{ Str::plural('board', $boardCount) }}
                        &middot; Created {{ $workspace->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-6 py-8">
        <div class="flex gap-8">

            {{-- Sidebar nav --}}
            <nav class="w-44 shrink-0 space-y-0.5">
                @foreach([
                    ['tab' => 'general',  'label' => 'General',  'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['tab' => 'danger',   'label' => 'Danger zone', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ] as $nav)
                    <button wire:click="$set('activeTab', '{{ $nav['tab'] }}')"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors text-left
                                   {{ $activeTab === $nav['tab']
                                       ? ($nav['tab'] === 'danger' ? 'bg-red-500/10 text-red-400' : 'bg-zinc-800 text-white')
                                       : ($nav['tab'] === 'danger' ? 'text-zinc-500 hover:text-red-400 hover:bg-red-500/5' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60') }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $nav['icon'] }}"/>
                        </svg>
                        {{ $nav['label'] }}
                    </button>
                @endforeach
            </nav>

            {{-- Main panel --}}
            <div class="flex-1 min-w-0">

                {{-- ── General ──────────────────────────────────────────── --}}
                @if($activeTab === 'general')
                    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-zinc-800">
                            <h2 class="font-semibold">General</h2>
                            <p class="text-sm text-zinc-500 mt-0.5">Update your workspace name, description and color.</p>
                        </div>

                        <form wire:submit="saveGeneral" class="p-6 space-y-5">
                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium mb-1.5">
                                    Workspace name <span class="text-red-400">*</span>
                                </label>
                                <input wire:model="name"
                                       class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 transition-colors">
                                @error('name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Description</label>
                                <textarea wire:model="description" rows="3"
                                          placeholder="What is this workspace for?"
                                          class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-500 focus:outline-none focus:border-sky-500 transition-colors resize-none"></textarea>
                                @error('description')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Color --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Workspace color</label>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @foreach(['#0ea5e9','#8b5cf6','#ec4899','#ef4444','#f97316','#10b981','#f59e0b','#6366f1'] as $clr)
                                        <button type="button"
                                                wire:click="$set('color', '{{ $clr }}')"
                                                class="w-7 h-7 rounded-full border-2 transition-all {{ $color === $clr ? 'border-white scale-110' : 'border-transparent hover:scale-105' }}"
                                                style="background-color: {{ $clr }}">
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Live preview --}}
                                <div class="mt-4 flex items-center gap-3 p-3 bg-zinc-800/50 rounded-lg border border-zinc-700/50">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-white shrink-0 transition-colors"
                                         style="background-color: {{ $color }}">
                                        {{ strtoupper(substr($name ?: $workspace->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ $name ?: $workspace->name }}</p>
                                        <p class="text-xs text-zinc-500">Preview</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button type="submit"
                                        class="flex items-center gap-2 bg-sky-500 hover:bg-sky-400 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-sky-500/20">
                                    <span wire:loading.remove wire:target="saveGeneral">Save changes</span>
                                    <span wire:loading wire:target="saveGeneral">Saving…</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- ── Danger zone ──────────────────────────────────────── --}}
                @if($activeTab === 'danger')
                    <div class="bg-zinc-900 border border-red-500/20 rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-red-500/20 bg-red-500/5">
                            <h2 class="font-semibold text-red-400">Danger zone</h2>
                            <p class="text-sm text-zinc-500 mt-0.5">These actions are permanent and cannot be undone.</p>
                        </div>

                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-white">Delete this workspace</p>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        Permanently deletes <strong class="text-zinc-300">{{ $workspace->name }}</strong>
                                        and all <strong class="text-zinc-300">{{ $boardCount }} {{ Str::plural('board', $boardCount) }}</strong>
                                        inside it. This cannot be undone.
                                    </p>
                                </div>
                                <button wire:click="$set('showDeleteModal', true)"
                                        class="shrink-0 px-4 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 rounded-lg text-sm font-medium transition-colors">
                                    Delete workspace
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Delete confirmation modal --}}
    @if($showDeleteModal)
        <div x-data x-show="$wire.showDeleteModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="$wire.set('showDeleteModal', false)">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                 wire:click="$set('showDeleteModal', false)"></div>

            <div class="relative bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="p-6 border-b border-zinc-800">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold">Delete workspace</h2>
                    </div>
                    <p class="text-sm text-zinc-400 mt-2">
                        This will permanently delete <strong class="text-white">{{ $workspace->name }}</strong>
                        and all {{ $boardCount }} {{ Str::plural('board', $boardCount) }} inside it.
                    </p>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm text-zinc-400 mb-1.5">
                            Type <strong class="text-white font-mono">{{ $workspace->name }}</strong> to confirm
                        </label>
                        <input wire:model="deleteConfirmName"
                               placeholder="{{ $workspace->name }}"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm placeholder-zinc-600 focus:outline-none focus:border-red-500 transition-colors font-mono">
                        @error('deleteConfirmName')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="flex-1 px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteWorkspace"
                                class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-400 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-40"
                                @if($deleteConfirmName !== $workspace->name) disabled @endif>
                            <span wire:loading.remove wire:target="deleteWorkspace">Delete permanently</span>
                            <span wire:loading wire:target="deleteWorkspace">Deleting…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
