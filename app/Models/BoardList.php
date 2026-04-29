<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoardList extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'color',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'position'    => 'integer',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
                    ->where('is_archived', false)
                    ->orderBy('position');
    }

    public function allCards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }

    public function getNextCardPosition(): int
    {
        return ($this->cards()->max('position') ?? -1) + 1;
    }
}
