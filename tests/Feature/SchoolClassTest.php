<?php

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;

it('lets an admin create a class', function () {
    $school = School::factory()->create();
    $year = AcademicYear::factory()->for($school)->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/classes', [
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Grade 10 - A',
            'grade_level' => '10',
            'section' => 'A',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Grade 10 - A');

    $this->assertDatabaseHas('classes', ['name' => 'Grade 10 - A']);
});

it('lists classes', function () {
    SchoolClass::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/classes')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single class', function () {
    $class = SchoolClass::factory()->create(['name' => 'Grade 11 - B']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/classes/{$class->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Grade 11 - B');
});

it('updates a class', function () {
    $class = SchoolClass::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/classes/{$class->id}", ['section' => 'C'])
        ->assertOk()
        ->assertJsonPath('data.section', 'C');
});

it('rejects a class with a missing name', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/classes', [
            'school_id' => School::factory()->create()->id,
            'academic_year_id' => AcademicYear::factory()->create()->id,
            'grade_level' => '10',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('rejects a class whose academic year belongs to another school', function () {
    $school = School::factory()->create();
    // A year from a DIFFERENT school - this must not be accepted.
    $otherYearsYear = AcademicYear::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/classes', [
            'school_id' => $school->id,
            'academic_year_id' => $otherYearsYear->id,
            'name' => 'Grade 10 - A',
            'grade_level' => '10',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('academic_year_id');
});
