<?php

namespace App\Livewire;

use App\Actions\CreateBoard;
use App\Models\Board;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BoardIndex extends Component
{
    use AuthorizesRequests;

    // ─── New board form ───────────────────────────────────────────
    public bool $showCreateModal = false;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    #[Rule('required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#0ea5e9';

    #[Rule('boolean')]
    public bool $is_public = false;

    public string $search = '';

    // ─── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function boards()
    {
        return Board::accessibleBy(auth()->user())
                    ->when($this->search, fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                    )
                    ->withCount('cards')
                    ->with('members:id,name,email')
                    ->latest()
                    ->get();
    }

    // ─── Actions ──────────────────────────────────────────────────

    public function createBoard(CreateBoard $action): void
    {
        $this->authorize('create', Board::class);
        $this->validate();

        $board = $action->handle(auth()->user(), [
            'name'        => $this->name,
            'description' => $this->description,
            'color'       => $this->color,
            'is_public'   => $this->is_public,
        ]);

        $this->reset(['name', 'description', 'color', 'is_public', 'showCreateModal']);
        $this->dispatch('board-created');

        $this->redirect(route('boards.show', $board), navigate: true);
    }

    public function deleteBoard(int $boardId): void
    {
        $board = Board::findOrFail($boardId);
        $this->authorize('delete', $board);

        $board->delete();
        unset($this->boards);
    }

    public function render()
    {
        return view('livewire.board.board-index');
    }
}
