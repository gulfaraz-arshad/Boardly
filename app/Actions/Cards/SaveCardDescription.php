<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class SaveCardDescription
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, ?string $description): void
    {
        $card->update(['description' => $description ?: null]);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            'updated the description'
        );
    }
}
