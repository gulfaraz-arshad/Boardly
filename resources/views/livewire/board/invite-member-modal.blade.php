<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="$wire.set('isOpen', false)">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('isOpen', false)"></div>

            <div class="relative bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-md"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="p-5 border-b border-zinc-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">Board members</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Invite people to collaborate</p>
                    </div>
                    <button wire:click="$set('isOpen', false)" class="text-zinc-500 hover:text-zinc-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Invite form --}}
                <div class="p-5 border-b border-zinc-800">
                    @if($success && $message)
                        <div class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-sm text-emerald-400">
                            {{ $message }}
                        </div>
                    @endif

                    <form wire:submit="invite" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-400 mb-1.5">Email address</label>
                            <input wire:model="email" type="email" placeholder="colleague@company.com"
                                   class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-3 py-2 text-sm placeholder-zinc-600 focus:outline-none transition-colors">
                            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-400 mb-1.5">Role</label>
                            <select wire:model="role"
                                    class="w-full bg-zinc-800 border border-zinc-700 focus:border-sky-500 rounded-lg px-3 py-2 text-sm focus:outline-none transition-colors">
                                <option value="member">Member — can create and edit cards</option>
                                <option value="admin">Admin — can manage lists and members</option>
                                <option value="viewer">Viewer — read only access</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full bg-sky-500 hover:bg-sky-400 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                            <span wire:loading.remove wire:target="invite">Send invitation</span>
                            <span wire:loading wire:target="invite">Sending...</span>
                        </button>
                    </form>
                </div>

                {{-- Current members --}}
                <div class="p-5 max-h-60 overflow-y-auto">
                    <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-3">Current members</p>
                    <div class="space-y-3">
                        @foreach($members as $member)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-400 to-violet-500 flex items-center justify-center text-sm font-semibold shrink-0">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $member->name }}</p>
                                    <p class="text-xs text-zinc-500 truncate">{{ $member->email }}</p>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $member->pivot->role === 'owner' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' :
                           ($member->pivot->role === 'admin' ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' :
                           'bg-zinc-700 text-zinc-400') }}">
                        {{ ucfirst($member->pivot->role) }}
                    </span>
                                @if($member->pivot->role !== 'owner' && auth()->id() !== $member->id)
                                    <button wire:click="removeMember({{ $member->id }})"
                                            wire:confirm="Remove {{ $member->name }} from this board?"
                                            class="text-zinc-600 hover:text-red-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
