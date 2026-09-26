# Authorization fix + permission matrix + user-creation lockdown

Date: 2026-09-26

Self-contained report: terms like "policy", "Form Request" and "403" are
explained the first time they appear.

## The critical bug

### What was wrong
There was a public endpoint `POST /api/register` (left over from the Breeze
install). ANYONE - including a student, even with no login at all - could call
it to create a user account in the database. For a school management system
this is wrong: accounts belong to real students/teachers and should only be
created by an admin.

### What changed
Public self-registration does not fit this system, so it was **removed
entirely**:
- routes/auth.php - deleted the `/register` route.
- Deleted the `RegisterUserController` and the `RegistrationTest.php` file.

The only way to create a user account now is an **admin creating a student or
a teacher**, and that flow creates the linked user account internally (see
below). There is no endpoint left that accepts "please make me an account".

## Permission model (exactly what was requested)

A "policy" is a Laravel class that answers "may THIS user do THIS action on
THIS record?". Policies answer `view` (see one), `viewAny` (see the list),
`create`, `update` and `delete`. Auto-discovery in Laravel 13 wires them up.

### Admin
- view: all admins, all teachers, all students.
- create/delete: students and teachers (and their linked user accounts).
- The ONLY role that can create or delete anything (checked and confirmed: on
  every resource the `create` / `update` / `delete` abilities are admin-only).

### Teacher
- view: the list/details of students, and their own personal profile only.
- cannot view other teachers or admins.
- cannot create or delete anything (students or teachers).

### Student
- view: their own personal profile only - no other students, no teachers,
  no admins.
- cannot create or delete anything.

## What I changed per file

### app/Policies/UserPolicy.php - NEW
A policy for user accounts did not exist. Added:
- viewAny: admin only.
- view: admin, or the user viewing their OWN account (self).
- create / update / delete: admin only.

### app/Policies/TeacherPolicy.php - changed
`view` previously returned admin-only, which meant a teacher could not even see
their own profile. Now:
- viewAny: admin only (list of teachers).
- view: admin **OR the teacher viewing their own profile** (self).
- create / update / delete: admin only (unchanged).

### app/Policies/StudentPolicy.php - changed
`view` previously returned admin-or-teacher only, so a student could not see
their own profile. Now:
- viewAny: admin or teacher (list of students).
- view: admin, or teacher, **OR the student viewing their own profile** (self).
- create / update / delete: admin only (unchanged).

### User creation is admin-controlled, internally
- app/Http/Requests/Student/StoreStudentRequest.php - no longer accepts a
  `user_id`. It now validates the pieces needed to build the account itself:
  name, email (unique across users), password (minimum 8), school_id,
  student_number.
- app/Http/Requests/Teacher/StoreTeacherRequest.php - same change with
  employee_number instead of student_number.
- app/Http/Controllers/StudentController.php - `store()` calls
  `authorize('create')` (admin), then creates the User with the **Student**
  role and the Student row linked to it. The password is hashed automatically
  by the User model.
- app/Http/Controllers/TeacherController.php - the same, with the **Teacher**
  role.
- Store/Update requests for students/teachers no longer have a `user_id` input
  anywhere (Update ones also dropped it).

### No inline validation left in controllers
Audit result: every resource controller already used Form Request classes
(the "request" classes) for create/update/delete. The two remaining inline
`$request->validate([...])` calls were in the password-reset auth endpoints.
Added two small Form Requests so nothing validates inside a controller:
- app/Http/Requests/Auth/SendPasswordResetLinkRequest.php
- app/Http/Requests/Auth/ResetPasswordRequest.php
...and PasswordResetLinkController / NewPasswordController now type-hint them.

## Rate limiting sanity check (no redesign)
`php artisan route:list -v` confirms:
- login, forgot-password, reset-password: `throttle:5,1` (5/min).
- verify-email + verification-notification: `throttle:6,1`.
- logout, /user, and every /api/v1 resource: `throttle:60,1` (60/min).
- `/register` no longer exists (removed in this fix).

## Tests added for every role x action (200 vs 403)
Both teachers and students now have tests covering admin/teacher/student
against view-list, view-detail, view-self, create and delete:

- StudentTest (11 tests):
  - admin: create (linked account asserted), list 200, detail 200, update, soft
    delete, missing-field validation.
  - teacher: list students 200, view a student 200.
  - student-owner: view own profile 200.
  - student: list 403, view another student 403, create 403, delete 403.
  - teacher wanting to create/delete a student: 403.
- TeacherTest (11 tests):
  - admin: create (linked account asserted), list 200, detail 200, update, soft
    delete, missing-field validation.
  - teacher-owner: view own profile 200.
  - teacher: list 403, view another teacher 403, create 403, delete 403.
  - student: list 403, view a teacher 403, create 403, delete 403.
  - staff: create 403 (kept from before).

New/updated test files:
- tests/Feature/StudentTest.php (rewritten create payload + new matrix tests).
- tests/Feature/TeacherTest.php (rewritten create payload + new matrix tests).
- tests/Feature/Auth/RegistrationTest.php - deleted (endpoint gone).

## Problems/bugs I hit while fixing, and the fixes

1. **The route file was modified externally again.** On the first run of the
   suite EVERY resource test failed with 404 - routes/api.php had lost its
   `/api/v1` prefix (the same drift that happened once before). I restored
   `prefix('v1')` on the versioned group, matching the tests and the original
   design. After that the suite went green.
2. **Pint reported style issues in the migrations** (also consistent with the
   external overwrite). Ran Pint, which normalized everything.
3. **Teacher/student create payloads changed shape** - previously the client
   sent a `user_id`; now the client sends name/email/password and the account
   is built server-side. All tests were updated to assert the linked user is
   created with the correct role.

## Final result

- Full test suite: **94 tests / 94 passed (251 assertions)**. Pint clean.
- A student can no longer create an account under any circumstance; only an
  admin can, and only through the student/teacher creation flows.
- view/viewAny now allow self-view on top of the admin/teacher rules.
- create/update/delete are admin-only across every resource.
- No controller does inline validation anymore; every write action uses a Form
  Request.
- Throttle limits confirmed: auth = 5/min, verify = 6/min, everything else
  on the general API = 60/min.