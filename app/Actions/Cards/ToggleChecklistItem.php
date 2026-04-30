<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\ChecklistItem;
use App\Models\User;

class ToggleChecklistItem
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(ChecklistItem $item, User $user, Card $card): void
    {
        abort_unless($item->checklist->card_id === $card->id, 403);

        $item->update(['is_checked' => ! $item->is_checked]);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            $item->is_checked
                ? "checked **{$item->content}**"
                : "unchecked **{$item->content}**"
        );
    }
}
