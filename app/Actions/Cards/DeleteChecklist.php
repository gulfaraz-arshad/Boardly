<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardChecklist;
use App\Models\User;

class DeleteChecklist
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(CardChecklist $checklist, User $user, Card $card): void
    {
        abort_unless($checklist->card_id === $card->id, 403);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            "removed checklist **{$checklist->title}**"
        );

        $checklist->delete();
    }
}
