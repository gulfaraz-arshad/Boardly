<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Board $board): bool
    {
        return $board->is_public || $board->hasMember($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Board $board): bool
    {
        $role = $board->getMemberRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    public function delete(User $user, Board $board): bool
    {
        return $board->isOwnedBy($user);
    }

    public function manageMembers(User $user, Board $board): bool
    {
        $role = $board->getMemberRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    public function createList(User $user, Board $board): bool
    {
        $role = $board->getMemberRole($user);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    public function createCard(User $user, Board $board): bool
    {
        $role = $board->getMemberRole($user);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}
