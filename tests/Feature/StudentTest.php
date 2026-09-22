<?php

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

it('lets an admin create a student', function () {
    $user = User::factory()->role(UserRole::Student)->create();
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/students', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'student_number' => 'STU0001',
        ])
        ->assertCreated()
        ->assertJsonPath('data.student_number', 'STU0001');

    $this->assertDatabaseHas('students', ['student_number' => 'STU0001']);
});

it('lists students', function () {
    Student::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/students')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single student', function () {
    $student = Student::factory()->create(['student_number' => 'STU0042']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/students/{$student->id}")
        ->assertOk()
        ->assertJsonPath('data.student_number', 'STU0042');
});

it('updates a student', function () {
    $student = Student::factory()->create();
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/students/{$student->id}", [
            'school_id' => $school->id,
            'student_number' => 'STU9999',
        ])
        ->assertOk()
        ->assertJsonPath('data.student_number', 'STU9999');
});

it('rejects a student with missing fields', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/students', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'school_id', 'student_number']);
});

it('refuses to let a non-admin create a student', function () {
    $user = User::factory()->role(UserRole::Student)->create();
    $school = School::factory()->create();

    $this->actingAs(User::factory()->role(UserRole::Teacher)->create())
        ->postJson('/api/v1/students', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'student_number' => 'STU0002',
        ])
        ->assertForbidden();
});

it('soft deletes a student', function () {
    $student = Student::factory()->create();

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/students/{$student->id}")
        ->assertNoContent();

    // The row is still in the database, just "deleted" via deleted_at.
    $this->assertSoftDeleted('students', ['id' => $student->id]);
});
