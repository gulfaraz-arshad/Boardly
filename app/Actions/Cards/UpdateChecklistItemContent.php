<?php

namespace App\Actions\Cards;

use App\Models\Card;
use App\Models\ChecklistItem;
use App\Models\User;

class UpdateChecklistItemContent
{
    public function handle(ChecklistItem $item, User $user, Card $card, string $content): void
    {
        abort_unless($item->checklist->card_id === $card->id, 403);

        if (trim($content) === '') {
            $item->delete();

            return;
        }

        $item->update(['content' => trim($content)]);
    }
}
