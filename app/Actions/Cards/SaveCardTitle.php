<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class SaveCardTitle
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, string $newTitle): void
    {
        $old = $card->title;

        if ($old === $newTitle) {
            return;
        }

        $card->update(['title' => $newTitle]);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            "renamed this card from **$old** to **$newTitle**",
        );
    }
}
