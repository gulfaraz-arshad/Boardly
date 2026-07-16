<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth.split')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
                                         'name' => ['required', 'string', 'max:255'],
                                         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                                         'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
                                     ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        Auth::login($user);

        $this->redirect(route('workspaces.index', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-8">
    <x-auth-header title="Create an account" description="Enter your details below to create your account" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-5">
        <!-- Name -->
        <flux:input
            wire:model="name"
            id="name"
            label="{{ __('Name') }}"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="name"
            placeholder="Full name"
            :error="$errors->has('name')"
            :description="$errors->first('name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            id="email"
            label="{{ __('Email address') }}"
            type="email"
            name="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
            :error="$errors->has('email')"
            :description="$errors->first('email')"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            id="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="Password"
            :error="$errors->has('password')"
            :description="$errors->first('password')"
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            id="password_confirmation"
            label="{{ __('Confirm password') }}"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            placeholder="Confirm password"
            :error="$errors->has('password_confirmation')"
            :description="$errors->first('password_confirmation')"
        />

        <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('Create account') }}</span>
            <span wire:loading class="flex items-center justify-center gap-2">
                <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Creating account...') }}
            </span>
        </flux:button>
    </form>

    <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <x-text-link href="{{ route('login') }}">{{ __('Log in') }}</x-text-link>
    </div>
</div>
