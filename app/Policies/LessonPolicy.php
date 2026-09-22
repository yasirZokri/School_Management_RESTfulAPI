<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

/**
 * Anyone can view lessons. Admins can change any lesson, and a teacher
 * can change lessons that belong to one of their own teaching
 * assignments. The "assignment belongs to this teacher" check is also
 * repeated in the Form Request, so even the validation layer refuses it.
 */
class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Admin, or any user that has a teacher profile.
        return $user->isAdmin() || $user->teacher !== null;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->teacher !== null
            && $lesson->teaching_assignment_id !== null
            && $lesson->teachingAssignment?->teacher_id === $user->teacher->id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}
