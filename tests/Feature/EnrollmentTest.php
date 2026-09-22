<?php

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;

it('lets an admin create an enrollment', function () {
    // Build one consistent school: the student, class and year all belong to it.
    $school = School::factory()->create();
    $student = Student::factory()->for($school)->create();
    $class = SchoolClass::factory()->for($school)->create([
        'academic_year_id' => AcademicYear::factory()->for($school)->create()->id,
    ]);

    $this->actingAs(adminUser())
        ->postJson('/api/v1/enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');

    $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'class_id' => $class->id]);
});

it('lists enrollments', function () {
    Enrollment::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/enrollments')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single enrollment', function () {
    $enrollment = Enrollment::factory()->create();

    $this->actingAs(adminUser())
        ->getJson("/api/v1/enrollments/{$enrollment->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $enrollment->id);
});

it('updates an enrollment status', function () {
    $enrollment = Enrollment::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/enrollments/{$enrollment->id}", ['status' => 'completed'])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');
});

it('rejects an enrollment with a missing student', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/enrollments', [
            'class_id' => SchoolClass::factory()->create()->id,
            'academic_year_id' => AcademicYear::factory()->create()->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('student_id');
});

it('rejects an enrollment for a student and class in different schools', function () {
    // Student in school A, class in school B.
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $student = Student::factory()->for($schoolA)->create();
    $class = SchoolClass::factory()->for($schoolB)->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $class->academic_year_id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('class_id');
});

it('rejects an enrollment whose academic year does not match the class', function () {
    $school = School::factory()->create();
    $student = Student::factory()->for($school)->create();
    $class = SchoolClass::factory()->for($school)->create();
    // A different year for the same school.
    $wrongYear = AcademicYear::factory()->for($school)->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/enrollments', [
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $wrongYear->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('academic_year_id');
});
