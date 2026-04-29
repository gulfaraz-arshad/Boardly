<?php

namespace App\Livewire;

use App\Actions\InviteMember;
use App\Models\Board;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

class InviteMemberModal extends Component
{
    use AuthorizesRequests;

    public ?Board $board = null;
    public bool $isOpen    = false;
    public string $email   = '';
    public string $role    = 'member';
    public ?string $message = null;
    public bool $success   = false;

    #[On('open-invite-modal')]
    public function open(int $boardId): void
    {
        $this->board  = Board::with('members:id,name,email')->findOrFail($boardId);
        $this->isOpen = true;
        $this->success = false;
        $this->message = null;
        $this->reset('email', 'role');
    }

    public function invite(InviteMember $action): void
    {
        abort_unless($this->board, 403);
        $this->authorize('manageMembers', $this->board);

        $this->validate([
            'email' => 'required|email|max:255',
            'role'  => 'required|in:admin,member,viewer',
        ]);

        try {
            $action->handle($this->board, auth()->user(), $this->email, $this->role);
            $this->success = true;
            $this->message = "Invitation sent to {$this->email}!";
            $this->reset('email', 'role');
        } catch (RuntimeException $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function removeMember(int $userId): void
    {
        abort_unless($this->board, 403);
        $this->authorize('manageMembers', $this->board);
        abort_if($userId === $this->board->user_id, 403, 'Cannot remove the board owner.');

        $this->board->members()->detach($userId);
        $this->board = $this->board->fresh(['members']);
    }

    public function render()
    {
        return view('livewire.board.invite-member-modal', [
            'members' => $this->board
                ? $this->board->members()->withPivot('role', 'joined_at')->get()
                : collect(),
        ]);
    }
}
