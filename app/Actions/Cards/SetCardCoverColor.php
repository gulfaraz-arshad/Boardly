<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class SetCardCoverColor
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, ?string $color): void
    {
        $card->update(['cover_color' => $color ?: null]);

        $this->logger->handle(
            $card,
            $user,
            'updated',
            $color
                ? "changed the cover color to **{$color}**"
                : 'removed the cover color'
        );
    }
}

