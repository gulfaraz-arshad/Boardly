<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\User;

class AddCardComment
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(Card $card, User $user, string $body): CardComment
    {
        $comment = CardComment::create([
            'card_id' => $card->id,
            'user_id' => $user->id,
            'body'    => $body,
        ]);

        $this->logger->handle(
            $card,
            $user,
            'commented',
            'added a comment'
        );

        return $comment;
    }
}
