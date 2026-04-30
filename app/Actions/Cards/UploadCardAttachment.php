<?php

namespace App\Actions\Cards;

use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadCardAttachment
{
    public function handle(Card $card, User $user, UploadedFile $file): CardAttachment
    {
        $path = $file->store('attachments', 'public');

        return CardAttachment::create([
            'card_id'       => $card->id,
            'uploaded_by'   => $user->id,
            'filename'      => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'disk'          => 'public',
        ]);
    }
}

