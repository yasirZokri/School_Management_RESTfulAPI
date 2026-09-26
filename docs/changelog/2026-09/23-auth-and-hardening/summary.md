# Security & authentication hardening - rate limiting, permissions, token auth

Date: 2026-09-22

Self-contained report: terms like "middleware", "policy" and "Sanctum" are
explained the first time they appear. Handles the 3 issues requested BEFORE
moving on to further phases.

NOTE: Phase 3 (lessons / teacher salaries / student payments) was already
finished earlier, so "moving to Phase 3" really means "moving to the NEXT phase
(Phase 4 and beyond)". Everything below is on top of the currently completed
Phases 1-3.

## Issue 1 - Rate limiting on the API

### What was wrong
The API had no protection against someone sending thousands of requests
(guessing passwords, hammering endpoints).

### What changed
Laravel ships a built-in "throttle" middleware that counts requests and stops
responding (returns HTTP 429 "Too Many Requests") once a limit is reached.
It was added per-route:

- routes/auth.php - the login, register, forgot-password and reset-password
  routes are the brute-force targets, so they are limited to **5 requests per
  minute** (`throttle:5,1`).
- routes/api.php - every normal API endpoint (schools, students, lessons, ...)
  is limited to **60 requests per minute per user/IP** (`throttle:60,1`),
  including the `/user` route.
- logout and email-verification keep a sane 60/6-per-minute limit.

Verification: `php artisan route:list -v` shows `ThrottleRequests:5,1` on
login and `ThrottleRequests:60,1` on the API routes. There is also a new test
that actually logs in 5 times, then proves the 6th attempt gets a 429.

## Issue 2 - Wrong permissions on viewing teachers / students

### What was wrong
Any logged-in user (including students) could GET the list of teachers and
students. Students should not peek at other students' records, and teachers
should not peek at each other's records.

### What changed
Permissions in Laravel live in "Policy" classes. Two were fixed:

- app/Policies/TeacherPolicy.php - viewing teachers (list and single) is now
  **admin only**. A teacher gets a 403 "Forbidden" when listing teachers, and a
  student gets 403 too (creating/changing teachers stayed admin-only as before).
- app/Policies/StudentPolicy.php - viewing students is now **admin or teacher**.
  A teacher can list/view students, but a student gets a 403.

### New tests
- TeacherTest: "blocks a student from viewing teachers" and "blocks a teacher
  from viewing other teachers" (both list and single -> 403).
- StudentTest: "lets a teacher view students" (-> 200) and "blocks a student
  from viewing other students" (-> 403).

## Issue 3 - From session login to token login (Sanctum)

### What was wrong
Login returned "204 No Content" and authenticated with session cookies (made to
work in Phase 1 by adding a StartSession middleware workaround to the API).
Real API clients work better with tokens.

### What changed
- app/Http/Controllers/Auth/LoginController.php
  - store(): checks email+password, and if valid issues a **Sanctum personal
    access token** and returns
    `{ "token": "...", "user": { id, name, email, role } }`.
  - destroy(): **revokes** (deletes) the token that was used, so it stops
    working immediately, and returns 204.
  - No cookie/session is used at all.
- app/Http/Requests/Auth/LoginRequest.php was simplified (validation only; the
  old Breeze session-rate-limiting code in it is no longer needed because the
  route middleware in Issue 1 does that).
- app/Http/Controllers/Auth/RegisteredUserController.php - register no longer
  logs the user into a session; it just creates the account (204). The client
  then logs in via /api/login to get a token.
- routes/api.php - all protected routes now use the `auth:sanctum` middleware,
  which reads the `Authorization: Bearer <token>` header.
- routes/auth.php - logout and email-verification routes moved to `auth:sanctum`.
- bootstrap/app.php - **removed** the Phase-1 StartSession workaround entirely.
  The comment explains it is no longer needed. (Removing is safe: nothing on
  the API uses sessions anymore; the web pages at "/" still use the normal web
  session middleware, which was never touched.)
- tests/Pest.php - added a small helper `tokenFor($user)` that creates a token;
  a test uses `$this->withToken(tokenFor($user))` to send it as a Bearer header.

### Updated tests (all to token flow)
- Every Feature test (tests/Feature/*.php) now creates a token for its user and
  sends it as a Bearer header instead of `actingAs()` (session impersonation).
- AuthenticationTest rewritten: login returns { token, user }, invalid password
  -> 422, logout deletes the token (asserted in the DB) and the revoked token
  is refused afterwards (401), plus the new rate-limit test.
- RegistrationTest checks the account was stored and no session is created.
- PasswordResetTest / EmailVerificationTest updated to assert JSON responses and
  send the Bearer token.

## Problems/bugs I hit while fixing, and the fixes

1. **The route file was not the one I had left in Phase 3.** Routes had lost
   their `/api/v1` prefix and pointed at bare `/api` paths, so 76 of the 86
   existing tests were failing with 404 before I touched anything. I restored
   the `prefix('/v1')` on the versioned API group (matching the tests and the
   original design) and the auth routes stay at `/api/*`.
2. **Token-flow tests and Sanctum guard caching.** After logout the test proved
   the token row is gone from the database, but a follow-up request still came
   back 200 - because Sanctum's auth guard caches "who is logged in" for the
   lifetime of the test application. Fixed in the test by resetting the auth
   guards (`auth()->forgetGuards()`) before re-checking, so the request must
   look the token up in the database again, where it is now deleted -> 401.
3. **Session middleware no longer safe to keep.** Once login stopped using
   sessions, leaving StartSession on the API would be dead weight. Removed it.
4. **Rate-limiter counters vs the test suite.** Laravel stores the counters in
   the database cache; because each test wipes the database, counters never
   leak between tests (verified by the new throttle test behaving correctly).

## New files added

None. Everything was a change to existing files (controllers, requests, policies,
routes, bootstrap, tests). The only "new" things are test cases.

## Final result

- Full test suite: **91 tests / 91 passed** (234 assertions). Before this work
  the suite was at 86 tests with **76 failures** (mostly the 404 route issue),
  so we went from broken to fully green while adding the 3 hardening features.
- Every protected endpoint requires a Bearer token, throttling is in place on
  the whole API with a tighter limit on auth endpoints, and teacher/student
  records are only visible to the roles that should see them.

## Ready for the next phase

The app is now hardened and fully green. Tell me which phase comes next
(e.g. attendance, grades, or a student's own-payment view) and we will follow
the usual Migration -> Model -> Factory -> Controller -> Form Request ->
Resource -> Policy -> route -> test pattern.