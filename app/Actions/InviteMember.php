<?php

namespace App\Actions;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\User;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

class InviteMember
{
    /**
     * Handles the logic of inviting a user to a workspace, including creating an invitation record and sending a notification.
     *
     * @param  Workspace  $workspace
     * @param  User  $inviter
     * @param  string  $email
     * @param  string  $role
     *
     * @return WorkspaceInvitation
     */
    public function handle(Workspace $workspace, User $inviter, string $email, string $role = 'member'): WorkspaceInvitation
    {
        // If user already exists and has access, skip
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $workspace->hasAccess($existingUser)) {
            throw new RuntimeException('User is already a member of this workspace.');
        }

        // Expire old pending invitations for this workspace + email
        WorkspaceInvitation::query()->where('workspace_id', $workspace->id)
                           ->where('email', $email)
                           ->whereNull('accepted_at')
                           ->delete();

        $invitation = WorkspaceInvitation::query()->create([
            'workspace_id' => $workspace->id,
            'inviter_id'   => $inviter->id,
            'email'        => $email,
            'token'        => Str::random(64),
            'role'         => $role,
            'expires_at'   => now()->addDays(7),
        ]);

        // Send notification — to existing user or anonymous email
        if ($existingUser) {
            $existingUser->notify(new WorkspaceInvitationNotification($invitation));
        } else {
            Notification::route('mail', $email)
                        ->notify(new WorkspaceInvitationNotification($invitation));
        }

        return $invitation;
    }

    /**
     * Accepts an invitation, adding the user to the workspace if valid.
     *
     * @param  WorkspaceInvitation  $invitation
     *
     * @return void
     */
    public function accept(WorkspaceInvitation $invitation): void
    {
        if ($invitation->isExpired()) {
            throw new RuntimeException('This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            throw new RuntimeException('This invitation has already been accepted.');
        }

        $user = User::where('email', $invitation->email)->firstOrFail();

        // Idempotent — won't duplicate if already a member somehow
        $invitation->workspace->members()->syncWithoutDetaching([
            $user->id => [
                'role'      => $invitation->role,
                'joined_at' => now(),
            ],
        ]);

        $invitation->update(['accepted_at' => now()]);
    }
}
