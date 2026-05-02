<?php

namespace App\Livewire;

use App\Actions\LogActivity;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Models\CardChecklist;
use App\Models\CardComment;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Storage;

class CardModal extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public ?Card $card  = null;
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
    public array  $newItemContent    = []; // keyed by checklist_id

    // ─── Lifecycle ────────────────────────────────────────────────

    #[On('open-card-modal')]
    public function openCard(int $cardId): void
    {
        $this->card = Card::with([
            'list.board.workspace',
            'list.board.labels',
            'labels',
            'members:id,name,email',
            'attachments.uploader:id,name',
            'comments.user:id,name,email',
            'activities.user:id,name',
            'checklists.items',
        ])->findOrFail($cardId);

        $this->authorize('view', $this->card);

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
        $this->reset('newComment', 'attachment', 'newChecklistTitle', 'newItemContent');
    }

    // ─── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function boardMembers()
    {
        return $this->card->list->board->workspace->members;
    }

    /**
     * Can the current user edit card details (title, desc, due date, labels, cover)?
     * owner/admin: any card. member: only their own.
     */
    #[Computed]
    public function canEditDetails(): bool
    {
        return auth()->user()->can('editDetails', $this->card);
    }

    /** Can the user manage members (assign/unassign)? */
    #[Computed]
    public function canAssignMembers(): bool
    {
        return auth()->user()->can('assignMember', $this->card);
    }

    /** Can the user archive this card? */
    #[Computed]
    public function canArchive(): bool
    {
        return auth()->user()->can('archive', $this->card);
    }

    /** Can the user delete this card? */
    #[Computed]
    public function canDelete(): bool
    {
        return auth()->user()->can('delete', $this->card);
    }

    // ─── Card detail edits ────────────────────────────────────────

    public function saveTitle(LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->validate(['title' => 'required|string|max:255']);

        $old = $this->card->title;
        if ($old !== $this->title) {
            $this->card->update(['title' => $this->title]);
            $logger->handle($this->card, auth()->user(), 'updated',
                "renamed this card from **{$old}** to **{$this->title}**");
            $this->refreshCard();
        }
    }

    public function saveDescription(LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->card->update(['description' => $this->description ?: null]);
        $logger->handle($this->card, auth()->user(), 'updated', 'updated the description');
        $this->refreshCard();
    }

    public function saveDueDate(LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->card->update(['due_date' => $this->dueDate ?: null]);
        $logger->handle($this->card, auth()->user(), 'due_date_changed',
            'changed the due date to **' . ($this->dueDate ?? 'none') . '**');
        $this->refreshCard();
    }

    public function toggleComplete(LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->isCompleted = ! $this->isCompleted;
        $this->card->update(['is_completed' => $this->isCompleted]);
        $verb = $this->isCompleted ? 'marked this card complete' : 'marked this card incomplete';
        $logger->handle($this->card, auth()->user(), 'completed', $verb);
        $this->refreshCard();
    }

    public function toggleLabel(int $labelId, LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->card->labels()->toggle($labelId);
        $logger->handle($this->card, auth()->user(), 'label_changed', 'changed labels');
        $this->refreshCard();
    }

    public function setCoverColor(string $color, LogActivity $logger): void
    {
        $this->authorize('editDetails', $this->card);
        $this->coverColor = $color;
        $this->card->update(['cover_color' => $color ?: null]);
        $this->refreshCard();
    }

    public function archiveCard(LogActivity $logger): void
    {
        $this->authorize('archive', $this->card);
        $this->card->update(['is_archived' => true]);
        $logger->handle($this->card, auth()->user(), 'archived', 'archived this card');
        $this->close();
        $this->dispatch('card-archived');
    }

    // ─── Members ─────────────────────────────────────────────────

    public function toggleMember(int $userId, LogActivity $logger): void
    {
        $this->authorize('assignMember', $this->card);

        $isAssigned = $this->card->members()->where('users.id', $userId)->exists();
        $this->card->members()->toggle($userId);

        $user = User::find($userId);
        $verb = $isAssigned
            ? "removed **{$user->name}** from this card"
            : "assigned **{$user->name}** to this card";

        $logger->handle($this->card, auth()->user(), 'assigned', $verb);
        $this->refreshCard();
    }

    // ─── Comments ─────────────────────────────────────────────────

    public function addComment(LogActivity $logger): void
    {
        $this->authorize('comment', $this->card);
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

    public function deleteComment(int $commentId): void
    {
        $comment = CardComment::findOrFail($commentId);
        // Users can always delete their own comments; admins can delete any
        $board = $this->card->list->board;
        abort_unless(
            $comment->user_id === auth()->id() || auth()->user()->canAdminBoard($board),
            403
        );
        $comment->delete();
        $this->refreshCard();
    }

    // ─── Attachments ─────────────────────────────────────────────

    public function uploadAttachment(LogActivity $logger): void
    {
        $this->authorize('uploadAttachment', $this->card);
        $this->validate(['attachment' => 'required|file|max:10240']);

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

        $logger->handle($this->card, auth()->user(), 'attached',
            "attached **{$this->attachment->getClientOriginalName()}**");
        $this->reset('attachment');
        $this->refreshCard();
    }

    public function deleteAttachment(int $attachmentId, LogActivity $logger): void
    {
        $attachment = CardAttachment::findOrFail($attachmentId);
        $board      = $this->card->list->board;

        // Uploader or board admin can delete attachments
        abort_unless(
            $attachment->uploaded_by === auth()->id() || auth()->user()->canAdminBoard($board),
            403
        );

        Storage::disk($attachment->disk)->delete($attachment->filename);
        $attachment->delete();
        $logger->handle($this->card, auth()->user(), 'attached', 'removed an attachment');
        $this->refreshCard();
    }

    // ─── Checklists ───────────────────────────────────────────────

    public function addChecklist(LogActivity $logger): void
    {
        $this->authorize('addChecklist', $this->card);
        $this->validate(['newChecklistTitle' => 'required|string|max:100']);

        $position = ($this->card->checklists()->max('position') ?? -1) + 1;

        CardChecklist::create([
            'card_id'  => $this->card->id,
            'title'    => $this->newChecklistTitle,
            'position' => $position,
        ]);

        $logger->handle($this->card, auth()->user(), 'updated',
            "added checklist **{$this->newChecklistTitle}**");
        $this->newChecklistTitle = '';
        $this->refreshCard();
    }

    public function deleteChecklist(int $checklistId, LogActivity $logger): void
    {
        // Only admins/owners can delete entire checklists
        $this->authorize('editDetails', $this->card);

        $checklist = CardChecklist::findOrFail($checklistId);
        abort_unless($checklist->card_id === $this->card->id, 403);

        $logger->handle($this->card, auth()->user(), 'updated',
            "removed checklist **{$checklist->title}**");
        $checklist->delete();
        $this->refreshCard();
    }

    public function addChecklistItem(int $checklistId): void
    {
        $this->authorize('addChecklist', $this->card);
        $content = trim($this->newItemContent[$checklistId] ?? '');
        if (empty($content)) return;

        $checklist = CardChecklist::findOrFail($checklistId);
        abort_unless($checklist->card_id === $this->card->id, 403);

        $position = ($checklist->items()->max('position') ?? -1) + 1;

        ChecklistItem::create([
            'card_checklist_id' => $checklistId,
            'content'           => $content,
            'position'          => $position,
        ]);

        $this->newItemContent[$checklistId] = '';
        $this->refreshCard();
    }

    public function toggleChecklistItem(int $itemId): void
    {
        $this->authorize('addChecklist', $this->card);

        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        $item->update(['is_checked' => ! $item->is_checked]);
        $this->refreshCard();
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $this->authorize('addChecklist', $this->card);

        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        $item->delete();
        $this->refreshCard();
    }

    public function updateChecklistItemContent(int $itemId, string $content): void
    {
        $this->authorize('addChecklist', $this->card);

        $item = ChecklistItem::findOrFail($itemId);
        abort_unless($item->checklist->card_id === $this->card->id, 403);

        trim($content) === '' ? $item->delete() : $item->update(['content' => trim($content)]);
        $this->refreshCard();
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function refreshCard(): void
    {
        $this->card = $this->card->fresh([
            'list.board.workspace',
            'list.board.labels',
            'labels',
            'members:id,name,email',
            'attachments.uploader:id,name',
            'comments.user:id,name,email',
            'activities.user:id,name',
            'checklists.items',
        ]);
        $this->dispatch('card-updated');
    }

    public function render()
    {
        return view('livewire.card.card-modal');
    }
}
