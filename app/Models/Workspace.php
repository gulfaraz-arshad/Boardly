<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class)->orderBy('name');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    // App\Models\Workspace.php
    protected static function booted()
    {
        static::deleting(function ($workspace) {
            // This ensures every board's own 'deleting' events are fired
            $workspace->boards->each->delete();
        });
    }
}
