<?php

use App\Models\User;

test('users can log in and receive a bearer token', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']])
        ->assertJsonPath('user.email', $user->email);

    // The issued token actually works on a protected route.
    $this->withToken($response->json('token'))
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('email', $user->email);
});

test('users cannot log in with an invalid password', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('login is rate limited after too many attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => 'someone@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    // The 6th attempt in the same minute is blocked (throttle:5,1).
    $this->postJson('/api/login', [
        'email' => 'someone@example.com',
        'password' => 'wrong-password',
    ])->assertTooManyRequests();
});

test('users can log out and their token stops working', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);

    // Reset the auth guards so the next request must look the token up in
    // the database again - where the row is now gone.
    auth()->forgetGuards();

    // The revoked token is no longer accepted.
    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});
