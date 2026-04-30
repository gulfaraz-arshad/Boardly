<?php

namespace App\Livewire;

use App\Actions\CreateBoard;
use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class BoardIndex extends Component
{
    use AuthorizesRequests;

    public bool $showCreateModal = false;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    #[Rule('required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#0ea5e9';

    #[Rule('boolean')]
    public bool $is_public = false;

    // Pre-select a workspace when clicking "New Board" inside a workspace section
    public ?int $workspace_id = null;

    public string $search = '';

    /**
     * Returns workspaces the user owns or is a member of,
     * each eager-loaded with their boards (filtered by search).
     */
    #[Computed]
    public function workspaces(): Collection
    {
        return Workspace::where('user_id', auth()->id())
                        ->with([
                            'boards' => function ($q) {
                                $q->when($this->search, fn($q) => $q->where('name', 'like', "%$this->search%"))
                                  ->withCount('cards')
                                  ->with('members:id,name,email')
                                  ->orderBy('name');
                            },
                        ])
                        ->latest()
                        ->get();
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function createBoard(CreateBoard $action): void
    {
        $this->authorize('create', Board::class);
        $this->validate();

        $board = $action->handle(auth()->user(), [
            'name'         => $this->name,
            'description'  => $this->description,
            'color'        => $this->color,
            'is_public'    => $this->is_public,
            'workspace_id' => $this->workspace_id,
        ]);

        $this->reset(['name', 'description', 'color', 'is_public', 'showCreateModal', 'workspace_id']);
        $this->dispatch('board-created');

        $this->redirect(route('boards.show', $board), navigate: true);
    }

    public function deleteBoard(int $boardId): void
    {
        $board = Board::findOrFail($boardId);
        $this->authorize('delete', $board);
        $board->delete();

        unset($this->workspaces);
        $this->dispatch('board-deleted');
    }

    /**
     * Opens the creation modal pre-scoped to a workspace.
     */
    public function openCreateModal(?int $workspaceId = null): void
    {
        $this->workspace_id = $workspaceId;
        $this->showCreateModal = true;
    }

    #[On('workspace-deleted')]
    public function workspaceDeleted(): void
    {
        unset($this->workspaces);
    }

    public function render(): Factory|View
    {
        return view('livewire.board.board-index')
            ->layout('components.layouts.app', [
                'title'         => 'My Boards',
                'activeBoardId' => null,
            ]);
    }
}
