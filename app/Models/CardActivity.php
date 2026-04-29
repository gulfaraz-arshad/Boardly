<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardActivity extends Model
{
    protected $fillable = ['card_id', 'user_id', 'type', 'content', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Activity type constants
    const TYPE_CREATED    = 'created';
    const TYPE_MOVED      = 'moved';
    const TYPE_UPDATED    = 'updated';
    const TYPE_COMMENTED  = 'commented';
    const TYPE_ATTACHED   = 'attached';
    const TYPE_ASSIGNED   = 'assigned';
    const TYPE_COMPLETED  = 'completed';
    const TYPE_LABEL      = 'label_changed';
    const TYPE_DUE_DATE   = 'due_date_changed';
    const TYPE_ARCHIVED   = 'archived';
}
