<?php

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

it('lets an admin create a student with a linked account', function () {
    $school = School::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/students', [
            'name' => 'Example Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'school_id' => $school->id,
            'student_number' => 'STU0001',
        ])
        ->assertCreated()
        ->assertJsonPath('data.student_number', 'STU0001');

    // The linked login account is created internally, with the Student role.
    $this->assertDatabaseHas('users', [
        'email' => 'student@example.com',
        'role' => UserRole::Student->value,
    ]);
    $this->assertDatabaseHas('students', ['student_number' => 'STU0001']);
});

it('lists students', function () {
    Student::factory()->count(3)->create();

    $this->withToken(tokenFor(adminUser()))
        ->getJson('/api/v1/students')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single student', function () {
    $student = Student::factory()->create(['student_number' => 'STU0042']);

    $this->withToken(tokenFor(adminUser()))
        ->getJson("/api/v1/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.student_number', 'STU0042');
});

it('updates a student', function () {
    $student = Student::factory()->create();
    $school = School::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->putJson("/api/v1/students/{$student->id}", [
            'school_id' => $school->id,
            'student_number' => 'STU9999',
        ])
        ->assertOk()
        ->assertJsonPath('data.student_number', 'STU9999');
});

it('rejects a student with missing fields', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/students', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'school_id', 'student_number']);
});

it('soft deletes a student', function () {
    $student = Student::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->deleteJson("/api/v1/students/{$student->id}")
        ->assertNoContent();

    // The row is still in the database, just "deleted" via deleted_at.
    $this->assertSoftDeleted('students', ['id' => $student->id]);
});

it('lets a teacher view students', function () {
    $student = Student::factory()->create();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Teacher)->create()))
        ->getJson('/api/v1/students')
        ->assertOk();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Teacher)->create()))
        ->getJson("/api/v1/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $student->id);
});

it('lets a student view their own profile', function () {
    $student = Student::factory()->create();

    $this->withToken(tokenFor($student->user))
        ->getJson("/api/v1/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $student->id);
});

it('blocks a student from viewing other students', function () {
    $student = Student::factory()->create();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Student)->create()))
        ->getJson('/api/v1/students')
        ->assertForbidden();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Student)->create()))
        ->getJson("/api/v1/students/{$student->id}")
        ->assertForbidden();
});

it('blocks a non-admin from creating a student', function () {
    $school = School::factory()->create();

    foreach ([UserRole::Teacher, UserRole::Student, UserRole::Staff] as $role) {
        $this->withToken(tokenFor(User::factory()->role($role)->create()))
            ->postJson('/api/v1/students', [
                'name' => 'Blocked Create',
                'email' => 'blocked-create-'.strtolower($role->value).'@example.com',
                'password' => 'password123',
                'school_id' => $school->id,
                'student_number' => 'STU0002',
            ])
            ->assertForbidden();
    }
});

it('blocks a non-admin from deleting a student', function () {
    $student = Student::factory()->create();

    foreach ([UserRole::Teacher, UserRole::Student] as $role) {
        $this->withToken(tokenFor(User::factory()->role($role)->create()))
            ->deleteJson("/api/v1/students/{$student->id}")
            ->assertForbidden();
    }

    $this->assertDatabaseHas('students', ['id' => $student->id]);
});
