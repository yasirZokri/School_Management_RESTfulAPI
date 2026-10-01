<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('STEP 1: login, logout with that token, then login again succeeds with a new token', function () {
    $user = User::factory()->create();

    $firstLogin = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $firstLogin->assertOk();
    $token = $firstLogin->json('token');
    expect($token)->toBeString()->not->toBeEmpty();

    $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson('/api/logout')->assertNoContent();

    $this->flushHeaders();

    $secondLogin = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $secondLogin
        ->assertOk()
        ->assertJsonStructure(['token', 'user']);

    $newToken = $secondLogin->json('token');
    expect($newToken)->toBeString()->not->toBeEmpty()->not->toBe($token);

    $this->withHeaders([
        'Authorization' => 'Bearer '.$newToken,
    ])->getJson('/api/user')->assertOk()->assertJsonPath('email', $user->email);
});

test('1. login then logout then login again succeeds and returns a new token', function () {
    $user = User::factory()->create();

    $first = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $oldToken = $first->json('token');

    $this->withToken($oldToken)->postJson('/api/logout')->assertNoContent();
    $this->flushHeaders();

    $second = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $newToken = $second->json('token');
    expect($newToken)->not->toBe($oldToken);

    $this->withToken($newToken)->getJson('/api/user')->assertOk();
});

test('2. login without logout then a second login is still blocked', function () {
    $user = User::factory()->create();

    $login = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    // Use the token on a protected route so the account really holds an
    // ACTIVE token. (A created-but-never-used token is abandoned data and
    // must not lock the account - that is the whole bug we fixed.)
    $this->withToken($login->json('token'))->getJson('/api/user')->assertOk();

    $tokensBefore = DB::table('personal_access_tokens')->count();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertUnauthorized()
        ->assertJson(['message' => 'You are already authenticated. Please log out before signing in again.']);

    $this->assertDatabaseCount('personal_access_tokens', $tokensBefore);
});

test('3. login then logout then the old token is rejected on a protected route', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withToken($token)->postJson('/api/logout')->assertNoContent();
    $this->flushHeaders();

    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
});

test('4. login logout login logout login keeps working every cycle', function () {
    $user = User::factory()->create();
    $tokens = [];

    for ($cycle = 1; $cycle <= 2; $cycle++) {
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');
        $tokens[] = $token;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();
        $this->flushHeaders();
    }

    $third = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $tokens[] = $third->json('token');

    expect(collect($tokens)->unique()->count())->toBe(count($tokens));
    $this->withToken($tokens[2])->getJson('/api/user')->assertOk();
});

test('logout still deletes the token when the Bearer value has trailing whitespace', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withHeaders([
        'Authorization' => 'Bearer '.$token.' ',
    ])->postJson('/api/logout')->assertNoContent();

    $this->flushHeaders();
    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
});

test('logout still deletes the token when Authorization is the raw token without Bearer', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withHeaders([
        'Authorization' => $token,
    ])->postJson('/api/logout')->assertNoContent();

    $this->flushHeaders();
    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
});
