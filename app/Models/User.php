<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Platform-level roles:
 *   super_admin — bypasses every policy gate
 *   admin       — can do anything EXCEPT hard-delete owned resources (boards/workspaces they don't own)
 *   member      — regular user, workspace/board roles determine what they can do
 *
 * Workspace-level roles (workspace_members pivot):
 *   owner  — created the workspace; full control including delete
 *   admin  — manage boards, lists, members; cannot delete workspace
 *   member — create/edit cards, comments, checklists, attachments; cannot manage lists/members
 *   viewer — read-only; no writes at all
 *
 * Board-level roles (board_members pivot):
 *   owner  — created the board; full control including delete
 *   admin  — manage lists, members, board settings; cannot delete board
 *   member — create/edit own cards, move cards, add comments/checklists/attachments
 *   viewer — read-only; no writes at all
 *
 * Role inheritance:
 *   If no explicit board role → falls back to workspace role:
 *     workspace owner  → board admin
 *     workspace admin  → board admin
 *     workspace member → board member
 *     workspace viewer → board viewer
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    const TYPE_SUPER_ADMIN = 'super_admin';
    const TYPE_ADMIN       = 'admin';
    const TYPE_MEMBER      = 'member';

    protected $fillable = ['name', 'email', 'password', 'type'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    public function workspaceMemberships(): BelongsToMany
    {
        return $this
            ->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function boardMemberships(): BelongsToMany
    {
        return $this
            ->belongsToMany(Board::class, 'board_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // ─── Platform-level type helpers ─────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->type === self::TYPE_SUPER_ADMIN;
    }

    /** platform admin OR super_admin */
    public function isPlatformAdmin(): bool
    {
        return in_array($this->type, [self::TYPE_SUPER_ADMIN, self::TYPE_ADMIN]);
    }

    public function isMember(): bool
    {
        return $this->type === self::TYPE_MEMBER;
    }

    // ─── Workspace-level role helpers ─────────────────────────────

    /**
     * Returns: 'owner' | 'admin' | 'member' | 'viewer' | null
     * null means no access to this workspace at all.
     */
    public function workspaceRole(Workspace $workspace): ?string
    {
        if ($workspace->user_id === $this->id) {
            return 'owner';
        }

        return $this
            ->workspaceMemberships()
            ->where('workspace_id', $workspace->id)
            ->value('workspace_members.role');
    }

    public function isWorkspaceOwner(Workspace $workspace): bool
    {
        return $workspace->user_id === $this->id;
    }

    /** owner or admin */
    public function isWorkspaceAdmin(Workspace $workspace): bool
    {
        return in_array($this->workspaceRole($workspace), ['owner', 'admin']);
    }

    /** Any role set (including viewer) */
    public function isWorkspaceMember(Workspace $workspace): bool
    {
        return $this->workspaceRole($workspace) !== null;
    }

    /** owner, admin, member — NOT viewer */
    public function canEditInWorkspace(Workspace $workspace): bool
    {
        return in_array($this->workspaceRole($workspace), ['owner', 'admin', 'member']);
    }

    public function canViewWorkspace(Workspace $workspace): bool
    {
        return $this->workspaceRole($workspace) !== null;
    }

    // ─── Board-level role helpers ─────────────────────────────────

    /**
     * Resolves effective board role with workspace fallback.
     * Returns: 'owner' | 'admin' | 'member' | 'viewer' | null
     *
     * Resolution order:
     *  1. Board owner (user_id === board.user_id)       → 'owner'
     *  2. Explicit board_members pivot row              → that role
     *  3. Workspace membership (if board has workspace) → mapped role
     *  4. Public board (view-only)                      → null (handled by canViewBoard)
     *  5. No access                                     → null
     */
    public function boardRole(Board $board): ?string
    {
        // 1. Board owner
        if ($board->user_id === $this->id) {
            return 'owner';
        }

        // 3. Workspace fallback
        if ($board->workspace_id) {
            $wsRole = $this->workspaceRole($board->workspace);

            return match ($wsRole) {
                'owner', 'admin' => 'admin',
                'member' => 'member',
                'viewer' => 'viewer',
                default => null,
            };
        }

        return null;
    }

    /**
     * Can read/view board content.
     * Allows: any role, public boards, super_admin.
     */
    public function canViewBoard(Board $board): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($board->is_public) {
            return true;
        }

        return $this->boardRole($board) !== null;
    }

    /**
     * Can create cards, move cards, add comments, checklists, attachments.
     * Allows: owner, admin, member. Blocks: viewer.
     */
    public function canEditBoard(Board $board): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->boardRole($board), ['owner', 'admin']);
    }

    /**
     * Can manage board settings, lists, and members.
     * Allows: owner, admin. Blocks: member, viewer.
     */
    public function canAdminBoard(Board $board): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->boardRole($board), ['owner', 'admin']);
    }

    /**
     * Can delete the board entirely.
     * Only the board owner or super_admin.
     */
    public function canDeleteBoard(Board $board): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $board->user_id === $this->id;
    }

    /**
     * Can delete the workspace entirely.
     * Only the workspace owner or super_admin.
     */
    public function canDeleteWorkspace(Workspace $workspace): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return false;
    }
}
