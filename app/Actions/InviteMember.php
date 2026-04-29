<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use App\Notifications\BoardInvitationNotification;
use Illuminate\Support\Str;
use RuntimeException;

class InviteMember
{
    public function handle(Board $board, User $inviter, string $email, string $role = 'member'): BoardInvitation
    {
        // If user already exists and is a member, skip
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $board->hasMember($existingUser)) {
            throw new RuntimeException('User is already a member of this board.');
        }

        // Expire old pending invitations
        BoardInvitation::where('board_id', $board->id)
                       ->where('email', $email)
                       ->where('accepted_at', null)
                       ->delete();

        $invitation = BoardInvitation::create([
            'board_id'   => $board->id,
            'invited_by' => $inviter->id,
            'email'      => $email,
            'token'      => Str::random(64),
            'role'       => $role,
            'expires_at' => now()->addDays(7),
        ]);

        // Send email notification
        if ($existingUser) {
            $existingUser->notify(new BoardInvitationNotification($invitation));
        } else {
            \Notification::route('mail', $email)
                         ->notify(new BoardInvitationNotification($invitation));
        }

        return $invitation;
    }

    public function accept(BoardInvitation $invitation): void
    {
        if ($invitation->isExpired()) {
            throw new RuntimeException('This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            throw new RuntimeException('This invitation has already been accepted.');
        }

        $user = User::where('email', $invitation->email)->firstOrFail();

        $invitation->board->members()->syncWithoutDetaching([
            $user->id => [
                'role'      => $invitation->role,
                'joined_at' => now(),
            ],
        ]);

        $invitation->update(['accepted_at' => now()]);
    }
}
