<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class ToggleCardLabel
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, int $labelId): void
    {
        $card->labels()->toggle($labelId);

        $this->logger->handle(
            $card,
            $user,
            'label_changed',
            'changed labels'
        );
    }
}
