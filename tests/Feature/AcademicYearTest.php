<?php

use App\Models\AcademicYear;
use App\Models\School;

it('lets an admin create an academic year', function () {
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/academic-years', [
            'school_id' => $school->id,
            'name' => '2026 / 2027',
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-06-30',
            'is_current' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', '2026 / 2027');

    $this->assertDatabaseHas('academic_years', ['name' => '2026 / 2027']);
});

it('lists academic years', function () {
    AcademicYear::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/academic-years')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single academic year', function () {
    $year = AcademicYear::factory()->create(['name' => '2027 / 2028']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/academic-years/{$year->id}")
        ->assertOk()
        ->assertJsonPath('data.name', '2027 / 2028');
});

it('updates an academic year', function () {
    $year = AcademicYear::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/academic-years/{$year->id}", ['is_current' => true])
        ->assertOk()
        ->assertJsonPath('data.is_current', true);
});

it('rejects an academic year with a missing name', function () {
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/academic-years', [
            'school_id' => $school->id,
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-06-30',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
