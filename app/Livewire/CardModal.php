<?php

namespace App\Livewire;

use App\Actions\Cards\AddCardComment;
use App\Actions\Cards\AddChecklist;
use App\Actions\Cards\AddChecklistItem;
use App\Actions\Cards\ArchiveCard;
use App\Actions\Cards\ToggleCardMember;
use App\Actions\Cards\DeleteCardAttachment;
use App\Actions\Cards\DeleteCardComment;
use App\Actions\Cards\DeleteChecklist;
use App\Actions\Cards\DeleteChecklistItem;
use App\Actions\Cards\SaveCardDescription;
use App\Actions\Cards\SaveCardDueDate;
use App\Actions\Cards\SaveCardTitle;
use App\Actions\Cards\SetCardCoverColor;
use App\Actions\Cards\ToggleCardComplete;
use App\Actions\Cards\ToggleCardLabel;
use App\Actions\Cards\ToggleChecklistItem;
use App\Actions\Cards\UpdateChecklistItemContent;
use App\Actions\Cards\UploadCardAttachment;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\CardChecklist;
use App\Models\CardComment;
use App\Models\ChecklistItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Class CardModal
 *
 * Thin orchestration layer for the card detail modal.
 * All business logic is delegated to dedicated action classes.
 */
class CardModal extends Component
{
    use AuthorizesRequests, WithFileUploads;

    /** @var Card|null The active card instance */
    public ?Card $card = null;

    /** @var bool Visibility toggle for the modal UI */
    public bool $isOpen = false;

    // ─── Editable fields ─────────────────────────────────────────

    public string  $title       = '';
    public string  $description = '';
    public ?string $dueDate     = null;
    public bool    $isCompleted = false;
    public string  $coverColor  = '';

    // ─── Comment ─────────────────────────────────────────────────

    public string $newComment = '';

    // ─── Attachment ──────────────────────────────────────────────

    #[Rule('nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt')]
    public $attachment = null;

    // ─── Checklist ───────────────────────────────────────────────

    public string $newChecklistTitle = '';
    public array  $newItemContent    = [];

    // ─── Lifecycle ────────────────────────────────────────────────

    #[On('open-card-modal')]
    public function openCard(int $cardId): void
    {
        $this->card = Card::with([
            'list.board',
            'labels',
            'members:id,name,email',
            'attachments.uploader:id,name',
            'comments.user:id,name,email',
            'activities.user:id,name',
            'checklists.items',
        ])->findOrFail($cardId);

        $this->authorize('view', $this->card->list->board);

        $this->title       = $this->card->title;
        $this->description = $this->card->description ?? '';
        $this->dueDate     = $this->card->due_date?->format('Y-m-d\TH:i');
        $this->isCompleted = $this->card->is_completed;
        $this->coverColor  = $this->card->cover_color ?? '';
        $this->isOpen      = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->card   = null;
        $this->reset('newComment', 'attachment');
    }

    // ─── Card ─────────────────────────────────────────────────────

    public function saveTitle(SaveCardTitle $action): void
    {
        $this->validate(['title' => 'required|string|max:255']);
        $action->handle($this->card, auth()->user(), $this->title);
        $this->refreshCard();
    }

    public function saveDescription(SaveCardDescription $action): void
    {
        $action->handle($this->card, auth()->user(), $this->description);
        $this->refreshCard();
    }

    public function saveDueDate(SaveCardDueDate $action): void
    {
        $action->handle($this->card, auth()->user(), $this->dueDate);
        $this->refreshCard();
    }

    public function toggleComplete(ToggleCardComplete $action): void
    {
        $action->handle($this->card, auth()->user());
        $this->isCompleted = ! $this->isCompleted;
        $this->refreshCard();
    }

    public function toggleLabel(int $labelId, ToggleCardLabel $action): void
    {
        $action->handle($this->card, auth()->user(), $labelId);
        $this->refreshCard();
    }

