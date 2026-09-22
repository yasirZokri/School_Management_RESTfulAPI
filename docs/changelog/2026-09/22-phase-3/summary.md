# Phase 3 - Lessons, Teacher Salaries, Student Payments

Date: 2026-09-22

This report is written so it can be read without knowing Laravel's internals.
Terms like "migration", "factory" and "resource" are explained the first time they appear.

## What this phase added

Three new things the school API can store and serve:

1. **Lessons** - content a teacher prepares. Each lesson belongs to a
   "teaching assignment" (the fact that teacher X teaches subject Y to class Z).
   The lesson itself does not store teacher/class/subject - it gets all of that
   from its assignment, so you can never mismatch them.
2. **Teacher salaries** - one salary record per teacher per month.
3. **Student payments** - money a student paid (tuition, books, ...).

## New files, and what each one does

### 1. Migrations (3) - these "write" the database tables

A migration is a file that creates or changes a database table. When we run
`php artisan migrate`, Laravel runs them in order.

- `2026_09_22_000010_create_lessons_table.php` - table `lessons` with:
  `teaching_assignment_id` (links to the assignment), `title`, `description`
  (optional), `content` (optional, long text), timestamps.
- `2026_09_22_000011_create_teacher_salaries_table.php` - table `teacher_salaries`
  with: `teacher_id`, `amount` stored as `decimal(10,2)` (always 2 decimals),
  `month` (e.g. "2026-09"), `paid_at` (empty until the salary is actually paid).
  Also adds a **unique constraint on (teacher_id, month)** - the database itself
  refuses a second salary row for the same teacher in the same month.
- `2026_09_22_000012_create_student_payments_table.php` - table `student_payments`
  with: `student_id`, `amount` `decimal(10,2)`, `purpose`, `paid_at`,
  `payment_method`. `purpose` and `payment_method` are plain text columns - the
  list of allowed values lives in the validation code, not in the database
  (so adding a new option later never touches the database).

### 2. Models (3) - the PHP classes that represent a row

A model is a class that talks to one table: it knows the column names, can
fetch/save rows, and knows the relationships.

- `app/Models/Lesson.php` - `teachingAssignment()` says "this lesson belongs to
  that assignment".
- `app/Models/TeacherSalary.php` - `teacher()`; also casts `amount` to always be
  shown with two decimals and `paid_at` as a date.
- `app/Models/StudentPayment.php` - `student()`; defines the two allowed-lists
  that validation uses:
  `PURPOSES = ['tuition', 'transport', 'books', 'uniform', 'other']` and
  `PAYMENT_METHODS = ['cash', 'card', 'bank_transfer', 'other']`.
  Keeping them here means the store and update validation share one source.

Also **added small relationships to existing files** (no new tables, just links):
- `User` now has `teacher()` and `student()` - so a login account can look up
  "my teacher record" or "my student record".
- `Teacher` has `teacherSalaries()` and `lessons()` (through assignments).
- `Student` has `studentPayments()`.
- `TeachingAssignment` has `lessons()`.

### 3. Factories (3) - fake data generators for tests/seeds

A factory produces example rows. `LessonFactory` makes a lesson for a random
assignment; `TeacherSalaryFactory` (amount + month "2026-09"); `StudentPaymentFactory`
(picks a random purpose/method from the allowed lists).

### 4. Form Requests (6) - validation, and one special ownership rule

A "Form Request" is a class that checks the incoming data *before* the controller
runs. Each resource got a Store (create) and Update version.

- `Lesson/StoreLessonRequest`, `Lesson/UpdateLessonRequest` - require
  `teaching_assignment_id` + `title`. The interesting part:
  **if the logged-in user is a teacher, the teaching_assignment_id they send must
  belong to that same teacher.** An admin (who has no teacher record) is not
  restricted. This is checked on update too, but only when the assignment is the
  field being changed. (Details on why there is both here and in the Policy below.)
- `TeacherSalary/Store- & Update...Request` - `amount` must be numeric with at
  most 2 decimals; `month` must look like "2026-09"; and the **uniqueness** rule
  means a duplicate (teacher, month) is rejected with a 422 response instead of a
  confusing database error.
