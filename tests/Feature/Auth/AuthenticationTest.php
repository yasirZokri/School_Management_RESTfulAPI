<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

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

test('an already authenticated user cannot log in again', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $tokensBefore = DB::table('personal_access_tokens')->count();

    // (a) The user calls /api/login again WHILE sending their valid token.
    //     They are refused the moment the token is seen.
    $this->withToken($token)
        ->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertUnauthorized()
        ->assertJson(['message' => 'You are already authenticated. Please log out before signing in again.']);

    // Use the token on a protected route so the account really is "in use".
    $this->withToken($token)
        ->getJson('/api/user')
        ->assertOk();

    // Clear the cached guard so the next request starts fresh.
    auth()->forgetGuards();

    // (b) The user calls /api/login again WITHOUT sending their token. The
    //     account already holds an active (used) token, so this must ALSO be
    //     refused.
    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertUnauthorized()
        ->assertJson(['message' => 'You are already authenticated. Please log out before signing in again.']);

    // No extra token was created by either attempt.
    $this->assertDatabaseCount('personal_access_tokens', $tokensBefore);
});

test('an abandoned token that was never used does not lock the account', function () {
    $user = User::factory()->create();

    // A leftover token row that was created but never used by any client
    // (e.g. old test data). It must NOT count as "still logged in".
    $user->createToken('api');

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure(['token']);
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

test('a user can log in again after logging out', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();

    auth()->forgetGuards();

    // Logout deleted the active token, so a fresh login is allowed again.
    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);
});
