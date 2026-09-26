<?php

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;

it('lets an admin create a teaching assignment', function () {
    $school = School::factory()->create();
    $teacher = Teacher::factory()->for($school)->create();
    $subject = Subject::factory()->for($school)->create();
    $class = SchoolClass::factory()->for($school)->create([
        'academic_year_id' => AcademicYear::factory()->for($school)->create()->id,
    ]);

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teaching-assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $class->academic_year_id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.subject.name', $subject->name);

    $this->assertDatabaseHas('teaching_assignments', [
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
    ]);
});

it('lists teaching assignments', function () {
    TeachingAssignment::factory()->count(3)->create();

    $this->withToken(tokenFor(adminUser()))
        ->getJson('/api/v1/teaching-assignments')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single teaching assignment', function () {
    $assignment = TeachingAssignment::factory()->create();

    $this->withToken(tokenFor(adminUser()))
        ->getJson("/api/v1/teaching-assignments/{$assignment->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $assignment->id);
});

it('updates a teaching assignment', function () {
    $assignment = TeachingAssignment::factory()->create();
    $newSubject = Subject::factory()->for($assignment->class->school)->create();

    $this->withToken(tokenFor(adminUser()))
        ->putJson("/api/v1/teaching-assignments/{$assignment->id}", [
            'subject_id' => $newSubject->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.subject.id', $newSubject->id);
});

it('rejects a teaching assignment with a missing teacher', function () {
    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teaching-assignments', [
            'class_id' => SchoolClass::factory()->create()->id,
            'subject_id' => Subject::factory()->create()->id,
            'academic_year_id' => AcademicYear::factory()->create()->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('teacher_id');
});

it('rejects a teaching assignment whose teacher and class are in different schools', function () {
    $schoolA = School::factory()->create();
    $schoolB = School::factory()->create();
    $teacher = Teacher::factory()->for($schoolA)->create();
    $subject = Subject::factory()->for($schoolB)->create();
    $class = SchoolClass::factory()->for($schoolB)->create();

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teaching-assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $class->academic_year_id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('teacher_id');
});

it('rejects a duplicate teaching assignment', function () {
    $school = School::factory()->create();
    $teacher = Teacher::factory()->for($school)->create();
    $subject = Subject::factory()->for($school)->create();
    $year = AcademicYear::factory()->for($school)->create();
    $class = SchoolClass::factory()->for($school)->create(['academic_year_id' => $year->id]);

    TeachingAssignment::factory()->create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
    ]);

    $this->withToken(tokenFor(adminUser()))
        ->postJson('/api/v1/teaching-assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('teacher_id');
});
