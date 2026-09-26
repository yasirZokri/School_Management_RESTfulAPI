<?php

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\User;

it('lets an admin record a payment from a student', function () {
    $student = Student::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/student-payments', [
            'student_id' => $student->id,
            'amount' => '500.00',
            'purpose' => 'tuition',
            'paid_at' => '2026-10-01',
            'payment_method' => 'cash',
        ])
        ->assertCreated()
        ->assertJsonPath('data.amount', '500.00')
        ->assertJsonPath('data.purpose', 'tuition')
        ->assertJsonPath('data.student.id', $student->id);

    $this->assertDatabaseHas('student_payments', [
        'student_id' => $student->id,
        'amount' => 500.00,
    ]);
});

it('lists payments', function () {
    StudentPayment::factory()->count(3)->create();

    $this->withToken(tokenFor(adminUser()))
        ->getJson('/api/v1/student-payments')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single payment', function () {
    $payment = StudentPayment::factory()->create(['amount' => '250.00']);

    $this->withToken(tokenFor(adminUser()))
        ->getJson("/api/v1/student-payments/{$payment->id}")
        ->assertOk()
        ->assertJsonPath('data.amount', '250.00');
});

it('updates a payment', function () {
    $payment = StudentPayment::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->putJson("/api/v1/student-payments/{$payment->id}", ['purpose' => 'books'])
        ->assertOk()
        ->assertJsonPath('data.purpose', 'books');
});

it('rejects a payment with an unknown purpose', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/student-payments', [
            'student_id' => Student::factory()->create()->id,
            'amount' => '100.00',
            'purpose' => 'gambling',
            'paid_at' => '2026-10-01',
            'payment_method' => 'cash',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('purpose');
});

it('rejects a payment with an unknown payment method', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/student-payments', [
            'student_id' => Student::factory()->create()->id,
            'amount' => '100.00',
            'purpose' => 'tuition',
            'paid_at' => '2026-10-01',
            'payment_method' => 'bitcoin',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('payment_method');
});

it('rejects a payment without an amount', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/student-payments', [
            'student_id' => Student::factory()->create()->id,
            'purpose' => 'tuition',
            'paid_at' => '2026-10-01',
            'payment_method' => 'cash',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount');
});

it('refuses to let a staff member record a payment', function () {
    $this->withToken(tokenFor(User::factory()->role(UserRole::Staff)->create()))
        ->postJson('/api/v1/student-payments', [
            'student_id' => Student::factory()->create()->id,
            'amount' => '100.00',
            'purpose' => 'tuition',
            'paid_at' => '2026-10-01',
            'payment_method' => 'cash',
        ])
        ->assertForbidden();
});
