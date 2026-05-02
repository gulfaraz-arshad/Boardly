<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Workspace extends Model
{

    protected $fillable = ['user_id', 'name', 'description', 'color'];

    // ─── Relationships ────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Non-deleted boards inside this workspace.
     * Soft-deleted boards are excluded via the Board model's SoftDeletes scope.
     */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class)->orderBy('name');
    }

    /**
     * Explicit workspace members (excludes the owner row — owner is stored
     * as user_id on the workspace itself, not in this pivot).
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    /**
     * Owner + all explicit members as a flat Collection.
     */
    public function allMembers()
    {
        $memberIds = $this->members()->pluck('users.id')->push($this->user_id)->unique();
        return User::whereIn('id', $memberIds)->orderBy('name')->get();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Get a user's role in this workspace.
     * Returns: 'owner' | 'admin' | 'member' | 'viewer' | null
     */
    public function getMemberRole(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner';
        }

        return $this->members()
                    ->where('users.id', $user->id)
                    ->value('workspace_members.role');
    }

    /** True if the user has ANY role in this workspace. */
    public function hasAccess(User $user): bool
    {
        return $this->getMemberRole($user) !== null;
    }
}
