<?php

use App\Models\School;
use App\Models\Subject;

it('lets an admin create a subject', function () {
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->postJson('/api/v1/subjects', [
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'MATH');

    $this->assertDatabaseHas('subjects', ['code' => 'MATH']);
});

it('lists subjects', function () {
    Subject::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/subjects')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single subject', function () {
    $subject = Subject::factory()->create(['code' => 'SCI']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/subjects/{$subject->id}")
        ->assertOk()
        ->assertJsonPath('data.code', 'SCI');
});

it('updates a subject', function () {
    $subject = Subject::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/subjects/{$subject->id}", ['name' => 'Physics'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Physics');
});

it('rejects a subject with a missing code', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/subjects', [
            'school_id' => School::factory()->create()->id,
            'name' => 'Mathematics',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('rejects a duplicate subject code in the same school', function () {
    $school = School::factory()->create();
    Subject::factory()->for($school)->create(['code' => 'MATH']);

    $this->actingAs(adminUser())
        ->postJson('/api/v1/subjects', [
            'school_id' => $school->id,
            'name' => 'Algebra',
            'code' => 'MATH',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});
