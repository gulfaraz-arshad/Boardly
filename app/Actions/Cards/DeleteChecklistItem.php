<?php

namespace App\Actions\Cards;

use App\Models\Card;
use App\Models\ChecklistItem;
use App\Models\User;

class DeleteChecklistItem
{
    public function handle(ChecklistItem $item, User $user, Card $card): void
    {
        abort_unless($item->checklist->card_id === $card->id, 403);

        $item->delete();
    }
}
