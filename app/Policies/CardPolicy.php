<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

/**
 * Card-level permissions.
 *
 * The board role drives most decisions. The extra rule for `member`:
 *   - Members can edit (title, description, due date, labels, cover) ONLY their own cards.
 *   - Members can ALWAYS move any card, add comments, add checklists, upload attachments.
 *   - Members CANNOT delete any card (only owner/admin can).
 *
 * ┌───────────────────┬─────────────┬───────┬───────┬──────────────────┬────────┐
 * │ Action            │ super_admin │ owner │ admin │ member           │ viewer │
 * ├───────────────────┼─────────────┼───────┼───────┼──────────────────┼────────┤
 * │ view              │ ✓           │ ✓     │ ✓     │ ✓                │ ✓      │
 * │ editDetails       │ ✓           │ ✓     │ ✓     │ own cards only   │ ✗      │
 * │ move              │ ✓           │ ✓     │ ✓     │ ✓ (any card)     │ ✗      │
 * │ comment           │ ✓           │ ✓     │ ✓     │ ✓                │ ✗      │
 * │ addChecklist      │ ✓           │ ✓     │ ✓     │ ✓                │ ✗      │
 * │ uploadAttachment  │ ✓           │ ✓     │ ✓     │ ✓                │ ✗      │
 * │ archive           │ ✓           │ ✓     │ ✓     │ own cards only   │ ✗      │
 * │ delete            │ ✓           │ ✓     │ ✓     │ ✗                │ ✗      │
 * └───────────────────┴─────────────┴───────┴───────┴──────────────────┴────────┘
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
        return $user->canViewBoard($card->list->board);
    }

    /**
     * Edit card details: title, description, due date, labels, cover color.
     * owner/admin: any card on the board.
     * member: only cards they created.
     */
    public function editDetails(User $user, Card $card): bool
    {
        $board = $card->list->board;

        if ($user->canAdminBoard($board)) {
            return true;
        }

        // member: only their own cards
        if ($user->canEditBoard($board)) {
            return $card->created_by === $user->id;
        }

        return false;
    }

    /**
     * Move a card to another list or reorder.
     * Any member (including those who didn't create the card).
     */
    public function move(User $user, Card $card): bool
    {
        return $user->canEditBoard($card->list->board);
    }

    /** Post a comment. */
    public function comment(User $user, Card $card): bool
    {
        return $user->canEditBoard($card->list->board);
    }

    /** Add/edit/toggle checklist items. */
    public function addChecklist(User $user, Card $card): bool
    {
        return $user->canEditBoard($card->list->board);
    }

    /** Upload an attachment. */
    public function uploadAttachment(User $user, Card $card): bool
    {
        return $user->canEditBoard($card->list->board);
    }

    /**
     * Archive a card.
     * owner/admin: any card. member: only their own.
     */
    public function archive(User $user, Card $card): bool
    {
        $board = $card->list->board;

        if ($user->canAdminBoard($board)) {
            return true;
        }

        if ($user->canEditBoard($board)) {
            return $card->created_by === $user->id;
        }

        return false;
    }

    /**
     * Permanently delete a card.
     * Only board owner or admin. Members cannot delete cards.
     */
    public function delete(User $user, Card $card): bool
    {
        return $user->canAdminBoard($card->list->board);
    }

    /**
     * Assign/unassign a member to this card.
     * Board owner or admin only.
     */
    public function assignMember(User $user, Card $card): bool
    {
        return $user->canAdminBoard($card->list->board);
    }
}
