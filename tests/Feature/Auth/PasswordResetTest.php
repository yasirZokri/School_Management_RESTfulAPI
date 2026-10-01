<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('a password can be reset with the plain-text token from the email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/api/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $plainToken = $notification->token;

        // The email carries the PLAIN token...
        $this->assertIsString($plainToken);
        $this->assertNotNull($plainToken);

        // ...while the database stores a HASH of it. This is by design: the
        // plain token is never saved, so a leaked database cannot be used to
        // reset passwords.
        $storedHash = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertNotNull($storedHash);
        $this->assertNotSame($plainToken, $storedHash);

        // The two STILL match: Laravel re-hashes the submitted plain token and
        // compares it to the stored hash. This proves there is no mismatch.
        $this->assertTrue(Hash::check($plainToken, $storedHash));

        // Submitting the PLAIN token from the email works end-to-end.
        $this->post('/api/reset-password', [
            'token' => $plainToken,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        // The one-time reset token is consumed (its row is deleted).
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        // Logging in with the NEW password succeeds...
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'new-password',
        ])->assertOk();

        // ...and logging in with the old password is rejected.
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertUnprocessable();

        // The SAME token cannot be reused for a second reset: it was consumed
        // by the first successful reset.
        $this->post('/api/reset-password', [
            'token' => $plainToken,
            'email' => $user->email,
            'password' => 'another-new-password',
            'password_confirmation' => 'another-new-password',
        ])->assertUnprocessable();

        return true;
    });
});
