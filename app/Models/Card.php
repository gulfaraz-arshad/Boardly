<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_list_id',
        'created_by',
        'title',
        'description',
        'position',
        'due_date',
        'is_completed',
        'is_archived',
        'cover_color',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'is_completed' => 'boolean',
        'is_archived'  => 'boolean',
        'position'     => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'board_list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'card_label');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_members');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CardAttachment::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CardActivity::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class)->whereNull('deleted_at')->latest();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(CardChecklist::class)->orderBy('position');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereFullText(['title', 'description'], $term);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '<', now())
                     ->where('is_completed', false);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeWithLabel(Builder $query, int $labelId): Builder
    {
        return $query->whereHas('labels', fn($q) => $q->where('labels.id', $labelId));
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isDueOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! $this->is_completed;
    }

    public function isDueSoon(): bool
    {
        return $this->due_date
               && $this->due_date->isFuture()
               && $this->due_date->diffInHours(now()) <= 24;
    }

    public function getChecklistProgressAttribute(): array
    {
        $total   = $this->checklists->sum(fn($cl) => $cl->items->count());
        $checked = $this->checklists->sum(fn($cl) => $cl->items->where('is_checked', true)->count());

        return [
            'total'   => $total,
            'checked' => $checked,
            'percent' => $total > 0 ? round($checked / $total * 100) : 0,
        ];
    }
}
