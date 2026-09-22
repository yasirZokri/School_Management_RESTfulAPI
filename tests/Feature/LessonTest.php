<?php

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;

// Creates a teaching assignment that belongs to this exact teacher,
// using only records from the teacher's own school.
function assignmentForTeacher(Teacher $teacher): TeachingAssignment
{
    $year = AcademicYear::factory()->for($teacher->school)->create();
    $class = SchoolClass::factory()->for($teacher->school)->create(['academic_year_id' => $year->id]);
    $subject = Subject::factory()->for($teacher->school)->create();

    return TeachingAssignment::create([
        'teacher_id' => $teacher->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $year->id,
    ]);
}

it('lets an admin create a lesson', function () {
    $assignment = assignmentForTeacher(Teacher::factory()->create());

    $this->actingAs(adminUser())
        ->postJson('/api/v1/lessons', [
            'teaching_assignment_id' => $assignment->id,
            'title' => 'Introduction to Fractions',
            'description' => 'First lesson on fractions.',
            'content' => 'Fractions are parts of a whole.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Introduction to Fractions')
        ->assertJsonPath('data.teaching_assignment.id', $assignment->id);

    $this->assertDatabaseHas('lessons', ['title' => 'Introduction to Fractions']);
});

it('lists lessons', function () {
    Lesson::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/lessons')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single lesson', function () {
    $lesson = Lesson::factory()->create(['title' => 'Photosynthesis']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/lessons/{$lesson->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Photosynthesis');
});

it('updates a lesson', function () {
    $lesson = Lesson::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/lessons/{$lesson->id}", ['title' => 'Renamed Lesson'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Renamed Lesson');
});

it('rejects a lesson without an assignment or a title', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/lessons', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['teaching_assignment_id', 'title']);
});

it('lets a teacher create a lesson for their own assignment', function () {
    $teacher = Teacher::factory()->create();
    $assignment = assignmentForTeacher($teacher);

    $this->actingAs($teacher->user)
        ->postJson('/api/v1/lessons', [
            'teaching_assignment_id' => $assignment->id,
            'title' => 'My own lesson',
        ])
        ->assertCreated()
        ->assertJsonPath('data.teaching_assignment.id', $assignment->id);
});

it('refuses a teacher creating a lesson for someone else\'s assignment', function () {
    $teacher = Teacher::factory()->create();
    $otherTeachersAssignment = assignmentForTeacher(Teacher::factory()->create());

    $this->actingAs($teacher->user)
        ->postJson('/api/v1/lessons', [
            'teaching_assignment_id' => $otherTeachersAssignment->id,
            'title' => 'Sneaky lesson',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('teaching_assignment_id');
});

it('refuses a teacher updating a lesson that is not theirs', function () {
    $owner = Teacher::factory()->create();
    $lesson = Lesson::factory()->create(['teaching_assignment_id' => assignmentForTeacher($owner)->id]);

    $otherTeacher = Teacher::factory()->create();
    $this->actingAs($otherTeacher->user)
        ->putJson("/api/v1/lessons/{$lesson->id}", ['title' => 'Nope'])
        ->assertForbidden();
});

it('refuses a staff member creating a lesson', function () {
    $assignment = assignmentForTeacher(Teacher::factory()->create());

    $this->actingAs(User::factory()->role(UserRole::Staff)->create())
        ->postJson('/api/v1/lessons', [
            'teaching_assignment_id' => $assignment->id,
            'title' => 'Nope',
        ])
        ->assertForbidden();
});
