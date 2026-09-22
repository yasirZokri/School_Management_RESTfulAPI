<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;

/**
 * Answers "may this user do this action on a school?"
 * Admins manage schools; everyone else can only browse them.
 */
class SchoolPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, School $school): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, School $school): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, School $school): bool
    {
        return $user->isAdmin();
    }
}
