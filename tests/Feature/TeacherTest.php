<?php

use App\Enums\UserRole;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;

it('lets an admin create a teacher', function () {
    $user = User::factory()->role(UserRole::Teacher)->create();
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/teachers', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'employee_number' => 'EMP0001',
        ])
        ->assertCreated()
        ->assertJsonPath('data.employee_number', 'EMP0001');

    $this->assertDatabaseHas('teachers', ['employee_number' => 'EMP0001']);
});

it('lists teachers', function () {
    Teacher::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/teachers')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single teacher', function () {
    $teacher = Teacher::factory()->create(['employee_number' => 'EMP0042']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/teachers/{$teacher->id}")
        ->assertOk()
        ->assertJsonPath('data.employee_number', 'EMP0042');
});

it('updates a teacher', function () {
    $teacher = Teacher::factory()->create();
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/teachers/{$teacher->id}", [
            'school_id' => $school->id,
            'employee_number' => 'EMP9999',
        ])
        ->assertOk()
        ->assertJsonPath('data.employee_number', 'EMP9999');
});

it('rejects a teacher with missing fields', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/teachers', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id', 'school_id', 'employee_number']);
});

it('refuses to let a non-admin create a teacher', function () {
    $user = User::factory()->role(UserRole::Teacher)->create();
    $school = School::factory()->create();

    $this->actingAs(User::factory()->role(UserRole::Staff)->create())
        ->postJson('/api/v1/teachers', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'employee_number' => 'EMP0002',
        ])
        ->assertForbidden();
});

it('soft deletes a teacher', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(adminUser())
        ->deleteJson("/api/v1/teachers/{$teacher->id}")
        ->assertNoContent();

    // The row is still in the database, just "deleted" via deleted_at.
    $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
});
