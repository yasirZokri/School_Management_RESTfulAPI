<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

/**
 * Answers "may this user do this action on a teacher?"
 * Anyone can view teacher records; only admins change them.
 */
class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin();
    }
}
