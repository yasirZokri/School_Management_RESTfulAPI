<?php

use App\Models\User;

test('1. login then logout with the same token succeeds', function () {
    $user = User::factory()->create();

    $login = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $token = $login->json('token');
    expect($token)->toBeString()->not->toBeEmpty();

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();
});

test('2. the same token cannot be used again on a protected route after logout', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();

    // The token row is gone, so the revoked token must be refused.
    auth()->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('3. logging in twice without logging out is still blocked while the first token is in use', function () {
    $user = User::factory()->create();

    $login = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    // Actually use the token on a protected route so the account really is
    // "logged in and in use" before trying to log in again.
    $this->withToken($login->json('token'))
        ->getJson('/api/user')
        ->assertOk();

    auth()->forgetGuards();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertUnauthorized();
});

test('4. login then logout then login again works and returns a new valid token', function () {
    $user = User::factory()->create();

    $first = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $this->withToken($first->json('token'))
        ->postJson('/api/logout')
        ->assertNoContent();

    auth()->forgetGuards();

    $second = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $newToken = $second->json('token');
    expect($newToken)->not->toBe($first->json('token'));

    $this->withToken($newToken)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('email', $user->email);
});
