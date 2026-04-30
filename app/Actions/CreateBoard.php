<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\User;

class CreateBoard
{
    public function handle(User $user, array $data): Board
    {
        $board = Board::create([
            'user_id'     => $user->id,
            'workspace_id' => $data['workspace_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#0ea5e9',
            'is_public'   => $data['is_public'] ?? false,
        ]);

        // Owner is also a member with role 'owner'
        $board->members()->attach($user->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        // Seed default labels
        $defaultLabels = [
            ['name' => 'Bug',      'color' => '#ef4444'],
            ['name' => 'Feature',  'color' => '#3b82f6'],
            ['name' => 'Urgent',   'color' => '#f97316'],
            ['name' => 'Docs',     'color' => '#8b5cf6'],
            ['name' => 'Design',   'color' => '#ec4899'],
            ['name' => 'Backend',  'color' => '#14b8a6'],
        ];

        foreach ($defaultLabels as $label) {
            $board->labels()->create($label);
        }

        return $board;
    }
}
