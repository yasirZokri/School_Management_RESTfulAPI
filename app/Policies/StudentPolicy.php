<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;

/**
 * Answers "may this user do this action on a student?"
 * Admins and teachers may view student records; a student may view their
 * own profile (self) but never other students. Only admins change them.
 */
class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->role === UserRole::Teacher;
    }

    public function view(User $user, Student $student): bool
    {
        return $user->isAdmin()
            || $user->role === UserRole::Teacher
            // A student can view their own profile (the linked Student row).
            || $user->student?->is($student);
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
