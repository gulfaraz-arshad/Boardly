<?php

namespace App\Livewire;

use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AppSidebar extends Component
{
    use AuthorizesRequests;

    // ─── Sidebar open/close ───────────────────────────────────────
    public bool $collapsed = false;

    // ─── Active board (passed from parent page) ───────────────────
    public ?int $activeBoardId = null;

    // ─── Workspace expand state (keyed by workspace id) ───────────
    public array $expanded = [];

    // ─── Create workspace modal ───────────────────────────────────
    public bool $showCreateWorkspace = false;
    public string $wsName        = '';
    public string $wsDescription = '';
    public string $wsColor       = '#0ea5e9';

    // ─── Edit workspace ───────────────────────────────────────────
    public ?int $editingWorkspaceId = null;
    public string $editWsName       = '';

    // ─── Create board inside workspace ───────────────────────────
    public ?int $creatingBoardInWorkspace = null;
    public string $quickBoardName         = '';
    public string $quickBoardColor        = '#0ea5e9';

    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(?int $activeBoardId = null): void
    {
        $this->activeBoardId = $activeBoardId;

        // Auto-expand workspace that contains the active board
        if ($activeBoardId) {
            $board = Board::find($activeBoardId);
            if ($board?->workspace_id) {
                $this->expanded[$board->workspace_id] = true;
            }
        } else {
            // Expand first workspace by default
            $first = $this->workspaces->first();
            if ($first) {
                $this->expanded[$first->id] = true;
            }
        }
    }

    // ─── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function workspaces()
    {
        return Workspace::where('user_id', auth()->id())
                        ->with(['boards' => function ($q) {
                            $q->withCount('cards');
                        }])
                        ->withCount('boards')
                        ->orderBy('name')
                        ->get();
    }

    #[Computed]
    public function sharedBoards()
    {
        // Boards the user is a member of but doesn't own (no workspace)
        return Board::whereHas('members', fn($q) => $q->where('users.id', auth()->id()))
                    ->where('user_id', '!=', auth()->id())
                    ->withCount('cards')
                    ->with('owner:id,name')
                    ->orderBy('name')
                    ->get();
    }

    // ─── Sidebar toggle ───────────────────────────────────────────

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    // ─── Workspace accordion ─────────────────────────────────────

    public function toggleWorkspace(int $workspaceId): void
    {
        $this->expanded[$workspaceId] = ! ($this->expanded[$workspaceId] ?? false);
    }

    // ─── Workspace CRUD ───────────────────────────────────────────

    public function createWorkspace(): void
    {
        $this->validate([
            'wsName'  => 'required|string|max:60',
            'wsColor' => 'required|string|size:7',
        ]);

        $ws = Workspace::create([
            'user_id'     => auth()->id(),
            'name'        => $this->wsName,
            'description' => $this->wsDescription ?: null,
            'color'       => $this->wsColor,
        ]);

        $this->expanded[$ws->id] = true;
        $this->reset('wsName', 'wsDescription', 'showCreateWorkspace');
        $this->wsColor = '#0ea5e9';
        unset($this->workspaces);
    }

    public function startEditWorkspace(int $workspaceId): void
    {
        $ws = Workspace::findOrFail($workspaceId);
        abort_unless($ws->isOwnedBy(auth()->user()), 403);

        $this->editingWorkspaceId = $workspaceId;
        $this->editWsName         = $ws->name;
    }

    public function saveWorkspaceName(): void
    {
        $this->validate(['editWsName' => 'required|string|max:60']);

        Workspace::where('id', $this->editingWorkspaceId)
                 ->where('user_id', auth()->id())
                 ->update(['name' => $this->editWsName]);

        $this->editingWorkspaceId = null;
        unset($this->workspaces);
    }

    public function deleteWorkspace(int $workspaceId): void
    {
        $ws = Workspace::where('id', $workspaceId)
                       ->where('user_id', auth()->id())
                       ->firstOrFail();

        // Detach boards from workspace (don't delete the boards)
        $ws->boards()->delete();
        $ws->delete();

        unset($this->workspaces);
        unset($this->expanded[$workspaceId]);
        $this->dispatch('workspace-deleted');
    }

    // ─── Quick board creation ─────────────────────────────────────

    public function createQuickBoard(): void
    {
        $this->validate(['quickBoardName' => 'required|string|max:100']);

        $board = Board::create([
            'user_id'      => auth()->id(),
            'workspace_id' => $this->creatingBoardInWorkspace,
            'name'         => $this->quickBoardName,
            'color'        => $this->quickBoardColor,
        ]);

        // Owner as member
        $board->members()->attach(auth()->id(), ['role' => 'owner', 'joined_at' => now()]);

        // Seed default labels
        $defaults = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Urgent', 'color' => '#f97316'],
        ];
        foreach ($defaults as $label) {
            $board->labels()->create($label);
        }

        $this->reset('quickBoardName', 'creatingBoardInWorkspace');
        $this->quickBoardColor = '#0ea5e9';
        unset($this->workspaces);

        $this->redirect(route('boards.show', $board), navigate: true);
    }

    #[On('board-created')]
    public function refreshBoards(): void
    {
        unset($this->workspaces);
    }


    #[On('board-deleted')]
    public function boardDeleted(): void
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.app-sidebar');
    }
}
