<?php

namespace App\Actions\Cards;

use App\Models\Card;
use App\Models\CardChecklist;
use App\Models\ChecklistItem;
use App\Models\User;

class AddChecklistItem
{
    public function handle(CardChecklist $checklist, User $user, Card $card, string $content): ChecklistItem
    {
        abort_unless($checklist->card_id === $card->id, 403);

        $position = $checklist->items()->max('position') + 1;

        return ChecklistItem::create([
            'card_checklist_id' => $checklist->id,
            'content'           => trim($content),
            'position'          => $position,
        ]);
    }
}
