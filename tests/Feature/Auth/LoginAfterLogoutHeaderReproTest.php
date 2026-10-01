<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('DEEPER REPRO: trailing space on Bearer token — logout 204 then login again', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withHeaders([
        'Authorization' => 'Bearer '.$token.' ',
    ])->postJson('/api/logout')->assertNoContent();

    dump([
        'case' => 'trailing-space-bearer',
        'tokens_after_logout' => DB::table('personal_access_tokens')->count(),
    ]);

    $this->flushHeaders();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
});

test('DEEPER REPRO: Authorization header is the raw token without Bearer prefix', function () {
    $user = User::factory()->create();

    $token = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->json('token');

    $this->withHeaders([
        'Authorization' => $token,
    ])->postJson('/api/logout')->assertNoContent();

    dump([
        'case' => 'raw-authorization-token',
        'tokens_after_logout' => DB::table('personal_access_tokens')->count(),
    ]);

    $this->flushHeaders();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
});
