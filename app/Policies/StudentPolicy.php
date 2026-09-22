<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * Answers "may this user do this action on a student?"
 * Anyone can view student records; only admins change them.
 */
class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Student $student): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }
}
