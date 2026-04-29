<?php

namespace App\Livewire;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\CardChecklist;
use App\Models\CardComment;
use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Class CardModal
 * * Manages the state and logic for the card detail modal, including
 * metadata updates, file attachments, and user comments.
 */
class CardModal extends Component
{
    use AuthorizesRequests, WithFileUploads;

    /** @var Card|null The active card instance */
    public ?Card $card = null;

    /** @var bool Visibility toggle for the modal UI */
    public bool $isOpen = false;

    // ─── Editable fields ─────────────────────────────────────────

    /** @var string The card title */
    public string $title = '';

    /** @var string The card description */
    public string $description = '';

    /** @var string|null ISO formatted due date string */
    public ?string $dueDate = null;

    /** @var bool Completion status of the card */
    public bool $isCompleted = false;

    /** @var string Hex or CSS color string for the card cover */
    public string $coverColor = '';

    // ─── Comment ─────────────────────────────────────────────────

    /** @var string Current text in the comment input field */
    public string $newComment = '';

    // ─── Attachment ──────────────────────────────────────────────

    /** @var UploadedFile|null Temporary file storage */
    #[Rule('nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt')]
    public $attachment = null;

    // ─── Checklist ───────────────────────────────────────────────
    public string $newChecklistTitle = '';
    public array  $newItemContent    = []; // keyed by checklist_id

    // ─── Lifecycle ────────────────────────────────────────────────

    /**
     * Initializes the modal with a specific card and its relations.
     * * @param  int  $cardId  The ID of the card to load.
     *
     * @return void
     * @throws ModelNotFoundException
     */
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

    /**
     * Resets state and closes the modal.
     * * @return void
     */
    public function close(): void
    {
        $this->isOpen = false;
        $this->card   = null;
        $this->reset('newComment', 'attachment');
    }

    // ─── Save card ────────────────────────────────────────────────

    /**
     * Updates the card title if changed.
     * * @param  LogActivity  $logger  Service to record the title change.
     *
     * @return void
     */
    public function saveTitle(LogActivity $logger): void
    {
        $this->validate(['title' => 'required|string|max:255']);
        $old = $this->card->title;

        if ($old !== $this->title) {
            $this->card->update(['title' => $this->title]);
            $logger->handle($this->card, auth()->user(), 'updated', "renamed this card from **{$old}** to **{$this->title}**");
            $this->refreshCard();
        }
    }

    /**
     * Saves the card description.
     * * @param  LogActivity  $logger  Service to record the description update.
     *
     * @return void
     */
    public function saveDescription(LogActivity $logger): void
    {
        $this->card->update(['description' => $this->description ? : null]);
        $logger->handle($this->card, auth()->user(), 'updated', 'updated the description');
        $this->refreshCard();
    }

    /**
     * Updates the card's due date.
     * * @param  LogActivity  $logger  Service to record the date change.
     *
     * @return void
     */
    public function saveDueDate(LogActivity $logger): void
    {
        $this->card->update(['due_date' => $this->dueDate ? : null]);
        $logger->handle($this->card, auth()->user(), 'due_date_changed', "changed the due date to **$this->dueDate**");
        $this->refreshCard();
    }

    /**
     * Toggles the completion status of the card.
     * * @param  LogActivity  $logger  Service to record status change.
     *
     * @return void
     */
    public function toggleComplete(LogActivity $logger): void
    {
        $this->isCompleted = ! $this->isCompleted;
        $this->card->update(['is_completed' => $this->isCompleted]);
        $verb = $this->isCompleted ? 'marked complete' : 'marked incomplete';
        $logger->handle($this->card, auth()->user(), 'completed', $verb);
        $this->refreshCard();
    }

    /**
     * Attaches or detaches a label from the card.
     * * @param  int  $labelId  The ID of the label to toggle.
     *
     * @param  LogActivity  $logger  Service to record label changes.
     *
     * @return void
     */
    public function toggleLabel(int $labelId, LogActivity $logger): void
    {
        $this->card->labels()->toggle($labelId);
        $logger->handle($this->card, auth()->user(), 'label_changed', 'changed labels');
        $this->refreshCard();
    }

    /**
     * Updates the card cover color.
     *
     * @return void
     */
    public function setCoverColor($color): void
    {
        $this->coverColor = $color;
        $this->card->update(['cover_color' => $color ? : null]);
        $this->refreshCard();
    }

    /**
     * Archives the card and closes the modal.
     * * @param  LogActivity  $logger  Service to record the archival.
     *
     * @return void
     */
    public function archiveCard(LogActivity $logger): void
    {
        $this->card->update(['is_archived' => true]);
        $logger->handle($this->card, auth()->user(), 'archived', 'archived this card');
        $this->close();
        $this->dispatch('card-archived');
    }

