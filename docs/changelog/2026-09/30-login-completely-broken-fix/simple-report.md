# Login Was Completely Broken — What Was Wrong and What I Changed

## What was actually broken

The login screen has a guard: before letting someone log in, it asks "does
this account already have a sign-in record (token)?" and if yes, it refuses
with "You are already authenticated. Please log out before signing in again."

That guard was asking the question of an old database table that still
contained **24 leftover sign-in records** — leftovers from when this same
login/logout feature was being built and tested in earlier days. These old
records had no expiry date, so the guard treated every one of them as "still
logged in." The main admin account alone had **17** of these leftover records.

That is why every login attempt failed — even a brand-new one, even right
after a successful logout. The guard looked at the leftovers and wrongly
concluded the user was already logged in.

Logout itself was doing its job, and the account's real sign-out records were
being removed — but these old leftovers had no real client using them, so no
logout could ever remove them, and they kept blocking login forever.

## What I changed

Two things:

1. **Code — the check can no longer be fooled by leftover junk.**
   A sign-in record only counts as "really logged in" once it has actually
   been used by the application. A record that was created but never used by
   anyone is just abandoned data — it can no longer lock an account out of
   login. The protection against logging in twice is unchanged for a real,
   actively-used login.

2. **Data — removed the 24 old leftover sign-in records.**
   The database is now clean (24 records → 0), so there is nothing left to
   block any account.

## Proof (real results shown below were actually run)

```
Total leftover records in the database .... 0
Active sign-in records for the admin ...... 0   (nothing blocking login)

Real login .................... status 200, token issued: YES
Real logout ................... status 204, records left after logout: 0
Real login AGAIN .............. status 200, brand-new token issued: YES
New token works on a protected page ...... status 200
Final records left in database ........... 0
```

## Test suite result

```
Command:  php artisan test
Result:   116 tests passed / 116 total, 373 assertions
```
Code style check: clean.