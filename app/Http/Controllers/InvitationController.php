<?php

namespace App\Http\Controllers;

use App\Actions\InviteMember;
use App\Models\BoardInvitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = BoardInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('boards.index')
                             ->with('error', 'This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('boards.show', $invitation->board)
                             ->with('info', 'Invitation already accepted.');
        }

        return view('invitations.show', compact('invitation'));
    }

    public function accept(string $token, InviteMember $action)
    {
        $invitation = BoardInvitation::where('token', $token)->firstOrFail();

        // User must be logged in and match the invited email
        if (auth()->user()->email !== $invitation->email) {
            return redirect()->route('boards.index')
                             ->with('error', 'This invitation was sent to a different email address.');
        }

        try {
            $action->accept($invitation);
            return redirect()->route('boards.show', $invitation->board)
                             ->with('success', "You've joined the board!");
        } catch (\RuntimeException $e) {
            return redirect()->route('boards.index')->with('error', $e->getMessage());
        }
    }
}