    public function setCoverColor(string $color, SetCardCoverColor $action): void
    {
        $this->coverColor = $color;
        $action->handle($this->card, auth()->user(), $color);
        $this->refreshCard();
    }

    public function archiveCard(ArchiveCard $action): void
    {
        $action->handle($this->card, auth()->user());
        $this->close();
        $this->dispatch('card-archived');
    }

    // ─── Comments ─────────────────────────────────────────────────

    public function addComment(AddCardComment $action): void
    {
        $this->validate(['newComment' => 'required|string|max:2000']);
        $action->handle($this->card, auth()->user(), $this->newComment);
        $this->reset('newComment');
        $this->refreshCard();
    }

    public function deleteComment(int $commentId, DeleteCardComment $action): void
    {
        $action->handle(CardComment::findOrFail($commentId), auth()->user());
        $this->refreshCard();
    }

    // ─── Attachments ─────────────────────────────────────────────

    public function updatedAttachment(): void
    {
        $this->uploadAttachment(app(UploadCardAttachment::class));
    }

    public function uploadAttachment(UploadCardAttachment $action): void
    {
        $this->validate(['attachment' => 'required|file|max:10240']);

        if (! $this->attachment) {
            return;
        }

        $action->handle($this->card, auth()->user(), $this->attachment);
        $this->reset('attachment');
        $this->refreshCard();
    }

    public function deleteAttachment(int $attachmentId, DeleteCardAttachment $action): void
    {
        $action->handle(CardAttachment::findOrFail($attachmentId), auth()->user(), $this->card);
        $this->refreshCard();
    }

    // ─── Checklists ───────────────────────────────────────────────

    public function addChecklist(AddChecklist $action): void
    {
        $this->validate(['newChecklistTitle' => 'required|string|max:100']);
        $action->handle($this->card, auth()->user(), $this->newChecklistTitle);
        $this->newChecklistTitle = '';
        $this->refreshCard();
    }

    public function deleteChecklist(int $checklistId, DeleteChecklist $action): void
    {
        $action->handle(CardChecklist::findOrFail($checklistId), auth()->user(), $this->card);
        $this->refreshCard();
    }

    public function addChecklistItem(int $checklistId, AddChecklistItem $action): void
    {
        $content = trim($this->newItemContent[$checklistId] ?? '');

        if (empty($content)) {
            return;
        }

        $action->handle(CardChecklist::findOrFail($checklistId), auth()->user(), $this->card, $content);
        $this->newItemContent[$checklistId] = '';
        $this->refreshCard();
    }

    public function toggleChecklistItem(int $itemId, ToggleChecklistItem $action): void
    {
        $action->handle(ChecklistItem::findOrFail($itemId), auth()->user(), $this->card);
        $this->refreshCard();
    }

    public function deleteChecklistItem(int $itemId, DeleteChecklistItem $action): void
    {
        $action->handle(ChecklistItem::findOrFail($itemId), auth()->user(), $this->card);
        $this->refreshCard();
    }

    public function updateChecklistItemContent(int $itemId, string $content, UpdateChecklistItemContent $action): void
    {
        $action->handle(ChecklistItem::findOrFail($itemId), auth()->user(), $this->card, $content);
        $this->refreshCard();
    }

    // ─── Members ──────────────────────────────────────────────────

    #[Computed]
    public function boardMembers()
    {
        return $this->card->list->board->members;
    }

    public function toggleMember(int $userId, ToggleCardMember $action): void
    {
        abort_unless($this->card, 403);
        $action->handle($this->card, auth()->user(), $userId);
        $this->refreshCard();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function refreshCard(): void
    {
        $this->card = $this->card->fresh([
            'labels',
            'members:id,name,email',
            'attachments.uploader:id,name',
            'comments.user:id,name,email',
            'activities.user:id,name',
            'checklists.items',
        ]);

        $this->dispatch('card-updated');
    }

    public function render(): View
    {
        return view('livewire.card.card-modal');
    }
}
