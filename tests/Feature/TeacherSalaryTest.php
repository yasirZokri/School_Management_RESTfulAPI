<?php

use App\Enums\UserRole;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use App\Models\User;

it('lets an admin create a salary for a teacher', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/teacher-salaries', [
            'teacher_id' => $teacher->id,
            'amount' => '3000.00',
            'month' => '2026-10',
        ])
        ->assertCreated()
        ->assertJsonPath('data.amount', '3000.00')
        ->assertJsonPath('data.month', '2026-10');

    $this->assertDatabaseHas('teacher_salaries', [
        'teacher_id' => $teacher->id,
        'month' => '2026-10',
    ]);
});

it('lets an admin mark a salary as paid', function () {
    $teacher = Teacher::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/teacher-salaries', [
            'teacher_id' => $teacher->id,
            'amount' => '3000.00',
            'month' => '2026-10',
            'paid_at' => '2026-10-01',
        ])
        ->assertCreated()
        ->assertJsonPath('data.paid_at', '2026-10-01T00:00:00.000000Z');
});

it('lists salaries', function () {
    TeacherSalary::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/teacher-salaries')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single salary', function () {
    $salary = TeacherSalary::factory()->create(['amount' => '2500.00']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/teacher-salaries/{$salary->id}")
        ->assertOk()
        ->assertJsonPath('data.amount', '2500.00');
});

it('updates a salary', function () {
    $salary = TeacherSalary::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/teacher-salaries/{$salary->id}", ['amount' => '3200.50'])
        ->assertOk()
        ->assertJsonPath('data.amount', '3200.50');
});

it('rejects a salary without an amount or a month', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/teacher-salaries', [
            'teacher_id' => Teacher::factory()->create()->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'month']);
});

it('rejects a duplicate salary for the same teacher and month', function () {
    $teacher = Teacher::factory()->create();

    TeacherSalary::factory()->create([
        'teacher_id' => $teacher->id,
        'month' => '2026-10',
    ]);

    $this->actingAs(adminUser())
        ->postJson('/api/v1/teacher-salaries', [
            'teacher_id' => $teacher->id,
            'amount' => '3000.00',
            'month' => '2026-10',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('teacher_id');
});

it('refuses to let a staff member create a salary', function () {
    $this->actingAs(User::factory()->role(UserRole::Staff)->create())
        ->postJson('/api/v1/teacher-salaries', [
            'teacher_id' => Teacher::factory()->create()->id,
            'amount' => '3000.00',
            'month' => '2026-10',
        ])
        ->assertForbidden();
});
