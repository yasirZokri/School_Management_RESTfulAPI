# Phase 1 - Users (role), Schools, Students, Teachers

Date: 2026-09-22

## What was decided

- **Database**: switched from SQLite to MySQL (host 127.0.0.1:3306, user root).
  - Dev database: `school_management_api`
  - Test database: `school_management_api_test` (used by phpunit via `phpunit.xml`)
- **users.role**: added with a NEW migration (not by editing the original), as approved.
  Stored as a string, handled in code as a `UserRole` enum (admin/teacher/student/staff).
- **Students and teachers**: use soft deletes (`deleted_at`), as approved, so future
  salary/payment records stay valid when a student/teacher is "removed".
- **API versioning**: endpoints live under `/api/v1/*`.
- **API auth for now**: the existing Breeze session login protects the API routes
  (`auth` middleware). Sanctum tokens were NOT added in this phase (Phase 6 was removed).
- **Policies**: admins can create/update/delete; everyone (authenticated) can view.

## Files created (Phase 1)

- `app/Enums/UserRole.php` - the role enum.
- `database/migrations/2026_09_22_000001_add_role_to_users_table.php`
- `database/migrations/2026_09_22_000002_create_schools_table.php`
- `database/migrations/2026_09_22_000003_create_students_table.php` (softDeletes)
- `database/migrations/2026_09_22_000004_create_teachers_table.php` (softDeletes)
- `app/Models/School.php`, `app/Models/Student.php`, `app/Models/Teacher.php`
- `app/Http/Controllers/{School,Student,Teacher}Controller.php` (+ `Controller.php` now
  includes `AuthorizesRequests` so controllers can call `authorize()`).
- `app/Http/Requests/{School,Student,Teacher}/{Store,Update}*Request.php`
- `app/Http/Resources/{School,Student,Teacher}Resource.php`
- `app/Policies/{School,Student,Teacher}Policy.php`
- `database/factories/{School,Student,Teacher}Factory.php` (+ `UserFactory` got a `role()` helper)
- `database/seeders/DatabaseSeeder.php` (1 admin + 1 school + 5 students + 3 teachers)
- `tests/Feature/{School,Student,Teacher}Test.php`

## Fixes outside the plan (small, required to make anything run)

- `app/Models/User.php` was missing `use Laravel\Sanctum\HasApiTokens;` (skeleton bug) -
  without it, creating any user crashed. Added the import.
- Routes in `routes/api.php` were missing the `use` for the new controllers.

## Test results

- 25 / 30 tests pass (all 20 new Phase 1 tests + 5 unrelated auth tests).
- 5 pre-existing Breeze auth tests FAIL - NOT caused by Phase 1:
  their requests hit `/login`, `/register`, `/forgot-password` but the routes are
  actually mounted at `/api/...`. They were already broken before this phase.

## Rejected alternatives

- Adding Sanctum token login now (waiting until the user asks - Phase 6 removed).
- Editing the original users migration (rejected: approved approach was a new migration).
- Hard "restrict" FK delete behavior on students/teachers (rejected: soft deletes chosen -
  simpler, and keeps money records valid).

## Next steps (waiting for approval)

- Fix or skip the 5 broken auth tests (user decision).
- Phase 2: academic_years, classes, subjects, enrollments, teaching_assignments.