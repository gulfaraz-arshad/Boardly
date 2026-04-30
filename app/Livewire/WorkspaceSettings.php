<?php

namespace App\Livewire;

use App\Models\Workspace;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Component;

class WorkspaceSettings extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public Workspace $workspace;

    // ── General tab form fields ──────────────────────────────────

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    #[Rule('required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#0ea5e9';

    // ── UI state ─────────────────────────────────────────────────

    public string $activeTab = 'general';
    public bool $showDeleteModal = false;
    public string $deleteConfirmName = '';

    // ── Lifecycle ────────────────────────────────────────────────

    public function mount(Workspace $workspace): void
    {
        $this->authorize('update', $workspace);

        $this->workspace   = $workspace;
        $this->name        = $workspace->name;
        $this->description = $workspace->description;
        $this->color       = $workspace->color ?? '#0ea5e9';
    }

    // ── Actions ──────────────────────────────────────────────────

    public function saveGeneral(): void
    {
        $this->authorize('update', $this->workspace);
        $this->validate();

        $this->workspace->update([
            'name'        => $this->name,
            'description' => $this->description,
            'color'       => $this->color,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Workspace updated.');
    }

    public function deleteWorkspace(): void
    {
        $this->authorize('delete', $this->workspace);

        if ($this->deleteConfirmName !== $this->workspace->name) {
            $this->addError('deleteConfirmName', 'Name does not match.');
            return;
        }

        $this->workspace->delete(); // cascades to boards via model booted()

        $this->dispatch('workspace-deleted');
        $this->redirect(route('boards.index'), navigate: true);
    }

    // ── Render ───────────────────────────────────────────────────

    public function render(): Factory|View
    {
        return view('livewire.workspace-settings', [
            'boardCount' => $this->workspace->boards()->count(),
        ])
            ->layout('components.layouts.app', [
                'title'         => $this->workspace->name . ' · Settings',
                'activeBoardId' => null,
            ]);
    }
}
