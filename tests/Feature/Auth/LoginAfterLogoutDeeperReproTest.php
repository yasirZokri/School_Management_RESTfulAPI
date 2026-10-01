<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('DEEPER REPRO: login, use protected route, logout, login again without clearing auth header', function () {
    $user = User::factory()->create();

    $firstLogin = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $token = $firstLogin->json('token');

    // Real usage: the token is used on protected routes before logout.
    $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson('/api/user')
        ->assertOk();

    $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/logout')
        ->assertNoContent();

    expect(DB::table('personal_access_tokens')->count())->toBe(0);

    // Intentionally no auth()->forgetGuards() and no withoutToken().
    $secondLogin = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    if ($secondLogin->status() !== 200) {
        dump([
            'second_login_status' => $secondLogin->status(),
            'second_login_json' => $secondLogin->json(),
            'tokens' => DB::table('personal_access_tokens')->get()->all(),
            'web_check' => auth('web')->check(),
            'sanctum_check' => auth('sanctum')->check(),
            'default_check' => auth()->check(),
        ]);
    }

    $secondLogin->assertOk();
    expect($secondLogin->json('token'))->not->toBe($token);
});

test('DEEPER REPRO: logout that does not send a bearer token still lets the next login work', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    expect(DB::table('personal_access_tokens')->count())->toBe(1);

    // Client "logged out" without sending the token (logout still returns 204).
    $this->postJson('/api/logout')->assertNoContent();

    $secondLogin = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    dump([
        'case' => 'logout-without-token',
        'second_login_status' => $secondLogin->status(),
        'second_login_json' => $secondLogin->json(),
        'tokens_left' => DB::table('personal_access_tokens')->count(),
    ]);

    $secondLogin->assertOk();
});
