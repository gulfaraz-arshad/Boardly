<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\User;

class ToggleCardMember
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, int $targetUserId): void
    {
        $isAssigned = $card->members()->where('users.id', $targetUserId)->exists();

        $card->members()->toggle($targetUserId);

        $target = User::findOrFail($targetUserId);

        $verb = $isAssigned
            ? "removed **{$target->name}** from this card"
            : "assigned **{$target->name}** to this card";

        $this->logger->handle(
            $card,
            $user,
            'assigned',
            $verb
        );
    }
}
