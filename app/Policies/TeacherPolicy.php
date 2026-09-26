<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

/**
 * Answers "may this user do this action on a teacher?"
 * Teachers are sensitive staff records. The list (viewAny) is admin-only,
 * and a teacher may view their own profile (self) but nobody checks other
 * teachers. Students cannot see teachers at all. Only admins change them.
 */
class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $user->isAdmin()
            // A teacher can view their own profile (the linked Teacher row).
            || $user->teacher?->is($teacher);
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
