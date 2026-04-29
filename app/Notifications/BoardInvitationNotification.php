<?php

namespace App\Notifications;

use App\Models\BoardInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoardInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly BoardInvitation $invitation
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $board   = $this->invitation->board;
        $inviter = $this->invitation->inviter;
        $url     = route('invitations.show', $this->invitation->token);

        return (new MailMessage)
            ->subject("$inviter->name invited you to \"$board->name\" on Trello")
            ->greeting("Hi there!")
            ->line("$inviter->name has invited you to collaborate on the **$board->name** board on Trello.")
            ->line("You'll join as a **" . ucfirst($this->invitation->role) . "**.")
            ->action('Accept Invitation', $url)
            ->line("This invitation will expire on {$this->invitation->expires_at->format('F j, Y')}.")
            ->line("If you don't want to join this board, you can ignore this email.")
            ->salutation("The Trello Team");
    }

    public function toArray(): array
    {
        return [
            'board_id'      => $this->invitation->board_id,
            'board_name'    => $this->invitation->board->name,
            'invited_by'    => $this->invitation->inviter->name,
            'role'          => $this->invitation->role,
            'token'         => $this->invitation->token,
            'expires_at'    => $this->invitation->expires_at->toIso8601String(),
        ];
    }
}
