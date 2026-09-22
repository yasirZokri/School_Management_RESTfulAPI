<?php

namespace App\Policies;

use App\Models\StudentPayment;
use App\Models\User;

/**
 * Anyone can view payments, only admins change them.
 */
class StudentPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StudentPayment $studentPayment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, StudentPayment $studentPayment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, StudentPayment $studentPayment): bool
    {
        return $user->isAdmin();
    }
}
