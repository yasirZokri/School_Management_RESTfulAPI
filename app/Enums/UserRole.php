<?php

namespace App\Enums;

/**
 * The possible roles a user can have.
 *
 * A "backed" enum means each case has a real value stored in the database
 * (a string like 'admin'). We use the enum type in code, and Laravel
 * converts between the enum and the stored string automatically.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Staff = 'staff';
}
