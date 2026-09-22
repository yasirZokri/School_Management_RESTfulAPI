<?php

use App\Enums\UserRole;
use App\Models\School;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->role(UserRole::Admin)->create();
}

it('lets an admin create a school', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/schools', [
            'name' => 'Sunrise High School',
            'address' => '1 Main Street',
            'phone' => '555-0100',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Sunrise High School');

    $this->assertDatabaseHas('schools', ['name' => 'Sunrise High School']);
});

it('lists schools', function () {
    School::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->getJson('/api/v1/schools')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('shows a single school', function () {
    $school = School::factory()->create(['name' => 'Green Valley School']);

    $this->actingAs(adminUser())
        ->getJson("/api/v1/schools/{$school->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Green Valley School');
});

it('updates a school', function () {
    $school = School::factory()->create();

    $this->actingAs(adminUser())
        ->putJson("/api/v1/schools/{$school->id}", ['name' => 'Renamed School'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed School');
});

it('rejects a school without a name', function () {
    $this->actingAs(adminUser())
        ->postJson('/api/v1/schools', ['name' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('refuses to let a non-admin create a school', function () {
    $this->actingAs(User::factory()->role(UserRole::Staff)->create())
        ->postJson('/api/v1/schools', ['name' => 'Sneaky School'])
        ->assertForbidden();
});
