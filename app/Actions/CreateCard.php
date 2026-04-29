<?php

namespace App\Actions;

use App\Models\BoardList;
use App\Models\Card;
use App\Models\User;

class CreateCard
{
    public function __construct(private readonly LogActivity $logger) {}

    public function handle(User $user, BoardList $list, array $data): Card
    {
        $card = Card::create([
            'board_list_id' => $list->id,
            'created_by'    => $user->id,
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'position'      => $list->getNextCardPosition(),
            'due_date'      => $data['due_date'] ?? null,
        ]);

        $this->logger->handle($card, $user, 'created', "created this card");

        return $card;
    }
}
