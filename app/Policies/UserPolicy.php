<?php

namespace App\Policies;

use App\Models\User;

/**
 * Answers "may this user do this action on a user account?"
 *
 * Accounts are only touched by admins. A user may see their own profile,
 * but nobody except an admin can list, create, or change accounts.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin();
    }
}