    // ─── Comments ─────────────────────────────────────────────────

    /**
     * Creates a new comment for the current card.
     * * @param  LogActivity  $logger  Service to record the comment activity.
     *
     * @return void
     */
    public function addComment(LogActivity $logger): void
    {
        $this->validate(['newComment' => 'required|string|max:2000']);

        CardComment::create([
            'card_id' => $this->card->id,
            'user_id' => auth()->id(),
            'body'    => $this->newComment,
        ]);

        $logger->handle($this->card, auth()->user(), 'commented', 'added a comment');

        $this->reset('newComment');
        $this->refreshCard();
    }

    /**
     * Removes a comment if the user is the owner.
     * * @param  int  $commentId  ID of the comment to delete.
     *
     * @return void
     */
    public function deleteComment(int $commentId): void
    {
        $comment = CardComment::findOrFail($commentId);
        abort_unless($comment->user_id === auth()->id(), 403);
        $comment->delete();
        $this->refreshCard();
    }

    /**
     * Livewire hook triggered when the attachment property is updated.
     * * @return void
     */
    public function updatedAttachment(): void
    {
        $this->uploadAttachment();
    }

    /**
     * Handles file validation and storage.
     * * @return void
     */
    public function uploadAttachment()
    {
        $this->validate([
            'attachment' => 'required|file|max:10240',
        ]);

        if ( ! $this->attachment) {
            return;
        }

        $path = $this->attachment->store('attachments', 'public');

        CardAttachment::create([
            'card_id'       => $this->card->id,
            'uploaded_by'   => auth()->id(),
            'filename'      => $path,
            'original_name' => $this->attachment->getClientOriginalName(),
            'mime_type'     => $this->attachment->getMimeType(),
            'size'          => $this->attachment->getSize(),
            'disk'          => 'public',
        ]);

        $this->reset('attachment');
        $this->refreshCard();
    }

    /**
     * Deletes a file from storage and its database record.
     * * @param  int  $attachmentId  ID of the attachment record.
     *
     * @param  LogActivity  $logger  Service to record the removal.
     *
     * @return void
     */
    public function deleteAttachment(int $attachmentId, LogActivity $logger): void
    {
        $attachment = CardAttachment::findOrFail($attachmentId);
        abort_unless($attachment->uploaded_by === auth()->id(), 403);

        Storage::disk($attachment->disk)->delete($attachment->filename);
        $attachment->delete();

        $logger->handle($this->card, auth()->user(), 'attached', "removed an attachment");
        $this->refreshCard();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Re-fetches the card model to ensure local state matches the DB.
     * * @return void
     */
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

    // ─── Checklists ───────────────────────────────────────────────

    public function addChecklist(LogActivity $logger): void
    {
        $this->validate(['newChecklistTitle' => 'required|string|max:100']);

        $position = $this->card->checklists()->max('position') + 1;

        CardChecklist::create([
            'card_id'  => $this->card->id,
            'title'    => $this->newChecklistTitle,
            'position' => $position,
        ]);

        $logger->handle($this->card, auth()->user(), 'updated', "added checklist **{$this->newChecklistTitle}**");
        $this->newChecklistTitle = '';
        $this->refreshCard();
    }

    public function deleteChecklist(int $checklistId, LogActivity $logger): void
    {
        $checklist = CardChecklist::findOrFail($checklistId);
        abort_unless($checklist->card_id === $this->card->id, 403);

        $logger->handle($this->card, auth()->user(), 'updated', "removed checklist **{$checklist->title}**");
        $checklist->delete();
        $this->refreshCard();
    }

    public function addChecklistItem(int $checklistId): void
    {
        $content = trim($this->newItemContent[$checklistId] ?? '');
        if (empty($content)) {
            return;
        }

        $checklist = CardChecklist::findOrFail($checklistId);
        abort_unless($checklist->card_id === $this->card->id, 403);

        $position = $checklist->items()->max('position') + 1;

        ChecklistItem::create([
            'card_checklist_id' => $checklistId,
            'content'           => $content,
            'position'          => $position,
        ]);

        $this->newItemContent[$checklistId] = '';
        $this->refreshCard();
    }

    public function toggleChecklistItem(int $itemId, LogActivity $logger): void
    {
        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        $item->update(['is_checked' => ! $item->is_checked]);
        $this->refreshCard();
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        $item->delete();
        $this->refreshCard();
    }

    public function updateChecklistItemContent(int $itemId, string $content): void
    {
        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        if (trim($content) === '') {
            $item->delete();
        } else {
            $item->update(['content' => trim($content)]);
        }
        $this->refreshCard();
    }

    /**
     * Renders the component view.
     * * @return View
     */
    public function render()
    {
        return view('livewire.card.card-modal');
    }
}
