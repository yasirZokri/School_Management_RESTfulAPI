<?php

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;

it('lets an admin create a teacher with a linked account', function () {
    $school = School::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teachers', [
            'name' => 'Example Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'school_id' => $school->id,
            'employee_number' => 'EMP0001',
        ])
        ->assertCreated()
        ->assertJsonPath('data.employee_number', 'EMP0001');

    // The linked login account is created internally, with the Teacher role.
    $this->assertDatabaseHas('users', [
        'email' => 'teacher@example.com',
        'role' => UserRole::Teacher->value,
    ]);
    $this->assertDatabaseHas('teachers', ['employee_number' => 'EMP0001']);
});

it('lists teachers', function () {
    Teacher::factory()->count(3)->create();

    $this->withToken(tokenFor(adminUser()))
        ->getJson('/api/v1/teachers')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single teacher', function () {
    $teacher = Teacher::factory()->create(['employee_number' => 'EMP0042']);

    $this->withToken(tokenFor(adminUser()))
        ->getJson("/api/v1/teachers/{$teacher->id}")
        ->assertOk()
        ->assertJsonPath('data.employee_number', 'EMP0042');
});

it('updates a teacher', function () {
    $teacher = Teacher::factory()->create();
    $school = School::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->putJson("/api/v1/teachers/{$teacher->id}", [
            'school_id' => $school->id,
            'employee_number' => 'EMP9999',
        ])
        ->assertOk()
        ->assertJsonPath('data.employee_number', 'EMP9999');
});

it('rejects a teacher with missing fields', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teachers', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'school_id', 'employee_number']);
});

it('soft deletes a teacher', function () {
    $teacher = Teacher::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->deleteJson("/api/v1/teachers/{$teacher->id}")
        ->assertNoContent();

    // The row is still in the database, just "deleted" via deleted_at.
    $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
});

it('lets a teacher view their own profile', function () {
    $teacher = Teacher::factory()->create();

    $this->withToken(tokenFor($teacher->user))
        ->getJson("/api/v1/teachers/{$teacher->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $teacher->id);
});

it('blocks a student from viewing teachers', function () {
    $teacher = Teacher::factory()->create();

    $student = User::factory()->role(UserRole::Student)->create();

    $this->withToken(tokenFor($student))
        ->getJson('/api/v1/teachers')
        ->assertForbidden();

    $this->withToken(tokenFor($student))
        ->getJson("/api/v1/teachers/{$teacher->id}")
        ->assertForbidden();
});

it('blocks a teacher from viewing other teachers', function () {
    $teacher = Teacher::factory()->create();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Teacher)->create()))
        ->getJson('/api/v1/teachers')
        ->assertForbidden();

    $this->withToken(tokenFor(User::factory()->role(UserRole::Teacher)->create()))
        ->getJson("/api/v1/teachers/{$teacher->id}")
        ->assertForbidden();
});

it('blocks a non-admin from creating a teacher', function () {
    $school = School::factory()->create();

    foreach ([UserRole::Staff, UserRole::Teacher, UserRole::Student] as $role) {
        $this->withToken(tokenFor(User::factory()->role($role)->create()))
            ->postJson('/api/v1/teachers', [
                'name' => 'Blocked Create',
                'email' => 'blocked-create-'.strtolower($role->value).'@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'school_id' => $school->id,
                'employee_number' => 'EMP0002',
            ])
            ->assertForbidden();
    }
});

it('blocks a non-admin before validating the teacher payload', function () {
    $this->withToken(tokenFor(User::factory()->role(UserRole::Student)->create()))
        ->postJson('/api/v1/teachers', [])
        ->assertForbidden();
});

it('blocks a non-admin from deleting a teacher', function () {
    $teacher = Teacher::factory()->create();

    foreach ([UserRole::Teacher, UserRole::Student] as $role) {
        $this->withToken(tokenFor(User::factory()->role($role)->create()))
            ->deleteJson("/api/v1/teachers/{$teacher->id}")
            ->assertForbidden();
    }

    $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
});
