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
                <p class="text-sm text-zinc-400 mt-0.5">
                    @if($workspace_id)
                        Adding to <strong class="text-zinc-200">{{ $this->workspaces->firstWhere('id', $workspace_id)?->name }}</strong>
                    @else
                        Set up a workspace for your project
                    @endif
                </p>
            </div>

            <form wire:submit="createBoard" class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Workspace <span class="text-red-400">*</span></label>
                    <select wire:model="workspace_id"
                            class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-sky-500 transition-colors">
                        <option value="">Select a workspace…</option>
                        @foreach($this->workspaces as $ws)
                            <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                        @endforeach
                    </select>
                </div>

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
                @foreach(['#0ea5e9','#8b5cf6','#ec4899','#ef4444','#f97316','#10b981','#f59e0b','#6366f1'] as $clr)
                    <button type="button"
                            wire:click="setColor('{{ $clr }}')"
                            class="w-7 h-7 rounded-full border-2 transition-all {{ $color === $clr ? 'border-white scale-110' : 'border-transparent' }}"
                            style="background-color: {{ $clr }}">
                    </button>
                @endforeach

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
                        <span wire:loading wire:target="createBoard">Creating…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
