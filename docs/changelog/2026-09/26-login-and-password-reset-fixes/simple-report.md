# Simple report - repeated login + password reset token

Date: 2026-09-26

Plain-language report for translation. Terms are explained the first time they
appear.

## What was actually changed in the code

### Issue 1 - A user can log in again even though they are already logged in

The login action (app/Http/Controllers/Auth/LoginController.php) now has TWO
real guards. A token works like a secret key card: logging in issues the card;
the client must send it back on every request.

New login behavior:

1. If the request comes in already carrying a valid card, the login is refused
   right away: status 401 and the message "You are already authenticated.
   Please log out before signing in again."
2. Even if the client does NOT send its card, the system now remembers that
   this account ALREADY has an active card, and refuses the login the same way.
   One account = one card. This is what stops "many logins in the same time":
   the second login is refused no matter what, until the user logs out (which
   deletes the card) and only then can log in again.

This was the real gap people hit: before, sending the token again was blocked,
but calling login WITHOUT sending the token minted a brand-new token every
time. That loophole is closed now.

### Issue 2 - Password reset token: email vs database (what you actually SEND)

There is no mismatch - the two values are deliberately different versions of
the SAME thing:

- The EMAIL contains the READABLE code (this is the one you use).
- The DATABASE stores a SCRAMBLED copy of that same code (a safe one-way
  version, like a fingerprint). The readable code is never stored anywhere.

When you submit a reset, the server scrambles the code YOU SENT and compares
it to the stored fingerprint. If they match, the reset works.

How to do it in practice:

1. Call:   POST /api/forgot-password   with { "email": "admin@example.com" }
2. Open the email you receive. It contains a link such as:
   http://localhost/reset-password?token=91888d888962b23e3a1b5dba99f4e6f5...
   The part after token= is the READABLE code: 91888d888962b23e3a1b5dba99f4e6f5
3. Call:   POST /api/reset-password   with
   {
     "token": "91888d888962b23e3a1b5dba99f4e6f5",   <- the READABLE one from the email
     "email": "admin@example.com",
     "password": "NewStrongPassword123",
     "password_confirmation": "NewStrongPassword123"
   }
4. You get a success response, and you can now log in with the new password.

NEVER copy the value from the password_reset_tokens database table and send
it: that is already the scrambled version, and scrambling a scrambled value
produces a different result (it fails). Always use the readable code from the
email. This whole journey is proven by an automated test that catches the real
email in the test, takes the real readable code out of it, and submits it.

## Real commands run this round and their real output

1. php artisan test --filter="Authentication" -v
   -> 6 tests passed, 32 smaller checks (covers: login success, no-token
      login refused while a card exists, token-header login refused, rate
      limit, logout, login again after logout).

2. php artisan test       (the ENTIRE suite)
   -> 96 tests passed, 269 smaller checks, 0 failures.

3. vendor\bin\pint --test (code style checker)
   -> passed.

## Final result

- Full test suite: **96 tests / 96 passing (269 assertions)**.
- Repeated login is now impossible: an account with an active token is refused
  401 until it logs out.
- The password reset token question is resolved: send the READABLE code from
  the email; the database's scrambled version is only for the server to compare
  against.