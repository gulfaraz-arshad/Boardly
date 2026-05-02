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
    public bool   $showCreateWorkspace = false;
    public string $wsName              = '';
    public string $wsDescription       = '';
    public string $wsColor             = '#0ea5e9';

    // ─── Edit workspace ───────────────────────────────────────────
    public ?int   $editingWorkspaceId = null;
    public string $editWsName         = '';

    // ─── Create board inside workspace ────────────────────────────
    public ?int   $creatingBoardInWorkspace = null;
    public string $quickBoardName           = '';
    public string $quickBoardColor          = '#0ea5e9';

    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(?int $activeBoardId = null): void
    {
        $this->activeBoardId = $activeBoardId;

        if ($activeBoardId) {
            $board = Board::find($activeBoardId);
            if ($board?->workspace_id) {
                $this->expanded[$board->workspace_id] = true;
            }
        } else {
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
        $userId = auth()->id();

        return Workspace::where('user_id', $userId)                          // owned
                        ->orWhereHas('members', fn($q) => $q->where('users.id', $userId)) // member of
                        ->with(['boards' => fn($q) => $q->withCount('cards')])
                        ->withCount('boards')
                        ->orderBy('name')
                        ->get();
    }

    // ─── Sidebar toggle ───────────────────────────────────────────

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    // ─── Workspace accordion ──────────────────────────────────────

    public function toggleWorkspace(int $workspaceId): void
    {
        $this->expanded[$workspaceId] = ! ($this->expanded[$workspaceId] ?? false);
    }

    // ─── Workspace CRUD ───────────────────────────────────────────

    public function createWorkspace(): void
    {
        dd($this->showCreateWorkspace);
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

        $ws->delete(); // boards cascade via FK
        unset($this->expanded[$workspaceId]);
        unset($this->workspaces);
        $this->dispatch('workspace-deleted');
    }

    // ─── Quick board creation ─────────────────────────────────────

    public function createQuickBoard(): void
    {
        $this->validate(['quickBoardName' => 'required|string|max:100']);

        // Confirm the user actually belongs to this workspace
        $workspace = Workspace::findOrFail($this->creatingBoardInWorkspace);
        abort_unless($workspace->hasAccess(auth()->user()), 403);

        $board = Board::create([
            'user_id'      => auth()->id(),
            'workspace_id' => $workspace->id,
            'name'         => $this->quickBoardName,
            'color'        => $this->quickBoardColor,
        ]);

        $this->reset('quickBoardName', 'creatingBoardInWorkspace');
        $this->quickBoardColor = '#0ea5e9';
        unset($this->workspaces);

        $this->redirect(route('boards.show', $board), navigate: true);
    }

    // ─── Event listeners ─────────────────────────────────────────

    #[On('board-created')]
    #[On('board-deleted')]
    #[On('card-archived')]
    public function refreshBoards(): void
    {
        unset($this->workspaces);
    }

    public function render()
    {
        return view('livewire.app-sidebar');
    }
}
