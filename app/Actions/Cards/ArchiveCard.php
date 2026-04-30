<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class ArchiveCard
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user): void
    {
        $card->update(['is_archived' => true]);

        $this->logger->handle(
            $card,
            $user,
            'archived',
            'archived this card'
        );
    }
}