- `StudentPayment/Store- & Update...Request` - `purpose` must be one of
  `PURPOSES`, `payment_method` one of `PAYMENT_METHODS` (the "allowed-values list").

### 5. API Resources (3) - how a record looks in JSON

- `LessonResource` - id, title, description, content + a small block about the
  assignment (teacher name, class name, subject name).
- `TeacherSalaryResource` - id, amount, month, paid_at + teacher block.
- `StudentPaymentResource` - id, amount, purpose, paid_at, payment_method +
  student block.

### 6. Controllers (3) - the route handlers (standard CRUD)

- `LessonController`, `TeacherSalaryController`, `StudentPaymentController`.
  Each has: index (list), store (create), show (one), update, destroy (delete).
  They check the Policy, save the validated data, and return the Resource.

### 7. Policies (3) - "who is allowed to do what"

- `TeacherSalaryPolicy` / `StudentPaymentPolicy` - same simple rule as before:
  anyone authenticated can read, only admins create/change/delete (money records).
- `LessonPolicy` - read for everyone; **create is allowed for admins or teachers**;
  update/delete allowed for admins or **the teacher who owns the assignment** of
  that lesson. The ownership is enforced twice on purpose: the Policy gives a 403
  "Forbidden" for a lesson that isn't yours, and the Form Request gives a 422
  "Unprocessable" if you try to link a lesson to someone else's assignment when
  creating/updating. Your reminder said "validate ... where relevant", so it lives
  in both the validation layer and the permission layer.

### 8. Routes (added to `routes/api.php`)

Three new endpoint groups under `/api/v1`, protected by login:
`/api/v1/lessons`, `/api/v1/teacher-salaries`, `/api/v1/student-payments`
(each with the usual GET/POST/GET{id}/PUT/DELETE).

### 9. Seeder (added to `database/seeders/DatabaseSeeder.php`)

Sample data on every fresh build: 2 lessons per teaching assignment (6 total),
1 salary per teacher (3, month "2026-09"), 1 payment per student (5).
The same "already seeded?" guard from Phase 2 still prevents duplicate data.

### 10. Tests (3 new files, 25 tests)

- `tests/Feature/LessonTest.php` (9) - admin CRUD, validation errors,
  **teacher can create a lesson for their own assignment**,
  **teacher is refused for someone else's assignment**,
  **teacher cannot edit another teacher's lesson**, staff cannot create.
- `tests/Feature/TeacherSalaryTest.php` (8) - admin CRUD, marking as paid,
  **duplicate (teacher, month) is rejected**, staff forbidden.
- `tests/Feature/StudentPaymentTest.php` (8) - admin CRUD, **unknown purpose /
  unknown payment method rejected**, missing amount, staff forbidden.

## Bugs / problems I hit, and how I fixed them

1. **ISO date format mismatch in a test** - the test expected a date as
   `2026-10-01T00:00:00+00:00` but Laravel actually sends it with microseconds:
   `2026-10-01T00:00:00.000000Z`. The fix was updating the test's expected string
   (the actual API output was correct - Laravel's standard format).
2. No other bugs this phase - everything was written to match the Phase 1/2
   pattern, and it worked on the first full run except that one test string.

## Final counts and verification

- Full test suite: **86 tests / 86 passed** (210 assertions).
- Sprint across all three phases: Phase 1 (30) + Phase 2 (31) + Phase 3 (25) = 86.
- Dev database rebuilt from scratch (`migrate:fresh --seed`): 6 lessons,
  3 teacher salaries, 5 student payments, plus everything from Phases 1-2.
- Code style checked and auto-fixed with Pint (Laravel's standard formatter).

## Where the auth fits

The whole API uses the existing session-login: a request is rejected with 401 if
the browser is not logged in, then Policies decide what that logged-in user may
do. No new auth code was written in this phase.

## What comes next (nothing yet)

All three planned phases for the current feature set are now complete. If we add
more later (e.g. attendance, grades, or a student seeing their own payments),
the same one-file-per-job pattern applies.