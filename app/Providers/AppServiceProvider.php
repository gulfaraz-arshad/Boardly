<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Workspace;
use App\Policies\BoardPolicy;
use App\Policies\CardPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All model → policy mappings for the application.
     *
     * Laravel auto-discovers policies following the naming convention
     * (App\Models\Foo → App\Policies\FooPolicy), but we register them
     * explicitly here for clarity and to avoid any discovery misses.
     */
    protected $policies = [
        Workspace::class => WorkspacePolicy::class,
        Board::class     => BoardPolicy::class,
        Card::class       => CardPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ── Platform-level gates ─────────────────────────────────

        /**
         * access-admin-panel
         * Only super_admin and admin types can access any admin UI.
         */
        Gate::define('access-admin-panel', function ($user) {
            return $user->isPlatformAdmin();
        });

        /**
         * manage-all-workspaces
         * Super admins can see and manage every workspace on the platform.
         */
        Gate::define('manage-all-workspaces', function ($user) {
            return $user->isSuperAdmin();
        });

        /**
         * impersonate-user
         * Super admins can log in as any other user for support purposes.
         */
        Gate::define('impersonate-user', function ($user) {
            return $user->isSuperAdmin();
        });
    }
}
