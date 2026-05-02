<?php

namespace App\Http\Controllers;

use App\Actions\InviteMember;
use App\Models\WorkspaceInvitation;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class InvitationController extends Controller
{
    /**
     * @param  string  $token
     *
     * @return Factory|\Illuminate\Contracts\View\View|RedirectResponse|View
     */
    public function show(string $token)
    {
        $invitation = WorkspaceInvitation::with(['workspace', 'inviter'])
                                         ->where('token', $token)
                                         ->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('workspaces.index')
                             ->with('error', 'This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('workspaces.show', $invitation->workspace)
                             ->with('info', 'Invitation already accepted.');
        }

        return view('invitations.show', compact('invitation'));
    }

    /**
     * @param  string  $token
     * @param  InviteMember  $action
     *
     * @return RedirectResponse
     */
    public function accept(string $token, InviteMember $action)
    {
        $invitation = WorkspaceInvitation::with(['workspace', 'inviter'])
                                         ->where('token', $token)
                                         ->firstOrFail();

        if (auth()->user()->email !== $invitation->email) {
            return redirect()->route('workspaces.index')
                             ->with('error', 'This invitation was sent to a different email address.');
        }

        try {
            $action->accept($invitation);
            return redirect()->route('workspaces.show', $invitation->workspace)
                             ->with('success', "You've joined the workspace!");
        } catch (RuntimeException $e) {
            return redirect()->route('workspaces.index')->with('error', $e->getMessage());
        }
    }
}
