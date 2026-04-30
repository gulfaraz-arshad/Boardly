<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkspaceShow extends Component
{
    use AuthorizesRequests;

    public Workspace $workspace;

    // ─── Create board form ────────────────────────────────────────
    public bool $showCreateBoard = false;
    public string $boardName     = '';
    public string $boardColor    = '#0ea5e9';
    public string $boardDesc     = '';

    // ─── Edit workspace ───────────────────────────────────────────
    public bool $editingName     = false;
    public string $editName      = '';
    public string $editDesc      = '';

    public function mount(Workspace $workspace): void
    {
        $this->authorize('view', $workspace);
        $this->workspace = $workspace;
        $this->editName  = $workspace->name;
        $this->editDesc  = $workspace->description ?? '';
    }

    #[Computed]
    public function boards()
    {
        return $this->workspace->boards()
                               ->withCount('cards')
                               ->with('members:id,name,email')
                               ->orderBy('name')
                               ->get();
    }

    // ─── Workspace editing ────────────────────────────────────────

    public function saveName(): void
    {
        $this->authorize('update', $this->workspace);
        $this->validate(['editName' => 'required|string|max:60']);

        $this->workspace->update([
            'name'        => $this->editName,
            'description' => $this->editDesc ?: null,
        ]);

        $this->editingName = false;
        $this->workspace->refresh();
    }

    public function updateColor(string $color): void
    {
        $this->authorize('update', $this->workspace);
        $this->workspace->update(['color' => $color]);
        $this->workspace->refresh();
    }

    // ─── Board creation ───────────────────────────────────────────

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

        $board->members()->attach(auth()->id(), ['role' => 'owner', 'joined_at' => now()]);

        foreach ([
            ['name' => 'Bug',     'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Urgent',  'color' => '#f97316'],
            ['name' => 'Docs',    'color' => '#8b5cf6'],
        ] as $label) {
            $board->labels()->create($label);
        }

        $this->reset('boardName', 'boardDesc', 'showCreateBoard');
        $this->boardColor = '#0ea5e9';
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

    public function render()
    {
        return view('livewire.workspace-show')
            ->layout('components.layouts.app', [
                'title'         => $this->workspace->name,
                'activeBoardId' => null,
            ]);
    }
}
