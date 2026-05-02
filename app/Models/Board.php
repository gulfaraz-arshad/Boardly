<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'name',
        'description',
        'color',
        'cover_image',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lists(): HasMany
    {
        return $this->hasMany(BoardList::class)
                    ->where('is_archived', false)
                    ->orderBy('position');
    }

    public function archivedLists(): HasMany
    {
        return $this->hasMany(BoardList::class)->where('is_archived', true);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        // Uses BoardInvitation — not WorkspaceInvitation
        return $this->hasMany(BoardInvitation::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, BoardList::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /**
     * Boards visible to a user:
     *  - boards they own
     *  - boards they are an explicit member of
     *  - boards whose workspace they belong to
     *  - public boards
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('members', fn($m) => $m->where('users.id', $user->id))
              ->orWhereHas('workspace', fn($w) =>
              $w->whereHas('members', fn($wm) => $wm->where('users.id', $user->id))
                ->orWhere('workspaces.user_id', $user->id)
              )
              ->orWhere('is_public', true);
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Get explicit board role from pivot only.
     * Does NOT include owner fallback — use User::boardRole() for that.
     */
    public function getMemberRole(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner';
        }

        return $this->members()
                    ->where('users.id', $user->id)
                    ->value('board_members.role');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->isOwnedBy($user)
               || $this->members()->where('users.id', $user->id)->exists();
    }

    public function getNextListPosition(): int
    {
        return ($this->lists()->max('position') ?? -1) + 1;
    }
}
