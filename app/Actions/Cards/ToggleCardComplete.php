<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class ToggleCardComplete
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user): void
    {
        $isCompleted = ! $card->is_completed;

        $card->update(['is_completed' => $isCompleted]);

        $verb = $isCompleted ? 'marked complete' : 'marked incomplete';

        $this->logger->handle(
            $card,
            $user,
            'completed',
            $verb
        );
    }
}

