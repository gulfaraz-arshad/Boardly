<?php

namespace App\Notifications;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly WorkspaceInvitation $invitation
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $workspace = $this->invitation->workspace;
        $inviter   = $this->invitation->inviter;
        $url       = route('invitations.show', $this->invitation->token);

        return (new MailMessage)
            ->subject("$inviter->name invited you to the \"$workspace->name\" workspace")
            ->greeting('Hi there!')
            ->line("$inviter->name has invited you to collaborate in the **$workspace->name** workspace.")
            ->line('You\'ll join as a **' . ucfirst($this->invitation->role) . '**.')
            ->action('Accept Invitation', $url)
            ->line("This invitation will expire on {$this->invitation->expires_at->format('F j, Y')}.")
            ->line("If you don't want to join this workspace, you can ignore this email.")
            ->salutation('The Trello Team');
    }

    public function toArray(): array
    {
        return [
            'workspace_id'   => $this->invitation->workspace_id,
            'workspace_name' => $this->invitation->workspace->name,
            'invited_by'     => $this->invitation->inviter->name,
            'role'           => $this->invitation->role,
            'token'          => $this->invitation->token,
            'expires_at'     => $this->invitation->expires_at->toIso8601String(),
        ];
    }
}
