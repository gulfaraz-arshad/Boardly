<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $fillable = ['card_checklist_id', 'content', 'is_checked', 'position'];

    protected $casts = ['is_checked' => 'boolean'];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(CardChecklist::class, 'card_checklist_id');
    }
}
