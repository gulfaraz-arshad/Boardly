<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardChecklist;
use App\Models\User;

class AddChecklist
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, string $title): CardChecklist
    {
        $position = $card->checklists()->max('position') + 1;

        $checklist = CardChecklist::create([
            'card_id'  => $card->id,
            'title'    => $title,
            'position' => $position,
        ]);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            "added checklist **{$title}**"
        );

        return $checklist;
    }
}

