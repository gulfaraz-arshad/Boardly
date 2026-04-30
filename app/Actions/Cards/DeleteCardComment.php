<?php

namespace App\Actions\Cards;

use App\Models\CardComment;
use App\Models\User;

class DeleteCardComment
{
    public function handle(CardComment $comment, User $user): void
    {
        abort_unless($comment->user_id === $user->id, 403);
        $comment->delete();
    }
}

