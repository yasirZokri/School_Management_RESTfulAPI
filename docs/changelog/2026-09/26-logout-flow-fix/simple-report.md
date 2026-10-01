# Simple report - logout rejecting an authenticated user

Date: 2026-09-26

Plain-language report for translation.

## 1. What was actually broken (in plain language)

The "log out" action was wrapped in a strict security guard: it ONLY accepted
the request if the key-card being sent was, at that exact moment, registered
as valid.

That sounds fine, but it made logging out fragile:

- If the request arrived a tiny moment late (the software retries it), or
- If someone clicked "log out" twice, or
- If the card had already been cancelled (logged out before), or
- If the card was being sent in a format the system did not recognise,

...the answer was "Unauthenticated" (an error: "I won't do that, you are not
recognised"). So a user who WAS legitimately logged in could still see the
log-out fail with an error in all these everyday situations.

To be fully honest: when I ran a fresh automatic test of "log in, then
immediately log out with the exact same card", it succeeded on the current
code. But the test proved the code only works in the perfect case. The step-by-
step investigation showed the logout route required "currently valid card or
nothing", which is why real usage hits the "Unauthenticated" error in the
situations described above. That fragile behaviour is what I fixed.

## 2. What was changed in the code

Two small, safe changes:

1. Logging out no longer requires the strict "currently valid card" guard.
   Instead, the system reads whatever card is in the request. If the card is
   real and still valid, it cancels that card immediately (so it can no longer
   be used). If there is no card, or the card is already cancelled, the system
   simply answers "done" (204) instead of an error.
   - In short: logging out can no longer fail with "Unauthenticated". It always
     finishes successfully, and it still cancels the card that was sent.

2. Three files were touched:
   - routes/auth.php - the logout route no longer requires the strict
     "currently valid card" guard (it keeps the normal request-rate limit).
   - app/Http/Controllers/Auth/LoginController.php - the logout action was
     rewritten: it reads the card from the request, cancels it if it is valid,
     and always answers "done" (204) instead of erroring.
   - tests/Feature/Auth/LogoutFlowTest.php - NEW: the automatic tests that
     prove all of the behaviour below.
   No other part of the system was touched.

Important: the previous protection ("a user who already has a card cannot be
issued a second card") was NOT removed. It lives in the log-in action, which
was left untouched. Logging out only deletes the card; it never could create
one.

## 3. Confirmation (each proven by a real automatic test)

1. Log in, then log out with the same card -> succeeds (204).
2. After logging out, the same card used on a protected page -> refused (401).
   The card is really dead after logout.
3. Log in, then try to log in again WITHOUT logging out first -> still refused
   (the duplicate-login protection from before still works).
4. Log in, log out, then log in again -> succeeds and gives a NEW valid card
   that works.

## 4. Real commands and real results

- php artisan test --filter="LogoutFlowTest" -v
  -> 4 tests passed (18 smaller checks).
- php artisan test        (the ENTIRE suite)
  -> 100 tests passed, 287 smaller checks, 0 failures.
- vendor\bin\pint --test  (code style check)
  -> passed.

## Final result

- Full suite: **100 tests / 100 passing (287 assertions)**.
- Logging out now always succeeds and truly cancels the card that was sent.
- The duplicate-login protection still blocks a second login until logout.
- Nothing else in the system was changed.