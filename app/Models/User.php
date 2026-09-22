<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class, // stored as a string, used as an enum
        ];
    }

    /**
     * Shortcut used by policies: "is this user an admin?"
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    // The teacher profile linked to this account (if it is a teacher).
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    // The student profile linked to this account (if it is a student).
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
