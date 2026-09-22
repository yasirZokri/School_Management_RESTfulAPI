# Phase 2 - Academic years, Classes, Subjects, Enrollments, Teaching assignments

Date: 2026-09-22

## Parallel work: fixed the 5 pre-existing auth test failures

- The Breeze auth tests posted to `/login`, `/register`, etc., but the real routes
  are `/api/login`, `/api/register`, ... (mounted via `routes/api.php`).
- Tests updated to use the real paths (tasks `.test` future note: only test files changed).
- Two tests then exposed a REAL bug: the `api` middleware group has no session
  middleware, so `/api/login` and `/api/logout` threw "Session store not set on request".
  Fix (routes/controllers unchanged): prepend `StartSession` to the api group
  in `bootstrap/app.php`.
- Result: all 30 tests passed before Phase 2 began.

## New tables (migrations)

| # | Table | Notes |
|---|---|---|
| 5 | academic_years | school_id, name, starts_on, ends_on, is_current |
| 6 | classes | school_id, academic_year_id, name, grade_level, section; unique per school/year/name/section |
| 7 | subjects | school_id, name, code; unique(school_id, code) |
| 8 | enrollments | student_id, class_id, academic_year_id, status; unique(student_id, class_id, academic_year_id) |
| 9 | teaching_assignments | teacher_id, class_id, subject_id, academic_year_id; unique(teacher_id, class_id, subject_id, academic_year_id), named index because auto name was too long for MySQL |

## Cross-school validation (in Form Requests)

- Class: its academic year must belong to the same school (Rule::exists + where).
- Enrollment: student and class in the same school; year must be the class's year.
- Teaching assignment: teacher, class and subject all same school; year matches class.
- Enrollment status is a plain string validated as in: active / completed / withdrawn.

## Files created

- 5 migrations (above)
- Models: AcademicYear, SchoolClass ($table = 'classes'), Subject, Enrollment, TeachingAssignment
  + reverse relationships added to School (academicYears/classes/subjects), Student (enrollments), Teacher (teachingAssignments)
- Factories: AcademicYearFactory, SchoolClassFactory, SubjectFactory, EnrollmentFactory, TeachingAssignmentFactory
- API Resources: AcademicYearResource, SchoolClassResource, SubjectResource, EnrollmentResource, TeachingAssignmentResource
- Form Requests: Store/Update x 5 (10 files)
- Controllers x 5 (thin, same pattern as Phase 1)
- Policies x 5 (view for all, changes admin-only)
- Routes: /api/v1/academic-years, classes, subjects, enrollments, teaching-assignments
- Tests: 5 new Feature test files (31 tests)
- DatabaseSeeder extended with a year, subjects, classes, enrollments, teaching assignments

## Bugs found during the phase (and fixes)

1. MySQL index name too long (64 char limit) -> explicit short name on the unique key.
2. Seeder was not safe to re-run -> added an early "already seeded?" guard; dev DB rebuilt with `migrate:fresh --seed`.
3. Route binding: `SchoolClassController` method params were `$schoolClass` but the route
   parameter is `{class}`, so Laravel injected an empty model (null fields). Renamed to `$class`
   and added a comment (course: variables named after the route parameter).
4. Enrollment create response showed status = null because the DB default is applied on insert;
   the in-memory model didn't know it -> `refresh()` before building the Resource.
5. `EnrollmentTest` was missing the `AcademicYear` import (fixed).

## Test results

- Full suite: 61 tests / 61 passed (148 assertions) before and after Pint.
- Checks covered: CRUD per endpoint, validation failures, cross-school rejections,
  duplicate subject code, duplicate teaching assignment.

## Rejected alternatives

- A `status` enum for enrollments (used a validated string instead - simpler; enum can be added later).
- Database-level composite FK checks for cross-school consistency (not practical in MySQL);
  validation lives in Form Requests instead.

## Next step (waiting for approval)

- Phase 3: lessons (tied to teaching_assignments), teacher_salaries, student_payments.