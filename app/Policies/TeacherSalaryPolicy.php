<?php

namespace App\Policies;

use App\Models\TeacherSalary;
use App\Models\User;

/**
 * Anyone can view salaries, only admins change them.
 */
class TeacherSalaryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TeacherSalary $teacherSalary): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TeacherSalary $teacherSalary): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, TeacherSalary $teacherSalary): bool
    {
        return $user->isAdmin();
    }
}
