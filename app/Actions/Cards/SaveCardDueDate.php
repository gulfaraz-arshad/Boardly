<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class SaveCardDueDate
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, ?string $dueDate): void
    {
        $card->update(['due_date' => $dueDate ?: null]);

        $this->logger->handle(
            $card,
            $user,
            'due_date_changed',
            $dueDate
                ? "changed the due date to **$dueDate**"
                : 'removed the due date'
        );
    }
}
