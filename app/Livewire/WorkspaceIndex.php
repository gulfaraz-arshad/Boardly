<?php

namespace App\Livewire;

use App\Models\Workspace;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WorkspaceIndex extends Component
{
    #[Computed]
    public function workspaces()
    {
        $userId = auth()->id();

        return Workspace::where('user_id', $userId)
                        ->orWhereHas('members', fn($q) => $q->where('users.id', $userId))
                        ->with(['boards' => fn($q) => $q->withCount('cards')])
                        ->withCount('boards')
                        ->orderBy('name')
                        ->get();
    }

    public function render()
    {
        return view('livewire.workspace-index')
            ->layout('components.layouts.app', [
                'title'         => 'My Workspaces',
                'activeBoardId' => null,
            ]);
    }
}
