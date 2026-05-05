<?php

namespace App\Livewire;

use App\Actions\CreateCard;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BoardShow extends Component
{
    use AuthorizesRequests;

    public Board $board;

    // ─── Add list form ────────────────────────────────────────────
    public bool   $addingList  = false;
    public string $newListName = '';

    // ─── Add card (quick) ────────────────────────────────────────
    public ?int   $addingCardToList = null;
    public string $newCardTitle     = '';

    // ─── Filters ──────────────────────────────────────────────────
    public string $searchQuery = '';
    public ?int   $filterLabel = null;
    public string $filterDue   = ''; // overdue | today | ''

    public bool    $showLabelManager = false;
    public ?int    $editingLabelId   = null;
    public string  $labelName        = '';
    public string  $labelColor       = '#ef4444';

    // Predefined palette (matches Trello's)
    public array $labelPalette = [
        '#61bd4f',
        '#f2d600',
        '#ff9f1a',
        '#eb5a46',
        '#c377e0',
        '#0079bf',
        '#00c2e0',
        '#51e898',
        '#ff78cb',
        '#c4c9cc',
    ];


    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(Board $board): void
    {
        $this->authorize('view', $board);
        $this->board = $board;
    }

    // ─── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function lists()
    {
        return $this->board
            ->lists()
            ->with([
                'cards' => function ($q) {
                    $q
                        ->where('is_archived', false)
                        ->when($this->searchQuery, fn($q) => $q->search($this->searchQuery))
                        ->when($this->filterLabel, fn($q) => $q->withLabel($this->filterLabel))
                        ->when($this->filterDue === 'overdue', fn($q) => $q->overdue())
                        ->when($this->filterDue === 'today', fn($q) => $q->dueToday())
                        ->with(['labels', 'members:id,name', 'attachments'])
                        ->withCount('comments')
                        ->orderBy('position');
                },
            ])
            ->get();
    }

    #[Computed]
    public function labels()
    {
        return $this->board->labels;
    }

    // ─── List Management ─────────────────────────────────────────

    public function addList(): void
    {
        $this->authorize('createList', $this->board);
        $this->validate(['newListName' => 'required|string|max:100']);

        BoardList::create([
            'board_id' => $this->board->id,
            'name'     => $this->newListName,
            'position' => ($this->board->lists()->max('position') ?? -1) + 1,
        ]);

        $this->reset('newListName', 'addingList');
        unset($this->lists);
    }

    public function updateListName(int $listId, string $name): void
    {
        $this->authorize('update', $this->board);
        $list = BoardList::findOrFail($listId);
        abort_unless($list->board_id === $this->board->id, 403);

        $list->update(['name' => trim($name)]);
        unset($this->lists);
    }

    public function deleteList(int $listId): void
    {
        $this->authorize('update', $this->board);
        $list = BoardList::findOrFail($listId);
        abort_unless($list->board_id === $this->board->id, 403);

        $list->update(['is_archived' => true]);
        unset($this->lists);
    }

    /**
     * Called by Alpine/SortableJS when lists are reordered.
     * Expects: ['order' => [id1, id2, id3, ...]]
     */
    public function updateListOrder(array $items): void
    {
        $this->authorize('update', $this->board);

        foreach ($items as $item) {
            BoardList::where('id', $item['value'])
                     ->where('board_id', $this->board->id)
                     ->update(['position' => $item['order']]);
        }

        // Refresh the computed 'lists' property
        unset($this->lists);
    }

    // ─── Card Management ─────────────────────────────────────────

    public function addCard(CreateCard $action): void
    {
        $this->authorize('createCard', $this->board);
        $this->validate(['newCardTitle' => 'required|string|max:255']);

        $list = BoardList::findOrFail($this->addingCardToList);
        abort_unless($list->board_id === $this->board->id, 403);

        $action->handle(auth()->user(), $list, ['title' => $this->newCardTitle]);

        $this->reset('newCardTitle', 'addingCardToList');
        unset($this->lists);
    }

    /**
     * Called by Alpine/SortableJS when a card is dragged.
     * Expects: ['cardId', 'toListId', 'orderedIds' => [...]]
     */
    public function updateCardOrder(array $groups): void
    {
        $this->authorize('move-card', $this->board);

        foreach ($groups as $group) {
            $toListId = (int)$group['value'];

            foreach ($group['items'] as $item) {
                $cardId = (int)$item['value'];
                Card::where('id', $cardId)->update([
                    'board_list_id' => $toListId,
                    'position'      => $item['order'],
                ]);
            }
        }

        unset($this->lists);
    }

    public function openCard(int $cardId): void
    {
        $this->dispatch('open-card-modal', cardId: $cardId);
    }

    #[On('card-updated')]

    public function cardUpdate()
    {
        $this->dispatch('$refresh');
    }


    public function openLabelManager(): void
    {
        $this->showLabelManager = true;
        $this->resetLabelForm();
    }

    public function createLabel(): void
    {
        $this->authorize('update', $this->board);
        $this->validate([
            'labelName'  => 'required|string|max:50',
            'labelColor' => 'required|string|size:7',
        ]);

        $this->board->labels()->create([
            'name'  => $this->labelName,
            'color' => $this->labelColor,
        ]);

        $this->resetLabelForm();
        unset($this->labels);
    }

    public function startEditLabel(int $labelId): void
    {
        $label = $this->board->labels()->findOrFail($labelId);
        $this->editingLabelId = $labelId;
        $this->showLabelManager = true;
        $this->labelName      = $label->name;
        $this->labelColor     = $label->color;
    }

    public function updateLabel(): void
    {
        $this->authorize('update', $this->board);
        $this->validate([
            'labelName'  => 'required|string|max:50',
            'labelColor' => 'required|string|size:7',
        ]);

        $this->board->labels()
                    ->findOrFail($this->editingLabelId)
                    ->update(['name' => $this->labelName, 'color' => $this->labelColor]);

        $this->resetLabelForm();
        unset($this->labels);
    }

    public function deleteLabel(int $labelId): void
    {
        $this->authorize('update', $this->board);
        $this->board->labels()->findOrFail($labelId)->delete();
        if ($this->filterLabel === $labelId) {
            $this->filterLabel = null;
        }
        unset($this->labels);
    }

    public  function resetLabelForm(): void
    {
        $this->editingLabelId = null;
        $this->labelName      = '';
        $this->labelColor     = '#4bce97';
        $this->showLabelManager = false;

    }

    #[On('card-deleted')]
    public function refreshBoard()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.board.board-show', [
            'board' => $this->board->load('members:id,name,email'),
        ])->layout('components.layouts.app', ['title' => $this->board->name]);
    }
}
