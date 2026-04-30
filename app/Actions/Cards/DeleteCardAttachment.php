<?php

namespace App\Actions\Cards;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteCardAttachment
{
    public function __construct(protected LogActivity $logger) {}

    public function handle(CardAttachment $attachment, User $user, Card $card): void
    {
        abort_unless($attachment->uploaded_by === $user->id, 403);

        Storage::disk($attachment->disk)->delete($attachment->filename);

        $attachment->delete();

        $this->logger->handle(
            $card,
            $user,
            'attached',
            'removed an attachment'
        );
    }
}
