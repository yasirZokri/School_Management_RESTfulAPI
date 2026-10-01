<?php

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Http\Request;

test('REPRO: login, logout, then log in again without any guard clearing succeeds', function () {
    $user = User::factory()->create();

    // 1. Log in with a valid user and capture the token.
    $first = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $token = $first->json('token');

    // 2. Log out using that token -> assert success (204).
    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertNoContent();

    // 3. Immediately attempt to log in again with the same credentials.
    // NOTE: intentionally NO auth()->forgetGuards() here - this replicates
    // real usage where the application instance persists between requests.
    // 4. Assert this second login SUCCEEDS (200) and returns a new valid token.
    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure(['token']);
});

test('REPRO (dig deeper): a leftover second token must not block re-login after logout', function () {
    $user = User::factory()->create();

    // Simulate an account that holds TWO active tokens (e.g. created before
    // the one-token-per-account rule, or via another device). Logging out
    // via one of them must clear the account's "logged in" state entirely,
    // otherwise the next login is refused with "already authenticated".
    $first = $user->createToken('api')->plainTextToken;
    $user->createToken('api')->plainTextToken;

    $this->withToken($first)
        ->postJson('/api/logout')
        ->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonStructure(['token']);
});

test('REPRO (dig deeper): login -> logout -> login -> logout -> login works every cycle', function () {
    $user = User::factory()->create();

    $tokens = [];

    for ($cycle = 1; $cycle <= 2; $cycle++) {
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');
        $tokens[] = $token;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertNoContent();
    }

    $third = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $tokens[] = $third->json('token');

    expect(collect($tokens)->unique()->count())->toBe(count($tokens));
});

test('logout also clears an active stateful session (leftover session cannot keep the account logged in)', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    // Build a real logout request with a session attached, exactly like
    // Sanctum does for stateful frontend domains.
    $session = app('session')->driver();
    $session->start();
    $session->put('some_leftover_user_data', 'present');

    $request = Request::create('/api/logout', 'POST', [], [], [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    $request->setLaravelSession($session);

    app(LoginController::class)->destroy($request);

    expect($session->has('some_leftover_user_data'))->toBeFalse();
});
