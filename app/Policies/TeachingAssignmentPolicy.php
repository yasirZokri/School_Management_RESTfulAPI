<?php

namespace App\Policies;

use App\Models\TeachingAssignment;
use App\Models\User;

/**
 * Anyone can view, only admins change.
 */
class TeachingAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TeachingAssignment $teachingAssignment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TeachingAssignment $teachingAssignment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, TeachingAssignment $teachingAssignment): bool
    {
        return $user->isAdmin();
    }
}
