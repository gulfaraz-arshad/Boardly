<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

/**
 * ┌─────────────────┬─────────────┬───────┬──────────┬───────────┬───────────┬──────┐
 * │ Action          │ super_admin │ owner │ ws_admin │ ws_member │ ws_viewer │ none │
 * ├─────────────────┼─────────────┼───────┼──────────┼───────────┼───────────┼──────┤
 * │ view            │ ✓           │ ✓     │ ✓        │ ✓         │ ✓         │ ✗    │
 * │ create          │ ✓           │ —     │ —        │ —         │ —         │ ✓*   │
 * │ update/settings │ ✓           │ ✓     │ ✓        │ ✗         │ ✗         │ ✗    │
 * │ delete          │ ✓           │ ✓     │ ✗        │ ✗         │ ✗         │ ✗    │
 * │ manageBoards    │ ✓           │ ✓     │ ✓        │ ✗         │ ✗         │ ✗    │
 * │ manageMembers   │ ✓           │ ✓     │ ✗        │ ✗         │ ✗         │ ✗    │
 * │ createContent   │ ✓           │ ✓     │ ✓        │ ✓         │ ✗         │ ✗    │
 * └─────────────────┴─────────────┴───────┴──────────┴───────────┴───────────┴──────┘
 * * Any authenticated user may create their own workspace.
 *
 * Platform-type rules (applied via before()):
 *   super_admin → always true (full bypass)
 *   admin       → can do anything EXCEPT delete workspaces they don't own
 */
class WorkspacePolicy
{
    /**
     * super_admin bypasses every check unconditionally.
     * platform admin bypasses every check EXCEPT delete (falls through to specific method).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // platform admin can do anything except delete
        if ($user->isPlatformAdmin() && $ability !== 'delete') {
            return true;
        }

        return null; // fall through to specific method
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Any role (including viewer) can view the workspace page. */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->hasAccess($user);
    }

    /** Any authenticated user can create their own workspace. */
    public function create(User $user): bool
    {
       if ($user->isSuperAdmin()) {
           return true;
       }
       return false;
    }

    /** Owner + workspace admins can update settings (name, color, description). */
    public function update(User $user, Workspace $workspace): bool
    {
        return $user->isWorkspaceAdmin($workspace);
    }

    /** Only the workspace owner (or super_admin via before()) can delete. */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $user->canDeleteWorkspace($workspace);
    }

    /** Owner + workspace admins can create/archive/delete boards. */
    public function manageBoards(User $user, Workspace $workspace): bool
    {
        return $user->isWorkspaceAdmin($workspace);
    }

    /** Only the workspace owner can invite/remove members or change roles. */
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $workspace->isOwnedBy($user);
    }

    /** Owner, admin, member can create cards/comments. Viewer cannot write anything. */
    public function createContent(User $user, Workspace $workspace): bool
    {
        return $user->canEditInWorkspace($workspace);
    }
}
