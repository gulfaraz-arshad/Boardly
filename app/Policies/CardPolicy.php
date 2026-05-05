<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

/**
 * Card-level permissions.
 *
 * The board role drives most decisions:
 *   - Members can view and move any card, add comments, add checklists, upload attachments.
 *   - Members CANNOT edit (title, description, due date, labels, cover) any card.
 *   - Members CANNOT delete or archive any card (only owner/admin can).
 *
 * ┌───────────────────┬─────────────┬───────┬───────┬────────┬────────┐
 * │ Action            │ super_admin │ admin │ owner │ member │ viewer │
 * ├───────────────────┼─────────────┼───────┼───────┼────────┼────────┤
 * │ view              │ ✓           │ ✓     │ ✓     │ ✓      │ ✓      │
 * │ editDetails       │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │
 * │ move              │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │
 * │ comment           │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │
 * │ addChecklist      │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │
 * │ uploadAttachment  │ ✓           │ ✓     │ ✓     │ ✓      │ ✗      │
 * │ archive           │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │
 * │ delete            │ ✓           │ ✓     │ ✓     │ ✗      │ ✗      │
 * └───────────────────┴─────────────┴───────┴───────┴────────┴────────┘
 */
class CardPolicy
{
    /**
     * super_admin bypasses every check.
     * platform admin bypasses every check EXCEPT hard delete.
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

    public function view(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canViewBoard($card->list->board);
    }

    /**
     * Edit card details: title, description, due date, labels, cover color.
     * owner/admin only. Members CANNOT edit cards.
     */
    public function editDetails(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        $board = $card->list->board;

        // Only owner/admin can edit
        return $user->canAdminBoard($board);
    }

    /**
     * Move a card to another list or reorder.
     * Any member (owner, admin, member).
     */
    public function move(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canEditBoard($card->list->board);
    }

    /** Post a comment. */
    public function comment(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canEditBoard($card->list->board);
    }

    /** Add/edit/toggle checklist items. */
    public function addChecklist(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canEditBoard($card->list->board);
    }

    /** Upload an attachment. */
    public function uploadAttachment(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canEditBoard($card->list->board);
    }

    /**
     * Archive a card.
     * Owner/admin only. Members CANNOT archive cards.
     */
    public function archive(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        $board = $card->list->board;

        // Only owner/admin can archive
        return $user->canAdminBoard($board);
    }

    /**
     * Permanently delete a card.
     * Only owner or admin. Members cannot delete cards.
     */
    public function delete(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canAdminBoard($card->list->board);
    }

    /**
     * Assign/unassign a member to this card.
     * Board owner or admin only.
     */
    public function assignMember(User $user, Card $card): bool
    {
        if (!$card->list || !$card->list->board) {
            return false;
        }
        return $user->canAdminBoard($card->list->board);
    }
}
