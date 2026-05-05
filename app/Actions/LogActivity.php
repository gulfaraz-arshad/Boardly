<?php

namespace App\Actions;

use App\Models\Card;
use App\Models\CardActivity;
use App\Models\User;

class LogActivity
{
    /**
     * @param  Card  $card
     * @param  User  $user
     * @param  string  $type
     * @param  string  $content
     * @param  array  $metadata
     *
     * @return CardActivity
     */
    public function handle(Card $card, User $user, string $type, string $content, array $metadata = []): CardActivity
    {
        return CardActivity::create([
            'card_id'  => $card->id,
            'user_id'  => $user->id,
            'type'     => $type,
            'content'  => $content,
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
