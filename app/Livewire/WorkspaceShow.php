<?php

namespace App\Livewire;

use App\Actions\InviteMember;
use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

class WorkspaceShow extends Component
{
    use AuthorizesRequests;

    public Workspace $workspace;

    #[Url]
    public string $tab = 'boards';

    // ─── Create board ─────────────────────────────────────────────

    public bool   $showCreateBoard = false;
    public string $boardName       = '';
    public string $boardColor      = '#0c66e4';
    public string $boardDesc       = '';

    // ─── Settings ─────────────────────────────────────────────────

    public string $editName      = '';
    public string $editDesc      = '';
    public string $editColor     = '';
    public bool   $settingsSaved = false;

    // ─── Invite ───────────────────────────────────────────────────

    public string  $inviteEmail   = '';
    public string  $inviteRole    = 'member';
    public ?string $inviteError   = null;
    public bool    $inviteSuccess = false;

    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(Workspace $workspace): void
    {
        $this->authorize('view', $workspace);
        $this->workspace = $workspace;
        $this->editName  = $workspace->name;
        $this->editDesc  = $workspace->description ?? '';
        $this->editColor = $workspace->color;
    }

    // ─── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function boards()
    {
        return $this->workspace->boards()
                               ->withCount('cards')
                               ->orderBy('name')
                               ->get();
    }

    #[Computed]
    public function members()
    {
        $owner            = $this->workspace->owner()->select('id', 'name', 'email')->first();
        $owner->pivot_role = 'owner';
        $owner->joined_at  = $this->workspace->created_at;

        $others = $this->workspace->members()
                                  ->select('users.id', 'users.name', 'users.email')
                                  ->withPivot('role', 'joined_at')
                                  ->orderBy('users.name')
                                  ->get()
                                  ->each(function ($m) {
                                      $m->pivot_role = $m->pivot->role;
                                      $m->joined_at  = $m->pivot->joined_at;
                                  });

        return collect([$owner])->merge($others)->unique('id');
    }

    // ─── Boards ───────────────────────────────────────────────────

    public function createBoard(): void
    {
        $this->authorize('manageBoards', $this->workspace);
        $this->validate(['boardName' => 'required|string|max:100']);

        $board = Board::create([
            'user_id'      => auth()->id(),
            'workspace_id' => $this->workspace->id,
            'name'         => $this->boardName,
            'color'        => $this->boardColor,
            'description'  => $this->boardDesc ?: null,
        ]);

        $this->reset('boardName', 'boardDesc', 'showCreateBoard');
        $this->boardColor = '#0c66e4';
        unset($this->boards);

        $this->redirect(route('boards.show', $board), navigate: true);
    }

    public function deleteBoard(int $boardId): void
    {
        $board = Board::findOrFail($boardId);
        abort_unless($board->workspace_id === $this->workspace->id, 403);
        $this->authorize('delete', $board);
        $board->delete();
        unset($this->boards);
    }

    // ─── Settings ─────────────────────────────────────────────────

    public function saveSettings(): void
    {
        $this->authorize('update', $this->workspace);
        $this->validate([
            'editName'  => 'required|string|max:60',
            'editColor' => 'required|string|size:7',
        ]);

        $this->workspace->update([
            'name'        => $this->editName,
            'description' => $this->editDesc ?: null,
            'color'       => $this->editColor,
        ]);

        $this->workspace->refresh();
        $this->settingsSaved = true;
    }

    public function deleteWorkspace(): void
    {
        $this->authorize('delete', $this->workspace);
        $this->workspace->delete();
        $this->redirect(route('workspaces.index'), navigate: true);
    }

    // ─── Members ──────────────────────────────────────────────────

    public function inviteMember(InviteMember $action): void
    {
        $this->authorize('manageMembers', $this->workspace);
        $this->inviteError   = null;
        $this->inviteSuccess = false;

        $this->validate([
            'inviteEmail' => 'required|email|max:255',
            'inviteRole'  => 'required|in:admin,member,viewer',
        ]);

        try {
            $action->handle($this->workspace, auth()->user(), $this->inviteEmail, $this->inviteRole);
        } catch (RuntimeException $e) {
            $this->inviteError = $e->getMessage();
            return;
        }

        $this->inviteSuccess = true;
        $this->inviteEmail   = '';
        unset($this->members);
    }

    public function changeMemberRole(int $userId, string $newRole): void
    {
        $this->authorize('manageMembers', $this->workspace);
        abort_if($userId === $this->workspace->user_id, 403);

        $this->workspace->members()->updateExistingPivot($userId, ['role' => $newRole]);
        unset($this->members);
    }

    public function removeMember(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);
        abort_if($userId === $this->workspace->user_id, 403);
        abort_if($userId === auth()->id(), 403);

        $this->workspace->members()->detach($userId);
        unset($this->members);
    }

    // ─── Render ───────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.workspace-show')
            ->layout('components.layouts.app', [
                'title'         => $this->workspace->name,
                'activeBoardId' => null,
            ]);
    }
}
