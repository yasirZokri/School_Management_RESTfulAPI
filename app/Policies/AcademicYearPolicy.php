<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

/**
 * Same rule as the Phase 1 policies:
 * anyone can view, only admins change.
 */
class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->isAdmin();
    }
}
