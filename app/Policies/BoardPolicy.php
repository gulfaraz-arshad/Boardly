<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

/**
 * ┌─────────────────┬─────────────┬───────┬───────┬────────┬────────┬────────┐
 * │ Action          │ super_admin │ owner │ admin │ member │ viewer │ public │
 * ├─────────────────┼─────────────┼───────┼───────┼────────┼────────┼────────┤
 * │ view            │ ✓           │ ✓     │ ✓     │ ✓      │ ✓      │ ✓      │
 * │ update settings │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │ ✗      │
 * │ delete          │ ✓           │ ✓     │ ✗     │ ✗      │ ✗      │ ✗      │
 * │ manageMembers   │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │ ✗      │
 * │ createList      │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │ ✗      │
 * │ archiveList     │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │ ✗      │
 * │ createCard      │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │ ✗      │
 * │ editCard        │ ✓           │ ✓     │ ✓     │ ✓*     │ ✗      │ ✗      │
 * │ deleteCard      │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │ ✗      │
 * │ moveCard        │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │ ✗      │
 * │ comment         │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │ ✗      │
 * │ addChecklist    │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │ ✗      │
 * │ uploadAttach.   │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │ ✗      │
 * └─────────────────┴─────────────┴───────┴───────┴────────┴────────┴────────┘
 * * member can edit card title/description/due-date/labels ONLY on cards they created.
 *   owner/admin can edit ANY card on the board.
 *
 * Platform-type rules (applied via before()):
 *   super_admin → always true
 *   admin       → can do anything EXCEPT delete boards they don't own
 */
class BoardPolicy
{
    /**
     * super_admin bypasses every check.
     * platform admin bypasses every check EXCEPT delete (falls through).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isPlatformAdmin() && $ability !== 'delete') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Any role or public board. */
    public function view(User $user, Board $board): bool
    {
        return $user->canViewBoard($board);
    }

    /** Any authenticated user can create a board. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Change board name, color, description — owner or admin. */
    public function update(User $user, Board $board): bool
    {
        return $user->canAdminBoard($board);
    }

    /** Hard delete — only board owner (or super_admin via before()). */
    public function delete(User $user, Board $board): bool
    {
        return $board->user_id === $user->id;
    }

    /** Invite/remove board members — owner or admin. */
    public function manageMembers(User $user, Board $board): bool
    {
        return $user->canAdminBoard($board);
    }

    /**
     * Create new lists — owner or admin only.
     * Members work within existing lists; they cannot add columns.
     */
    public function createList(User $user, Board $board): bool
    {
        return $user->canAdminBoard($board);
    }

    /**
     * Archive/restore lists — owner or admin only.
     */
    public function archiveList(User $user, Board $board): bool
    {
        return $user->canAdminBoard($board);
    }

    /** Create cards — owner, admin, member. */
    public function createCard(User $user, Board $board): bool
    {
        return $user->canEditBoard($board);
    }

    /** Move / reorder cards across lists — owner, admin, member. */
    public function moveCard(User $user, Board $board): bool
    {
        return $user->canEditBoard($board);
    }

    /** Add comments — owner, admin, member. */
    public function comment(User $user, Board $board): bool
    {
        return $user->canEditBoard($board);
    }

    /** Add/remove checklist items — owner, admin, member. */
    public function addChecklist(User $user, Board $board): bool
    {
        return $user->canEditBoard($board);
    }

    /** Upload attachments — owner, admin, member. */
    public function uploadAttachment(User $user, Board $board): bool
    {
        return $user->canEditBoard($board);
    }
}
